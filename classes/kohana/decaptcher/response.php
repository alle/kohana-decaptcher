<?php defined('SYSPATH') or die('No direct access allowed.');

class Kohana_DeCaptcher_Response {

	protected $_data = array(
		'status'  => TRUE,
		'message' => ''
	);

	private $__error_message = 'Property :name does not exists in :class';

	public function __set($name, $value)
	{
		if (isset($this->_data[$name]))
		{
			$this->_data[$name] = $value;

			return;
		}

		throw new Publish_Exception($this->__error_message, array(
			':name'  => $name,
			':class' => get_class($this)
		));
	}

	public function __get($name)
	{
		if (array_key_exists($name, $this->_data))
			return $this->_data[$name];

		throw new Publish_Exception($this->__error_message, array(
			':name'  => $name,
			':class' => get_class($this)
		));
	}

	public function __construct($status = TRUE, $message = '')
	{
		$this->set_fields(array(
			'status'  => $status,
			'message' => $message,
		));

		return $this;
	}

	public function set_fields(array $data)
	{
		foreach (array_intersect_key($data, $this->_data) as $key => $value)
		{
			$this->$key = $value;
		}

		return $this;
	}

} // End Kohana_DeCaptcher_Response