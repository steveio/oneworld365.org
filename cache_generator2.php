<?
/* Cache Generator 2 - backed cache page generator */

$brand = (strlen($argv[1]) > 1) ? $argv[1] : $_GET['BRAND'];
if (strlen($brand) < 1) die("ERROR : BRAND identifier must be supplied");
	
include("/www/vhosts/".$brand."/htdocs/conf/config.php");
include(ROOT_PATH."/classes/db_pgsql.class.php");
include(ROOT_PATH."/classes/logger.php");
include(ROOT_PATH."/classes/cache.class.php");

$db = new db($dsn,$debug = false);
$db->query("SELECT sid,uri FROM cache WHERE active = 'F' AND sid = ".$_CONFIG['site_id']);

if ($db->getNumRows() >= 1) {
	$aRows = $db->getRows();
	foreach($aRows as $aRow) {
		$sUrl = "http://www.".$brand.$aRow['uri'];
		Cache::Generate($sUrl,$aRow['uri'],$_CONFIG['site_id']);
	}
}
?>
