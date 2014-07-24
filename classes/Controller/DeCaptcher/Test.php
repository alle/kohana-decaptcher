<?php defined('SYSPATH') or die('No direct script access.');
/**
 * DeCatpcher Test Controller
 *
 * @package kohana-decaptcher
 * @author Alessandro Frangioni
*/
class Controller_Decaptcher_Test extends Controller {

	public function action_index()
	{
		try
		{
			$image = Kohana::find_file('media', 'raincaptcha.png', FALSE);

			$dec = new DeCaptcher;

			$response = $dec->decode($image);

			Kohana::$log->add(($response->status) ? Log::INFO : Log::ERROR, $response->message);
		}
		catch (Exception $e)
		{
			Kohana::$log->add(Log::ERROR, $e->getMessage());
		}
	}

} // End Controller_DeCaptcher_Test