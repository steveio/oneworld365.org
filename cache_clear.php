<?
/* Cache Clear - triggers refresh of all cached pages */

	
include("/www/vhosts/oneworld365.org/htdocs/conf/config.php");
include("/www/vhosts/oneworld365.org/htdocs/classes/db_pgsql.class.php");

$db = new db($dsn,$debug = false);
$db->query("UPDATE cache SET active = 'F'");

?>
