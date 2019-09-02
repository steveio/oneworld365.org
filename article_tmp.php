<?

$brand = "oneworld365.org";

include("/www/vhosts/".$brand."/htdocs/conf/config.php");

include(ROOT_PATH."/classes/db_pgsql.class.php");
include(ROOT_PATH."/classes/logger.php");
//include(ROOT_PATH."/classes/cache.class.php");

$db = new db($dsn,$debug = false);


$db->query("select a.id,a.short_desc from article a where a.id in (select article_id from article_map where website_id = 4 and section_uri like '%/teach');");

$a = $db->getRows();

foreach($a as $row) {
	$short_desc = preg_replace("/http:\/\/www\.oneworld365\.org/","",$row['short_desc']);
	$sql = "UPDATE article SET short_desc = '".addslashes($short_desc)."' WHERE id = ".$row['id'].";";
	Logger::Msg($sql,'plaintext');
}





?>
