<?php

/*
 * A few utility methods for dealing with paypal transactions
 * 
 * 
 */

class PayPal {


	public static function TXLog() {
		
		/* log paypal transaction callback data */
		$sPath = LOG_PATH."/paypal_tx.log";
		$sData = "\n\rBEGIN TRANSACTION :::::::::::::::::::::::::::::::\n\r";
		$sData .= "recieved_date= ".date("H:i:s d/m/Y")."\n\r";
		foreach($_REQUEST as $k => $v) {
			$sData .= $k."=".$v."\n\r";
		}
		$sData .= "\n\rEND TRANSACTION :::::::::::::::::::::::::::::::\n\r";
		file::Write($sData,$sPath,$mode = "a");
		
	}


};

?>