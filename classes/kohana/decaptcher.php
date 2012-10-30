<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Simple w
 *
 * @package DeCaptcher
 * @author Alessandro Frangioni
 */
class Kohana_DeCaptcher {

	/**
	 * @var Config_Group configuration
	 */
	protected $_config;

	/**
	 * @var int timeout on cURL connection
	 */
	protected $_timeout = 300;

	/**
	 * @var int number of tries if some error is got
	 */
	protected $_tries = 3;

	/**
	 * @var int delay time in second between tries
	 */
	protected $_try_delay = 10;

	/**
	 * Loads configuration
	 *
	 */
	public function __construct()
	{
		$this->_config = Kohana::$config->load('decaptcher');
	}

	/**
	 * Decodes the input image
	 *
	 * @param string $filename path to image
	 * @param boolean $delete delete to image file after decoded
	 * @return string
	 */
	public function decode($filename, $delete = FALSE)
	{
		$code = NULL;
		$body = '';
		$current_try = 0;

		$post = array(
			'function'     => 'picture2',
			'username'     => $this->_config->username,
			'password'     => $this->_config->password,
			'pict'         => '@'.$filename,
			'text1'        => '',
			'pict_to'      => '0',
			'pict_type'    => (string) DeCaptcher_Codes::ptUNSPECIFIED,
			'print_format' => 'line'
		);

		$options = $this->options(array(
			CURLOPT_URL        => $this->url(),
			CURLOPT_POSTFIELDS => $post
		));

		while ($code !== 0
			AND $code !== DeCaptcher_Codes::ccERR_TEXT_SIZE
			AND $code !== DeCaptcher_Codes::ccERR_BALANCE
			AND $code !== DeCaptcher_Codes::ccERR_BAD_PARAMS
			AND $code < 10
			AND $current_try < $this->_tries)
		{
			if ($current_try++ > 0)
			{
				sleep($this->_try_delay);
			}

			Kohana::$log->add(Log::INFO, 'DeCaptcher try :try/:tries', array(
				':try'   => $current_try,
				':tries' => $this->_tries,
			));

			$ch = curl_init();

			curl_setopt_array($ch, $options);

			$body = curl_exec($ch);

			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			curl_close($ch);

			if ( ! strpos($code, '2') === 0)
			{
				return new DeCaptcher_Response(FALSE, 'cURL error');
			}

			$response = DeCaptcher::check_response($code, $body);

			$code = $response->code;

			$body = $response->body;
		}

		$parts = @explode( '|', $body);

		// ResultCode|MajorID|MinorID|Type|Timeout|Text

		$text = '';

		if ($parts[0] === '0')
		{
			// no errors, get text
			$status = TRUE;
			$text = $parts[5];
		}
		else
		{
			// got some error
			$status = FALSE;
		}

		if ($status === TRUE AND $delete === TRUE AND is_file($filename))
		{
			@unlink($filename);
		}

		return new DeCaptcher_Response($status, $text);
	}

	public function url()
	{
		if ($this->_config->port === 80 )
		{
			$url = 'http://'.$this->_config->host;
		}
		elseif ($this->_config->ssl === TRUE AND $this->_config->port === 443)
		{
			$url = 'https://'.$this->_config->host;
		}
		elseif ($this->_config->ssl === TRUE)
			{
			$url = "https://{$this->_config->host}:{$this->_config->port}";
		}
		else
		{
			$url = "http://{$this->_config->host}:{$this->_config->port}";
		}

		return $url;
	}

	/**
	 * Compiles options array
	 *
	 * @param array $options options
	 * @return type
	 */
	public function options(array $options = array())
	{
		$options = Arr::merge(array(
			CURLOPT_HEADER         => 0,
			CURLOPT_VERBOSE        => 0,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_HTTPHEADER     => array('Expect:'),
			CURLOPT_SSL_VERIFYPEER => (bool) $this->_config->ssl,
			CURLOPT_CONNECTTIMEOUT => $this->_timeout,
			CURLOPT_POST           => TRUE,
			CURLOPT_USERAGENT      => 'Mozilla/4.0 (compatible;)'
		), $options);

		return $options;
	}

	public static function check_response($code, $body = '')
	{
		if (strlen($body) === 0)
			return (object) array(
				'code' => $code,
				'body' => 'wrong settings'
			);

		if (preg_match('/^-?[0-9]+$/', $body))
		{
			$code = (int) $body;

			if($code < 0)
			{
				switch($code)
				{
					case DeCaptcher_Codes::ccERR_GENERAL:
						$error = 'general internal error';
						break;
					case DeCaptcher_Codes::ccERR_STATUS:
						$error = 'status is not correct';
						break;
					case DeCaptcher_Codes::ccERR_NET_ERROR:
						$error = 'network data transfer error';
						break;
					case DeCaptcher_Codes::ccERR_TEXT_SIZE:
						$error = 'text is not of an appropriate size';
						break;
					case DeCaptcher_Codes::ccERR_OVERLOAD:
						$error = 'server is overloaded';
						break;
					case DeCaptcher_Codes::ccERR_BALANCE:
						$error = 'not enough funds to complete the request';
						break;
					case DeCaptcher_Codes::ccERR_TIMEOUT:
						$error = 'request timed out';
						break;
					case DeCaptcher_Codes::ccERR_BAD_PARAMS:
						$error = 'provided parameters are not good for this function';
						break;
					default:
						$error = 'unknown error';
						break;
				}
			}
			/*
			elseif ($body < 10)
			{
				$code = $body;

				$error = 'Malformed output data';
			}
			*/
			else
			{
				$code = FALSE;

				$error = 'HTTR ERROR '.$body;
			}

			return (object) array(
				'code' => $code,
				'body' => 'Can\'t process: '.$error
			);
		}

		return (object) array(
			'code' => TRUE,
			'body' => $body
		);
	}

} // End Kohana_DeCaptcher