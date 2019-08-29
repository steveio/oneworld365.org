<?php





$_REQUEST['page_title'] = $oArticle->GetTitle();

if (strlen($oArticle->GetMetaDesc()) > 1) {
	$_REQUEST['page_meta_description'] = $oArticle->GetMetaDesc();
} else {
	$_REQUEST['page_meta_description'] = $oArticle->GetTitle();
}
if (strlen($oArticle->GetMetaKeywords()) > 1) {
	$_REQUEST['page_keywords'] = $oArticle->GetMetaKeywords();
} else {
	$_REQUEST['page_keywords'] = "";
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


if (count($oArticle->GetAttachedProfile() < 1) && (!preg_match("/\//",$sUri)) && (!preg_match("/\//",$sUri) && $sUri != "/blog")) {

	/* SOLR Search Engine */
	require_once(ROOT_PATH."/classes/SolrSearch.php");
	require_once(ROOT_PATH."/classes/SolrMoreLikeSearch.php");
	
	// get some related placements
	$oSolrMoreLikeSearch = new SolrMoreLikeSearch($solr_config);
	$oSolrMoreLikeSearch->getPlacementsByArticle($oArticle->GetId());
	$oSolrMoreLikeSearch->setRows(10);
	$aProfileId = $oSolrMoreLikeSearch->getId();

	if (count($aProfileId) >= 1) {
		$oArticle->SetAttachedProfile($fetch = FALSE,$aProfileId);
	}
}

switch($sUri) {
	case "/news" :
		$template = "news_section_01.php";
		break;
	case "/features" :
		$template = "features_section_01.php";
		break;
	case "/blog" :
		$template = "blog.php";
		break;
	default :
		$template = "article_01.php";
}


if (preg_match("/\/activity\//",$sUri) || preg_match("/\/category\//",$sUri)) {
	$oSearchResultPanel = new Layout();
	$oSearchResultPanel->Set("URI",$sUri);
	$oSearchResultPanel->Set('ARTICLE_DISPLAY_OPT_PTITLE',$aPageOptions[ARTICLE_DISPLAY_OPT_PTITLE]);
	$oSearchResultPanel->Set('ARTICLE_DISPLAY_OPT_OTITLE',$aPageOptions[ARTICLE_DISPLAY_OPT_OTITLE]);
	$oSearchResultPanel->Set('ARTICLE_DISPLAY_OPT_PINTRO',$aPageOptions[ARTICLE_DISPLAY_OPT_PINTRO]);
	$oSearchResultPanel->Set('ARTICLE_DISPLAY_OPT_OINTRO',$aPageOptions[ARTICLE_DISPLAY_OPT_OINTRO]);
		
	//$oSearchResultPanel->Set('HIDE_FILTERS',TRUE);
	$oSearchResultPanel->LoadTemplate('search_result.php');
	$oArticle->initTemplate();
	$oArticle->oTemplate->Set('oSearchResult',$oSearchResultPanel);
} else {
	$oArticle->initTemplate();
}

$oArticle->LoadTemplate($template);
include("./header_html.php");
print $oArticle->Render();
include("./footer.php");


?>
