<?


$brand = (strlen($argv[1]) > 1) ? $argv[1] : $_GET['BRAND'];
if (strlen($brand) < 1) die("ERROR : BRAND identifier must be supplied");


include("/www/vhosts/".$brand."/htdocs/conf/config.php");
include(ROOT_PATH."/classes/db_pgsql.class.php");
include(ROOT_PATH."/classes/logger.php");
include(ROOT_PATH."/classes/cache.class.php");

$db = new db($dsn,$debug = false);


$sUrl = $_CONFIG['url'];
$sPath = ROOT_PATH;
$sUri = "/";
$id = $_CONFIG['site_id'];

Cache::Generate($sUrl,$sUri,$id);

if (in_array($brand,array("oneworld365.org","gapyear365.com"))) {

$sUrl = $_CONFIG['url']."/category/volunteer";
$sUri = "/category/volunteer";
//Cache::Generate($sUrl,$sUri,$id);

$sUrl = $_CONFIG['url']."/category/teaching";
$sUri = "/category/teaching";
//Cache::Generate($sUrl,$sUri,$id);

$sUrl = $_CONFIG['url']."/category/seasonal-jobs";
$sUri = "/category/seasonal-jobs";
//Cache::Generate($sUrl,$sUri,$id);

$sUrl = $_CONFIG['url']."/category/travel-tour";
$sUri = "/category/travel-tour";
//Cache::Generate($sUrl,$sUri,$id);

}

?>
