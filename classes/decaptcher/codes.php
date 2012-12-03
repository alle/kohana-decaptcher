<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Output/error code from the De-Captcher service
 *
 * @package kohana-decaptcher
 * @author Alessandro Frangioni
 */
final class DeCaptcher_Codes {

	const ccERR_OK         = 0; // everything went OK
	const ccERR_GENERAL    = -1; // general internal error
	const ccERR_STATUS     = -2; // status is not correct
	const ccERR_NET_ERROR  = -3; // network data transfer error
	const ccERR_TEXT_SIZE  = -4; // text is not of an appropriate size
	const ccERR_OVERLOAD   = -5; // server's overloaded
	const ccERR_BALANCE    = -6; // not enough funds to complete the request
	const ccERR_TIMEOUT    = -7; // request timed out
	const ccERR_BAD_PARAMS = -8 ; // provided parameters are not good for this function
	const ccERR_UNKNOWN    = -200; // unknown error

	const ptoDEFAULT = 0; // default timeout, server-specific
	const ptoLONG    = 1; // long timeout for picture, server-specfic
	const pto30SEC   = 2; // 30 seconds timeout for picture
	const pto60SEC   = 3; // 60 seconds timeout for picture
	const pto90SEC   = 4; // 90 seconds timeout for picture

	const ptUNSPECIFIED = 0; // unspecified
	const ptASIRRA      = 86; // ASIRRA pictures
	const ptTEXT        = 83; // TEXT questions
	const ptMULTIPART   = 82; // MULTIPART quetions

	const ptASIRRA_PICS_NUM    = 12;
	const ptMULTIPART_PICS_NUM = 20;

} // End DeCaptcher_Codes