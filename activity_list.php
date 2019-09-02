<?

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/www/vhosts/www.oneworld365.org/logs/php_error.log');
error_reporting(E_ALL &~ E_NOTICE &~ E_STRICT);


/* SOLR Search Engine */
require_once(ROOT_PATH."/classes/SolrSearch.php");
require_once(ROOT_PATH."/classes/SolrMoreLikeSearch.php");
require_once("./classes/BalancedDistributor.php");


/*
 * Display a list of activities grouped by category
 * 
 */

$oCategory = new Category($db);
$oCategories = $oCategory->GetCategoriesByWebsite($_CONFIG['site_id'],"OBJECTS");

foreach($oCategories as $oCategory) {
	$oCategory->oArticle = getArticle($_CONFIG['site_id'],"/category/".$oCategory->url_name);
	if (!$oCategory->oArticle) {
		$oArticle = new Article();
		$oArticle->SetTitle($oCategory->name);
		$oArticle->SetDescShort($oCategory->desc);
		$oCategory->oArticle = $oArticle;
	} else {
		// get some related placements
		$oSolrMoreLikeSearch = new SolrMoreLikeSearch($solr_config);
		$oSolrMoreLikeSearch->getPlacementsByArticleBalanced($oCategory->oArticle->GetId(),'text');
		$oSolrMoreLikeSearch->setRows(64);
		$aProfileId = $oSolrMoreLikeSearch->getId();
		$oCategory->oArticle->SetAttachedProfile($fetch = FALSE,$aProfileId);
	}	
	
	$oActivityApi = new Activity();
	$oCategory->aActivity = $oActivityApi->GetByCategoryId($oCategory->id);
	foreach($oCategory->aActivity as &$oActivity) {
		$oActivity->oArticle = getArticle($_CONFIG['site_id'], "/".$oActivity->url_name);
		if (!$oActivity->oArticle) {
			$oArticle = new Article();
			$oArticle->SetTitle($oActivity->name);
			$oArticle->SetDescShort($oActivity->desc);
			$oActivity->oArticle = $oArticle;
		}
	}
}



function getArticle($iSiteId, $sUri) {
	$oArticle = new Article;
	$oArticle->SetAttachedArticleFetchLimit(1);
	$oArticle->SetFetchProfiles(FALSE);
	$oArticle->SetChildFetchMode(FETCHMODE__SUMMARY);
	$oArticle->SetFetchCurrentMappingOnly(TRUE);
	if ($oArticle->Get($iSiteId,$sUri)) return $oArticle;
	
}
?>

<div>
<div class="span12">

<section class="news">
<div class="row-fluid">
<h1>All Activities with One World 365</h1>
<?php
foreach($oCategories as $oCategory) {
?>
<div class="span12" style="margin-bottom: 22px;">
	<div class="news-item span6">
		<div class="pull-center">
		<?php 
		if (is_object($oCategory->oArticle) && is_object($oCategory->oArticle->GetImage(0))) {
			print "<a href='/category/". $oCategory->url_name."' title='".$oCategory->oArticle->GetTitle()."' class'thumbnail'>";
			print $oCategory->oArticle->GetImage(0)->GetHtml("_mf",$oCategory->oArticle->GetTitle());
			print "</a>";
		} 
		?>
		</div>
		<h3><a href="/category/<?= $oCategory->url_name ?>" title="<?= $oCategory->oArticle->GetTitle(); ?>"><?= $oCategory->oArticle->GetTitle(); ?></a></h3>
		<div>
		<?php 
        if (is_object($oCategory->oArticle) && is_array($oCategory->oArticle->GetAttachedProfile())) {
			foreach($oCategory->oArticle->GetAttachedProfile() as $oProfile) {
				if (!is_object($oProfile)) continue;
				$oProfile->LoadTemplate("featured_project_image_matrix.php");
				print $oProfile->Render();

			}
        }
        ?>
		</div>
	</div>
	<div class="news-item span6" style="margin-top: 40px;">
	<?php foreach ($oCategory->aActivity as $oActivity) { ?>
		<ul>
			<li><a href="/<?= $oActivity->url_name; ?>" title="<?= $oActivity->title; ?>"><?= $oActivity->oArticle->GetTitle(); ?></a></li>
		</ul>
	<?php } ?>
	</div>
	<hr />
</div>
<?php 
}
?>
</div>


</div>
</div>
