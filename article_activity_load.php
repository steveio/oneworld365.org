<?php


$sBasePath = "/www/vhosts/oneworld365.org/htdocs";

require_once($sBasePath."/conf/config.php");
require_once($sBasePath."/classes/db_pgsql.class.php");
require_once($sBasePath."/classes/logger.php");
require_once($sBasePath."/classes/activity.class.php");
require_once($sBasePath."/classes/template.class.php");
require_once($sBasePath."/classes/article.class.php");
require_once($sBasePath."/classes/validation.class.php");

include("header_html.php");




$db = new db($dsn,$debug = false);


$oActivity = new Activity($db);

$aActivity = $oActivity->GetAll($r = "ROWS",$all = false);

Logger::Msg("Found ".count($aActivity) ." activities.");

foreach($aActivity as $a) {
	
	Logger::Msg("START PROCESSING ACTIVITY : ".$a['name']);
	
	$a['description'] = preg_replace("/<\/p>/","\n\r",$a['description']);
	$a['description'] = preg_replace("/<p>/","",$a['description']);
	
	$oArticle = new Article();
	$oArticle->SetFromArray(array(
								"title" => $a['name']
								,"short_desc" => $a['description']
								,"created_by" => 1
								,"published_status" => 1 /* PUBLISHED */
								)
							);


	$aResponse = array();
	if (!$oArticle->Save($aResponse)) {
		Logger::Msg($aResponse);	
	} else {
		Logger::Msg("Save OK : Article Id = ".$oArticle->GetId());
	}

	$aMapping = array(
						array(0 => "/".$a['url_name']),
						array(1 => "/".$a['url_name'])
					);
	
	if (!$oArticle->Map($aMapping,$bDeleteExisting = true,$aResponse)) {
		Logger::Msg($aResponse);
	} else {
		Logger::Msg("Mapping OK");
	}
	
	Logger::Msg("FINISH PROCESSING ACTIVITY : ".$a['name']."<br /><br />");
	
}








?>