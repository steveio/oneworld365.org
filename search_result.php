<?


/*
 * search_result.php
 * 
 * Handles requests thats display result sets
 * 		eg. /country/<country-name>
 * 			/continent/<continent-name>
 * 			/<activity-name>
 * 			/<category-name>
 * 			/search/<search-phrase> 
 * 
 * 
 * 
 * 
 */

require_once("/www/vhosts/oneworld365.org/htdocs/classes/http.php");
require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrSearchPanelSearch.php");


// 301 redirect the old pager URL's eg ?&P1=2 to the base url
if (isset($_REQUEST['P1']) || isset($_REQUEST['P2'])) {
	$url = $_CONFIG['url'].$sUri;
	Http::Header(301,$url);
}



/* retrieve any associated article @todo - more efficient way to parse uri / handle query string */
$sUri = preg_replace("/[^a-zA-Z0-9_\?\-\/ ]/","",urldecode($_SERVER['REQUEST_URI']));
if (preg_match("/\?/",$sUri)) {
	$arrUri = explode("?",$sUri);
	$sUri = $arrUri[0];
}


$oArticle = new Article;
$oArticle->SetAttachedArticleFetchLimit(10);
$oArticle->SetFetchProfiles(FALSE);
$oArticle->SetChildFetchMode(FETCHMODE__SUMMARY);
$oArticle->SetFetchCurrentMappingOnly(TRUE);


if (!$oArticle->Get($_CONFIG['site_id'],$sUri)) {
	/* if no article exists for this uri, use default title, description */
	$sTitle = Search::ProcessUri("//".$_REQUEST['cat_name']);
	$oArticle->SetTitle($sTitle);
}



$aPageOptions = array();
$aPageOptions[ARTICLE_DISPLAY_OPT_PLACEMENT] = "t";
$aPageOptions[ARTICLE_DISPLAY_OPT_ORG] = "t";
$aPageOptions[ARTICLE_DISPLAY_OPT_ARTICLE] = "t";

if (is_object($oArticle)) {
	// overide default content features if options were explicitly set during article publication
	if (is_object($oArticle->GetMappingBySectionUri($sUri))) {
		$aPageOptions = $oArticle->GetMappingBySectionUri($sUri)->GetOptions();
	}	
}


$_REQUEST['page_title'] = $oArticle->GetTitle();
if (strlen($oArticle->GetMetaDesc()) > 1) {
	$_REQUEST['page_meta_description'] = $oArticle->GetMetaDesc(); 
} else {
	$_REQUEST['page_meta_description'] = $oArticle->GetDescShort();
}
if (strlen($oArticle->GetMetaKeywords()) > 1) {
	$_REQUEST['page_keywords'] = $oArticle->GetMetaKeywords();
} else {
	$_REQUEST['page_keywords'] = "";
}


$oSearchResultPanel = new Layout();
$oSearchResultPanel->Set("URI",$sUri);

/* have a look for any search params in session */
$oSolrSearchPanelSearch = SolrSearchPanelSearch::getFromSession();

if (is_object($oSolrSearchPanelSearch)) {
	$oSolrSearchPanelSearch->setFiltersByUri($sUri);

	if ($oSolrSearchPanelSearch->filterEnabled('activity')) {
		$oSearchResultPanel->Set("FACET_ACTIVITY",$oSolrSearchPanelSearch->getFilterAsCheckbox('activity'));		
	}

	if (is_numeric($oSolrSearchPanelSearch->getDurationFromId)) {
		//$oSearchResultPanel->Set("FACET_DURATION_FROM","<select id=''><option></option></select>");
	}
	
	if (is_numeric($oSolrSearchPanelSearch->getDurationToId)) {
		$oSearchResultPanel->Set("FACET_DURATION_FROM",$oSolrSearchPanelSearch->getFilterAsCheckbox('activity'));
	}
	
	
/*
$oSearchResultPanel->Set("FACET_COUNTRY",$sUri);
$oSearchResultPanel->Set("FACET_CONTINENT",$sUri);

*/
}

$oSearchResultPanel->Set('ARTICLE_DISPLAY_OPT_PTITLE',$aPageOptions[ARTICLE_DISPLAY_OPT_PTITLE]);
$oSearchResultPanel->Set('ARTICLE_DISPLAY_OPT_OTITLE',$aPageOptions[ARTICLE_DISPLAY_OPT_OTITLE]);
$oSearchResultPanel->Set('ARTICLE_DISPLAY_OPT_PINTRO',$aPageOptions[ARTICLE_DISPLAY_OPT_PINTRO]);
$oSearchResultPanel->Set('ARTICLE_DISPLAY_OPT_OINTRO',$aPageOptions[ARTICLE_DISPLAY_OPT_OINTRO]);


$oSearchResultPanel->Set('HIDE_FILTERS',FALSE);
$oSearchResultPanel->LoadTemplate('search_result.php');


if (is_numeric($oArticle->GetId()))
{
/**
 * Get Related Articles
 * @var Ambiguous $aProfile
 */
global $solr_config;

require_once(ROOT_PATH."/classes/SolrSearch.php");
require_once(ROOT_PATH."/classes/SolrMoreLikeSearch.php");

global $solr_config;
$oSolrMoreLikeSearch = new SolrMoreLikeSearch($solr_config);
$aFilterQuery = array();
$oSolrMoreLikeSearch->setRows(10);
$aRelatedArticle = $oSolrMoreLikeSearch->getRelatedArticle($oArticle->GetId(),$aFilterQuery);
$oArticle->GetArticleCollection()->AddFromArray($aRelatedArticle);


// get some related placements
$oSolrMoreLikeSearch = new SolrMoreLikeSearch($solr_config);
$oSolrMoreLikeSearch->getPlacementsByArticle($oArticle->GetId());
$oSolrMoreLikeSearch->setRows(10);
$aProfileId = $oSolrMoreLikeSearch->getId();

if (count($aProfileId) >= 1) {
    $oArticle->SetAttachedProfile($fetch = FALSE,$aProfileId);
}
}

?>




<? require_once("./header_html.php"); ?>







<div class="search-result row">

<!-- start: Page section -->
<section id="page-sidebar" class="span12">

	<div class="page-inner">

    <div class="span12" style="margin: 20px;">
    <div class="pull-right sharethis-inline-share-buttons"></div>
    </div>


	<div class="row-fluid">
		
		<h1><?=  $oArticle->GetTitle();  ?></h1>
		<p class='lead'><?=  strip_tags($oArticle->GetDescShort());  ?></p>
	
		<hr />
		
	   	<?=  $oSearchResultPanel->Render(); ?>

	<div class='row-fluid'>
		<div class="span12">
        	<p><?= Article::convertCkEditorFont2Html($oArticle->GetDescFull(),"h3"); ?> </p>
        </div>
	</div>
	</div>


<!--  BEGIN Related Article -->
<?
if (is_array($oArticle->GetArticleCollection()->Get())) { 
?>

    <div class="span12 ">    
	<div class="search-result"><?
 
            $aArticle = $oArticle->GetArticleCollection()->Get();
            $limit = 10;

            for ($i=0;$i<$limit;$i++) {
                    if (is_object($aArticle[$i])) {
                            $aArticle[$i]->SetImgDisplay(FALSE);
                            $aArticle[$i]->LoadTemplate("article_summary_search_result.php");
                            print $aArticle[$i]->Render();
                    }
            } ?>
    </div>
    </div><?php
}
?>
<!--  END Related Article -->


<div class="span12 article-profiles">
<?
/*
if (count($oArticle->GetAttachedProfile()) >= 1) {
    	print "<h3>Related Projects</h3>";
        $aProfile = $oArticle->GetAttachedProfile();
        //shuffle($aProfile);
		$limit = 9;
		print "<div class='search-result row-fluid'>"; 
        for($i=0; $i<$limit; $i++) {
            $oProfile = array_shift($aProfile);
            if (!is_object($oProfile)) continue;
                $oProfile->LoadTemplate("featured_project.php");
                print $oProfile->Render();
            }
        print "</div>";
}
*/
?>
</div>


	</div>

</section>


</div>

<?php 

include("./footer.php");
?>
