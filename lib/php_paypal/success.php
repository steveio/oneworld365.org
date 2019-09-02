<?php

/*
 * success.php
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


include("/www/vhosts/oneworld365.org/htdocs/conf/config.php");
include("/www/vhosts/oneworld365.org/htdocs/classes/file.class.php");
include("/www/vhosts/oneworld365.org/htdocs/classes/logger.php");
$sPath = "/www/vhosts/oneworld365.org/logs/paypal_tx.log";
$sData = "\n\rBEGIN TRANSACTION :::::::::::::::::::::::::::::::\n\r";
$sData = "recieved_date= ".date("H:i:s d/m/Y")."\n\r";
foreach($_GET as $k => $v) {
	$sData .= $k."=".$v."\n\r";
}
$sData .= "\n\rEND TRANSACTION :::::::::::::::::::::::::::::::\n\r";
file::Write($sData,$sPath,$mode = "a");



?>

<html>
<head><title>::Thank You::</title>
<link rel="stylesheet" type="text/css" href="styles.css">
</head>

<body bgcolor="ffffff">
<br>
<br>
<table width="500" border="0" align="center" cellpadding="1" cellspacing="0">
   <tr> 
      <td align="left" valign="top" bgcolor="#333333"> <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr> 
               <td align="center" bgcolor="#EEEEEE"> <p>&nbsp;</p>
                  <p>Thank you! Your order has been successfully processed.</p>
                  <p>&nbsp;</p></td>
            </tr>
         </table></td>
   </tr>
</table>
<br>
<table width="500" border="0" align="center" cellpadding="1" cellspacing="0">
   <tr> 
      <td align="left" valign="top" bgcolor="#333333"> <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr align="left" valign="top"> 
               <td width="20%" bgcolor="#EEEEEE"><table width="100%" border="0" cellspacing="0" cellpadding="3">
                     <tr align="left" valign="top"> 
                        <td bgcolor="#EEEEEE">Order Number:</td>
                        <td bgcolor="#EEEEEE"> 
                           <?=$_POST[txn_id]?>
                        </td>
                     </tr>
                     <tr align="left" valign="top"> 
                        <td bgcolor="#EEEEEE">Date:</td>
                        <td bgcolor="#EEEEEE"> 
                           <?=$_POST[payment_date]?>
                        </td>
                     </tr>
                     <tr align="left" valign="top"> 
                        <td width="20%" bgcolor="#EEEEEE"> First Name: </td>
                        <td width="80%" bgcolor="#EEEEEE"> 
                           <?=$_POST[first_name]?>
                        </td>
                     </tr>
                     <tr align="left" valign="top"> 
                        <td bgcolor="#EEEEEE">Last Name:</td>
                        <td bgcolor="#EEEEEE"> 
                           <?=$_POST[last_name]?>
                        </td>
                     </tr>
                     <tr align="left" valign="top"> 
                        <td bgcolor="#EEEEEE">Email:</td>
                        <td bgcolor="#EEEEEE"> 
                           <?=$_POST[payer_email]?>
                        </td>
                     </tr>
                  </table></td>
            </tr>
         </table></td>
   </tr>
</table>
<br>
</body>
</html>
