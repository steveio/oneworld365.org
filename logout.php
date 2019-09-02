<?
ob_start();



/*
*
* Login Handler
*
*/

require_once("./conf/config.php");
require_once("./classes/logger.php");
require_once("./classes/login.class.php");



$oLogin = new Login();
$oLogin->doLogout($_CONFIG['url']);

die();

?>
