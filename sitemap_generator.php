<?php


ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/www/vhosts/www.oneworld365.org/logs/php_error.log'); 
error_reporting(E_ALL &~ E_NOTICE &~ E_STRICT);


include("./conf/config.php");
require_once($_CONFIG['root_path']."/classes/db_pgsql.class.php");

/* establish database connection */
$db = new db($dsn,$debug = false);


$domain = "http://www.oneworld365.org";
$site_id = 0;

$header = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
EOF;

print $header."\n";

//<url><loc>http://www.example.com/index.html</loc></url>

// display the continent select list
$db->query("SELECT id,name,url_name FROM continent ORDER BY name asc");
$aContinent = $db->getObjects();
$db->query("SELECT c.id, c.name,c.url_name,c.continent_id FROM country c GROUP BY c.name,c.id,c.continent_id, c.url_name ORDER by name ASC;");
$aCountry= $db->getObjects();
foreach ($aContinent as $oContinent) {
	print "<url><loc>".$domain."/continent/".$oContinent->url_name."</loc></url>\n";
	foreach ($aCountry as $oCountry) {
		if ($oCountry->continent_id == $oContinent->id) {
			print "<url><loc>".$domain."/travel/".$oCountry->url_name."</loc></url>\n";
		}
	}
}

// activity pages
$db->query("SELECT a.url_name FROM activity a, cat_act_map m WHERE m.activity_id = a.id order by a.url_name asc");
$arr = $db->getObjects();
foreach ($arr as $c) {
	print "<url><loc>".$domain."/activity/".$c->url_name."</loc></url>\n";
}

// category pages
$db->query("SELECT c.url_name FROM category c, website_category_map m WHERE m.website_id = ".$site_id." AND m.category_id = c.id");
$arr = $db->getObjects();
foreach ($arr as $c) {
	print "<url><loc>".$domain."/category/".$c->url_name."</loc></url>\n";
}


// articles
$db->query("select '".$domain."'||section_uri as url from article_map where website_id = ".$site_id." and section_uri not like '/country%' and section_uri not like '/category%' and section_uri not like '/activity%' and section_uri not like '/continent%' and section_uri not like '/search%' order by section_uri asc;");
$arr = $db->getObjects();
foreach ($arr as $c) {
	print "<url><loc>".$c->url."</loc></url>\n";
}


// placements
$db->query("SELECT p.url_name,c.url_name as comp_url_name FROM ".$_CONFIG['placement_table']." p, ".$_CONFIG['company_table']." c WHERE p.company_id = c.id order by c.title asc");
$arr = $db->getObjects();
$comp_url_name = '';
foreach ($arr as $c) {
	if ($c->comp_url_name != $comp_url_name) {
		$comp_url_name = $c->comp_url_name;
		print "<url><loc>".$domain."/company/".$c->comp_url_name."</loc></url>\n";
	}
	print "<url><loc>".$domain."/company/".$c->comp_url_name."/".$c->url_name."</loc></url>\n";
}


$db->query("SELECT c.url_name FROM company c where prod_type < 1 order by c.title asc");
$arr = $db->getObjects();
foreach ($arr as $c) {
        print "<url><loc>".$domain."/company/".$c->url_name."</loc></url>\n";
}

print "</urlset>\n";


