<?php

/*
 * Project Search Ajax Updater : 
 * Handles dynamic updating on project search ddlists :
 * 		activity, country, continent  
 * with associated project frequency counts based on selected criterea  
 *  
*/

require_once("./conf/config.php");
require_once("./classes/json.class.php");
require_once("./classes/logger.php");
require_once("./classes/db_pgsql.class.php");
require_once("./classes/activity.class.php");
require_once("./classes/continent.class.php");
require_once("./classes/country.class.php");

if (!is_object($db))
	$db = new db($dsn,$debug = false);

require_once("./classes/Brand.php");
$oBrand = new Brand(array());
$oBrand->site_id = $_CONFIG['site_id'];


$aResponse = array();
$aResponse['status'] = false;
$aResponse['errorMsg'];


// VALIDATE Inputs ----------------------------------------

if (!isset($_GET['term']) && !in_array($_GET['a'],array('dispatch','update'))) {
	$aResponse['errorMsg'] = 'Invalid search action';	
	sendResponse($aResponse);
}

$action = isset($_GET['a']) ? $_GET['a'] : "autocomplete";
$aSearchParams = array();
$aSearchParams['search-panel-activity'] = trim(urldecode($_GET['act']));
$aSearchParams['search-panel-destinations'] = trim(urldecode($_GET['d'])); 



// Process Request ----------------------------------------


if ($action == "dispatch") {

	require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrSearchPanelSearch.php");
	
	$oSolrSearchPanelSearch = new SolrSearchPanelSearch;
	$oSolrSearchPanelSearch->setup($aSearchParams);	
	$oSolrSearchPanelSearch->saveInSession();
		
	$aResponse['status'] = true;
	$aResponse['action'] = $action; 
	$aResponse['url'] = $oSolrSearchPanelSearch->getForwardUrl();
	
	//var_dump($oSolrSearchPanelSearch);
	//var_dump($aResponse);
	//die(__FILE__."::".__LINE__);
	
	sendResponse($aResponse);
} elseif ($action == "autocomplete") {
	$q = strtolower(preg_replace("/[^a-zA-Z- ]/","",$_GET['term']));
	$sql = "SELECT id,name FROM continent WHERE LOWER(name) like '".$q."%' 
			UNION 
			SELECT id,name FROM country WHERE LOWER(name) like '".$q."%'
			ORDER BY name asc 
			LIMIT 25";
	$db->query($sql);
	if ($db->getNumRows() >= 1) {
		$aResult = array();
		$aRows = $db->getRows();
		foreach($aRows as $aRow)
		{
			$objResult = new stdClass();
			$objResult->id = $aRow['id'];
			$objResult->label = $aRow['name'];
			$objResult->value = trim($aRow['name']);
			$aResult[] = $objResult;
		}
		sendResponse($aResult);
	}
	$objResult = new stdClass();
	$objResult->id = null;
	$objResult->label = '';
	$objResult->value = '';
	$aResult = array();
	$aResult[] = $objResult;
	sendResponse($aResult);
}


if ($action == "update") {
	
	require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrSearch.php");
	require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrPlacementSearch.php");
	require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrCompanySearch.php");
	require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrQuery.php");
	require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrSearchPanel.php");
	
	$oSolrSearchPanel = new SolrSearchPanel;
	
	$fq = array();
	$fq['profile_type'] = "1";
	//$fq['category_id'] = "7";
	
	// now add activity, country or continent filters
	if (isset($aSearchParams['search-panel-activity']) && $aSearchParams['search-panel-activity'] != "NULL") {
		$fq['activity'] = '"'.$aSearchParams['search-panel-activity'].'"';
	}
	if (isset($aSearchParams['search-panel-country']) && $aSearchParams['search-panel-country'] != "NULL") {
		$fq['country'] = '"'.$aSearchParams['search-panel-country'].'"';
	}
	if (isset($aSearchParams['search-panel-continent']) && $aSearchParams['search-panel-continent'] != "NULL") {
		$fq['continent'] = '"'.$aSearchParams['search-panel-continent'].'"';
	}
	
	$oSolrSearchPanel->setFilterQuery($fq);
	
	$aFacetField = array();
	$aFacetField[] = array("country" => "country");
	$aFacetField[] = array("continent" => "continent");
	$aFacetField[] = array("activity" => "activity");

	$oSolrSearchPanel->setFacetField($aFacetField);
	$oSolrSearchPanel->setup();

	$facetTypes = array('activity','country','continent');
	foreach($facetTypes as $key) {
		$aResponse['facet'][$key]['name'] = $key;
		$aResponse['facet'][$key]['data'] = $oSolrSearchPanel->getFacetFieldResultByKey($key);
	}

	$aResponse['action'] = $action;
	$aResponse['status'] = true;
		
	sendResponse($aResponse);		
}

//die(__FILE__."::".__LINE__);




// Send Response -----------------------------------------------

function sendResponse($aResponse) {

	/* return response back to the caller */
	$oJson = new Services_JSON;
	header("Expires: Sat, 1 Jan 2005 00:00:00 GMT");
	header("Last-Modified: ".gmdate( "D, d M Y H:i:s")."GMT");
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
	
	header('Content-type: application/x-json');
	print $oJson->encode($aResponse);
	die();
}



?>
