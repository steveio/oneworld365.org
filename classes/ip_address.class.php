<?php



class IPAddress {
	
	
	public static function GetVisitorIP() {

		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {

			$ipString = getenv('HTTP_X_FORWARDED_FOR');

		} elseif (isset($SERVER['REMOTE_ADDR'])) {

			$ipString = getenv('REMOTE_ADDR');

		} else {
			$ipString = '0.0.0.0';
		}

		//$ipString = (defined('HTTP_X_FORWARDED_FOR') && getenv('HTTP_X_FORWARDED_FOR')) ?  getenv(HTTP_X_FORWARDED_FOR) :  getenv(REMOTE_ADDR);
		if (preg_match("/\,/",$ipString)) {
			$addr = explode(",",$ipString);
			$ip = $addr[sizeof($addr)-1];
		} else {
			$ip = substr($ipString,0,15);
		}
		$ip = substr($ip,0,15);/* just to be sure not to permit > 15chars */

		return $ip;
		
	}
	
	
}

?>
