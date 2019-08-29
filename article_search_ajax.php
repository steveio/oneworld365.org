<?php


/*
 * Handles :
 * 	- searching for a collection of articles mapped to a specific uri
 *  - associating articles mapped to specific uri to an article
 * 
 * @param $_GET['m'] mode {search|map}
 * @param $_GET['uri'] string section uri including any fuzzy patterns (eg % )
 * @param $_GET['aid'] optional int article id
 *  
 */

require_once("./conf/config.php");
require_once("./classes/json.class.php");
require_once("./classes/db_pgsql.class.php");
require_once("./classes/logger.php");
require_once("./classes/template.class.php");
require_once("./classes/link.class.php");
require_once("./classes/article.class.php");


$db = new db($dsn,$debug = false);



$aResponse = array();
$aResponse['retVal'] = false;
$aResponse['msg'] = "";



$mode = $_GET['m'];
$uri = $_GET['uri'];
$template = $_GET['t'];
$match = $_GET['match'];
$bits = explode("::",$_GET['wid']);
$website_id = array();
foreach($bits as $id) {
	if (is_numeric($id)) $website_id[] = $id;
}

if (!in_array($mode,array("search","map"))) {
	$aResponse['msg'] = "ERROR : Invalid mode";
	sendResponse($aResponse);
}
if (preg_match("/[^a-zA-Z0-9\/ _\-\%]/",$uri)) {
	$aResponse['msg'] = "ERROR : Invalid URI";
	sendResponse($aResponse);
}
if (preg_match("/[^a-zA-Z0-9\/ _\-\%\.]/",$template)) {
	$aResponse['msg'] = "ERROR : Invalid template name";
	sendResponse($aResponse);
}
if(is_array($website_id) && count($website_id) > 1) {
	foreach($website_id as $id) {
		if (!is_numeric($id)) {
			$aResponse['msg'] = "ERROR : Invalid (non-numeric) website id";
			sendResponse($aResponse);
		}
	}
}


if ($mode == "map") {
	$id = $_GET['id'];
	if(!is_numeric($id)) {
		$aResponse['msg'] = "ERROR : Invalid Article ID";
		sendResponse($aResponse);		
	}
}


if ($mode == "search") { /* search for articles published to uri and return a result list */

	$bUnpublished = ($uri == "UNPUBLISHED") ? true : false;
	
	$oArticleCollection = new ArticleCollection();
	if ($match == ARTICLE_SEARCH_MODE_EXACT) {
		$oArticleCollection->SetSearchMode(ARTICLE_SEARCH_MODE_EXACT);
	}
	$oArticleCollection->GetBySectionId($website_id,$uri,$getAttachedObj = false,$bUnpublished);
	
	if ($oArticleCollection->Count() < 1) {
		$aResponse['msg'] = "No articles found matching uri: ".$uri."<br />Try again with a pattern match eg %".$uri;
		sendResponse($aResponse);
	} 
	
	/* render articles as a browse/select list */
	$oArticleCollection->LoadTemplate($template);
	
	$aResponse['retVal'] = true;
	$aResponse['msg'] = "Found ".$oArticleCollection->Count()." articles.";
	$aResponse['html'] = $oArticleCollection->Render(); 
	sendResponse($aResponse);
	
	
} elseif ($mode == "map") { /* map articles published to uri to article id */
	

}




function sendResponse($aResponse) {


	/* return response back to the caller */
	$oJson = new Services_JSON;
	header('Content-type: application/x-json');
	print $oJson->encode($aResponse);
	die();	

}

?>
