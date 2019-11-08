<?

/*
* Populate cache DB table with uri's to be cached
*
* Run nightly by cron
*
*/

$brand = (strlen($argv[1]) > 1) ? $argv[1] : $_GET['BRAND'];
if (strlen($brand) < 1) die("ERROR : BRAND identifier must be supplied");

$mode = (strlen($argv[2]) > 1) ? $argv[2] : 'ALL';
if (strlen($mode) < 1) die("ERROR : MODE [ ALL | DELTA ] must be supplied");


include("/www/vhosts/oneworld365.org/htdocs/conf/config.php");

include(ROOT_PATH."/classes/db_pgsql.class.php");
include(ROOT_PATH."/classes/logger.php");

ini_set('display_errors', 0);

$db = new db($dsn,$debug = false);

if ($mode == "ALL") $db->query("DELETE from cache");


$strWhere = ($mode == "DELTA") ? " AND last_updated > now()::date - interval '24 hours'" : "";
$db->query("SELECT url_name FROM ".$_CONFIG['company_table']." WHERE prod_type >= ".BASIC_LISTING." ".$strWhere." ORDER BY prod_type ASC, title ASC;");
$aCompany = $db->getObjects();

$strWhere = ($mode == "DELTA") ? " AND p.last_updated > now()::date - interval '24 hours'" : "";
$db->query("SELECT c.url_name || '/' || p.url_name as url_name FROM ".$_CONFIG['placement_table']." p, company c WHERE p.company_id = c.id ".$strWhere." ORDER BY p.title ASC;");
$aPlacement = $db->getObjects();

$strWhere = ($mode == "DELTA") ? " AND a.last_updated > now()::date - interval '24 hours'" : "";
$db->query("SELECT m.section_uri as url_name FROM article_map m, article a WHERE m.article_id = a.id and m.website_id = 0 ".$strWhere. " ORDER BY a.title ASC");
$aArticle = $db->getObjects();

$aPages = array("/");

out($_CONFIG['site_id'],"",$aPages);
out($_CONFIG['site_id'],"/company/",$aCompany);
out($_CONFIG['site_id'],"/company/",$aPlacement);	
out($_CONFIG['site_id'],"",$aArticle);


function out($id,$prefix,$arr,$suffix = '') {
	global $db,$mode;
	if (!is_array($arr)) return false;
	foreach($arr as $oRes) {
		$value = is_object($oRes) ? $oRes->url_name : $oRes;
		if ($mode == "ALL")
		{
			$sql = "INSERT INTO cache (sid,uri,active) VALUES (0,'".$prefix.$value.$suffix."','F')";
			print $sql ."\n"; 
			$db->query($sql);
		} else {
                        $sql = "SELECT 1 FROM cache WHERE sid = 0 and uri = '".$prefix.$value.$suffix."'";
                        print $sql ."\n";
                        $db->query($sql);
			if ($db->getNumRows() == 1)
			{
                                $sql = "UPDATE cache set active = 'F' WHERE uri='".$prefix.$value.$suffix."'";
                                print $sql ."\n";
                                $db->query($sql);
	
			} else {
	                        $sql = "INSERT INTO cache (sid,uri,active) VALUES (0,'".$prefix.$value.$suffix."','F')";
        	                print $sql ."\n";
                	        $db->query($sql);
			}
		}
	}
}




?>
