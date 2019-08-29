<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);



/*
 * Handles :
 * 	- attaching/ detaching links from associated objects
 * 
 *  
 */

require_once("./conf/config.php");
require_once("./classes/json.class.php");
require_once("./classes/db_pgsql.class.php");
require_once("./classes/logger.php");
require_once("./classes/template.class.php");
require_once("./classes/article.class.php");


$db = new db($dsn,$debug = false);


$aResponse = array();
$aResponse['retVal'] = false;
$aResponse['msg'] = "";


$mid = $_POST['mid'];
$opts = $_POST['opts'];
$search_keywords = trim($_POST['q']);
$p_title = trim($_POST['pt']);
$o_title = trim($_POST['ot']);
$n_title = trim($_POST['nt']);
$p_intro = trim($_POST['pi']);
$o_intro = trim($_POST['oi']);


if (!is_numeric($mid)) {
	$aResponse['msg'] = "ERROR : Invalid / Missing Mapping ID";
	sendResponse($aResponse);
}

$opts_array = array();

$opts_bits = explode("::",$opts);
foreach($opts_bits as $opt) {
	$bits = explode("_",$opt);
	$opt_id = $bits[2]; 
	$opt_val =  $bits[3];
	if (is_numeric($opt_id) && in_array($opt_val,array("T","F"))) {
		$opts_array[$opt_id] =  $opt_val;
	}
} 


if (!is_array($opts_array) || count($opts_array) < 1) {
	$aResponse['msg'] = "ERROR : Invalid / empty content options array";
	sendResponse($aResponse);
}

$oContentMapping = new ContentMapping($mid,NULL,NULL);
$result = $oContentMapping->GetById();
if (!$result) {
	$aResponse['msg'] = "ERROR : Unable to retrieve mapping";
	sendResponse($aResponse);
}

$aTextFieldOpts = array(
					"search_keywords" => $search_keywords,
					"p_title" => $p_title,
					"o_title" => $o_title,
					"n_title" => $n_title,
					"p_intro" => $p_intro,
					"o_intro" => $o_intro
					);

$oContentMapping->SetOptions($mid,$opts_array, $aTextFieldOpts);




$aResponse['retVal'] = true;
$aResponse['msg'] = "SUCCESS: Updated article content options";
sendResponse($aResponse);

	

function sendResponse($aResponse) {

	/* return response back to the caller */
	$oJson = new Services_JSON;
	header('Content-type: application/x-json');
	print $oJson->encode($aResponse);
	die();	

}

?>
