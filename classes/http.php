<?php


class Http {
	
	public static function Header($http_status_code,$url = '') {
		
		switch($http_status_code) {
			case 404:	
				header('HTTP/1.1 404 Not Found');
				break;
			case 301:
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: ".$url);
				break;
		}
		
		die();
	}
	
	public static function Redirect($url_to) {

		
		header('Location: '.$url_to);
		die();
	}  
	
	
}