<?php 


/*
 * Controller for /search page
 * 
 * 
 */



/*
 * Quick Search Panel
 * 
 */


require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrSearch.php");
require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrPlacementSearch.php");
require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrCompanySearch.php");
require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrQuery.php");
require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrSearchPanel.php");
require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrSearchPanelSearch.php");



$oSolrSearchPanel = new SolrSearchPanel;


// clear any previous search
SolrSearchPanelSearch::clearFromSession();

$fq = array();
$fq['profile_type'] = "1";
//$fq['category_id'] = "7";
$oSolrSearchPanel->setFilterQuery($fq);

$aFacetField = array();
$aFacetField[] = array("country" => "country");
$aFacetField[] = array("continent" => "continent");
$aFacetField[] = array("activity" => "activity");


$oSolrSearchPanel->setFacetField($aFacetField);
$oSolrSearchPanel->setup($_CONFIG['site_id']);


$oArticle = new Article();
$oArticle->SetFetchMode(FETCHMODE__SUMMARY);
$oArticle->SetAttachedArticleFetchLimit(8);
$oArticle->Get(0,"/travel");

/*
 * Country Select 
 * 
 */
// display the continent select list
$db->query("SELECT id,name,url_name FROM continent ORDER BY name asc");
$aContinent = $db->getObjects();

foreach($aContinent as $oContinent) {
	$aContinentId[] = $oContinent->id;
}

$db->query("SELECT c.id, c.name,c.url_name,c.continent_id, count(*) FROM country c, ".$_CONFIG['comp_country_map']." m WHERE m.country_id = c.id GROUP BY c.name,c.id,c.continent_id, c.url_name ORDER by name ASC;");
$aCountry= $db->getObjects();


/*
 * Marshal Templates
 * 
 */

$oSearchPanel = new Template;
$oSearchPanel->Set('TITLE_TAG','h2');
$oSearchPanel->Set('HOSTNAME',$_CONFIG['url']);
$oSearchPanel->Set('ACTIVITY_LIST',$oSolrSearchPanel->getFacetFieldResultByKey('activity'));
$oSearchPanel->Set('COUNTRY_LIST',$oSolrSearchPanel->getFacetFieldResultByKey('country'));
$oSearchPanel->Set('CONTINENT_LIST',$oSolrSearchPanel->getFacetFieldResultByKey('continent'));
$oSearchPanel->LoadTemplate("./search_panel.php");


$oSearchPage = new Template;
if ($display_404) {
	$oSearchPage->Set('DISPLAY_404',TRUE);
	
}
$oSearchPage->Set('SEARCH_PANEL',$oSearchPanel->Render());
$oSearchPage->Set('ARTICLE',$oArticle);
$oSearchPage->Set('COUNTRY_ARRAY',$aCountry);
$oSearchPage->Set('CONTINENT_ARRAY',$aContinent);

$oCompany = new Company($db);
$aSponsoredCompanyProfile = $oCompany->GetHomepageCompHTML(30,80);
$oSearchPage->Set('BRANDS',$aSponsoredCompanyProfile);


$oSearchPage->LoadTemplate("./search_page.php");
print $oSearchPage->Render();


?>
