<?php


class Request {
	
	public static function GetUri($return = "STRING") {
		
		$uri = preg_replace("/[^a-zA-Z0-9_\"\-\/\?\&=\. ]/","",urldecode($_SERVER['REQUEST_URI']));
		
		if ($return == "STRING") {
			return $uri; 
		} elseif ($return == "ARRAY") {
			$a = explode("?",$uri);
			return explode("/",$a[0]);
		}
		
	}
	
	public static function GetHostName() {
		
		$server_name = $_SERVER["SERVER_NAME"];
		$a = explode(".",$_SERVER["SERVER_NAME"]);
		return $hostname = $a[1].".".$a[2];
		
	}
			
	
}