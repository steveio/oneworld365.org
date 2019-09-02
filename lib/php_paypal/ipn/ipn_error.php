<?php
/*
 * ipn_error.php
 *
 * PHP Toolkit for PayPal v0.51
 * http://www.paypal.com/pdn
 *
 * Copyright (c) 2004 PayPal Inc
 *
 * Released under Common Public License 1.0
 * http://opensource.org/licenses/cpl.php
 *
 */


// this is an include file - no functionality when
// called directly

if(isset($paypal['business']))
{
	//log error to file or database
	//log successful transaction to file or database
	include("/www/vhosts/oneworld365.org/htdocs/conf/config.php");
	include("/www/vhosts/oneworld365.org/htdocs/classes/file.class.php");
	include("/www/vhosts/oneworld365.org/htdocs/classes/logger.php");
	$sPath = "/www/vhosts/oneworld365.org/logs/paypal_tx_fail.log";
	$sData = "\n\rBEGIN TRANSACTION :::::::::::::::::::::::::::::::\n\r";
	$sData = "recieved_date= ".date("H:i:s d/m/Y")."\n\r";
	foreach($paypal as $k => $v) {
		$sData .= $k."=".$v."\n\r";
	}
	$sData .= "\n\rEND TRANSACTION :::::::::::::::::::::::::::::::\n\r";
	
	file::Write($sData,$sPath,$mode = "a");
}
else
{
	die('This page is not directly accessible');
}


?>