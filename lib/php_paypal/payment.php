<?php
/*
 * payment.php
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
?>

<html>
<head>
<title>::PHP PayPal Payment Button::</title>
</head>

<body>

<form method="post" action="process.php">
<input type="hidden" name="amount" value="50">
<input type="hidden" name="item_name" value="Test Payment">
Basic Advertising Package 6months  &pound;50 <input type="submit" value=" Pay ">
</form>

<form method="post" action="process.php">
<input type="hidden" name="amount" value="99">
<input type="hidden" name="item_name" value="Test Payment">
Basic Advertising Package 12months  &pound;99 <input type="submit" value=" Pay ">
</form>


</head>
</html>
