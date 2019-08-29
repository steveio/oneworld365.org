<?php



class IPAddress {
	
	
	public static function GetVisitorIP() {
		$ipString = (getenv(HTTP_X_FORWARDED_FOR)) ?  getenv(HTTP_X_FORWARDED_FOR) :  getenv(REMOTE_ADDR);
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
