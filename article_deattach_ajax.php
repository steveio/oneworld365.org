<?php


/*
 * Article Deattach - Delete a mapping entry from article_link
 * 
 * @param $_GET['pid'] parent article id
 * @param $_GET['aid'] attached article id
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


$pid = $_GET['pid'];
$aid = $_GET['aid'];

if (!is_numeric($pid) || !is_numeric($aid)) {
	$aResponse['msg'] = "Error: invalid parameters";
	sendResponse($aResponse);
} 

$oArticle = new Article();
$oArticle->SetId($pid); // instance of parent_article
if ($oArticle->RemoveAttachedArticle(array($aid))) {
	$aResponse['retVal'] = true;
	$aResponse['msg'] = "Success: Deattached article";
	//$aResponse['html'] = $oArticleCollection->Render(); 
	sendResponse($aResponse);
} else {
	$aResponse['msg'] = "Error: Deattach article failed";
	sendResponse($aResponse);	
}
	
	
	




function sendResponse($aResponse) {

	/* return response back to the caller */
	$oJson = new Services_JSON;
	header('Content-type: application/x-json');
	print $oJson->encode($aResponse);
	die();	

}

?>
