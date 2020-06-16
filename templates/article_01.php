<!-- BEGIN article_01 -->
<?

$oArticle = $this->Get('ARTICLE_OBJECT');

$aPageOptions = $this->Get('aPageOptions');

?>

<?php if ($aPageOptions[ARTICLE_DISPLAY_OPT_ADS] != "f") { ?>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
 (adsbygoogle = window.adsbygoogle || []).push({
      google_ad_client: "ca-pub-9874604497476880",
      enable_page_level_ads: true
 });
</script> 
<?php  } ?>


<div class="row">
<!-- start: Page section -->
<section class="article span12">

<?php if ($aPageOptions[ARTICLE_DISPLAY_OPT_SOCIAL] != "f") { ?>
<div class="span12" style="padding: 10px;">
<div class="pull-right sharethis-inline-share-buttons" style="padding-bottom: 10px;"></div>
</div>
<?php } ?>


<div class="span12 article-body">

    <? if ($aPageOptions[ARTICLE_DISPLAY_OPT_IMG] != "f") { ?>
	<div class="pull-right span6" style="padding: 6px;">
	<?
	if (is_object($oArticle->GetImage(0)) && $oArticle->GetImage(0)->GetHtml("",'')) {
		print $oArticle->GetImage(0)->GetHtml("",$oArticle->GetTitle());
	} elseif (is_object($oArticle->GetImage(0)) && $oArticle->GetImage(0)->GetHtml("_mf",'')) {
		print $oArticle->GetImage(0)->GetHtml("_mf",$oArticle->GetTitle());
	}
	?>
	</div>
	<?php } ?>

	<h1><?= $oArticle->GetTitle(); ?></h1>

    	<div class="row-fluid">
    		<p class="lead"><strong><?= strip_tags($oArticle->GetDescShort()); ?></strong></p>

        <?
    	if ($aPageOptions[ARTICLE_DISPLAY_OPT_PLACEMENT] != "f") {
                    $oSearchResultPanel = $this->Get('oSearchResult');
                    if (is_object($oSearchResultPanel))
                            print $oSearchResultPanel->Render();
    	}
        ?>

		<div class="row-fluid lead">
					<?
					  // insert related profiles into article body
						$strArticleBody = Article::convertCkEditorFont2Html($oArticle->GetDescFull(),"h3");

						$aH2Blocks = explode("<h2>",$strArticleBody);
						$aH3Blocks = explode("<h3>",$strArticleBody);
						$aBlocks = (count($aH2Blocks) > count($aH3Blocks)) ? $aH2Blocks : $aH3Blocks;
						$strHeaderTag = (count($aH2Blocks) > count($aH3Blocks)) ? "<h2>" : "<h3>";

						if ($aPageOptions[ARTICLE_DISPLAY_OPT_PROFILE] != "f") {
    						// get related placements
    						$aProfile = $oArticle->GetAttachedProfile();
						} else {
						     $aProfile = array();   
						}

						$i = 0; // block index
						$iAdsInserted = 0;
						$lineCount = 0;
						for($i=0; $i<count($aBlocks);$i++)
						{

                            if ($i >= 1) print $strHeaderTag;
                            print $aBlocks[$i];
                            $lineCount += count(explode("\n",$aBlocks[$i]));
                            
                            // insert ads every nth block (except when block line count less than minimum)
                            if ($lineCount > 20 && count($aProfile) >= 1 && ($i > 1) && ($i % 4 == 0))
                            {
                            
                            	//$strTemplate = (($iAdsInserted % 2) == 0) ? "featured_project_list_col3.php" : "featured_project_list_sm.php";
                            	//$strProfileGroupName = (($iAdsInserted % 2) == 0) ? "aProfile" : "aCompany";
                            	$strTemplate = "featured_project_list_sm.php";
                            	$strProfileGroupName = "aProfile";
                            
                            	$aProfileGroup = array();
                                $iNumProfiles = 3;
                                for($j=0;$j<$iNumProfiles;$j++)
                                {
                                    $aProfileGroup[] = array_shift($$strProfileGroupName);
                                }
                            
                                $oTemplate = new Template();
                                $oTemplate->Set("PROFILE_ARRAY",$aProfileGroup);
                            	$oTemplate->Set("PROFILE_TYPE",$strProfileGroupName);
                                $oTemplate->LoadTemplate($strTemplate);
                                print $oTemplate->Render();
                            	$iAdsInserted++;
                            	$lineCount = 0;
                            }
				   }

			    ?>
    </div>
	</div>
</div>
<?
if (count($oArticle->GetAttachedLink()) >= 1) {
?>

  <div class="pull-right">
 	<ul class="unstyled">
			<li class="featureRelated"><span>Related Links</span>
			<? foreach ($oArticle->GetAttachedLink() as $oLink) { ?>
			<div style="padding-bottom: 6px;">
				<img src="./images/bullet.gif" alt="bullet" /> <a href="<?= $oLink->GetUrl(); ?>" title="<?= $oLink->GetTitle(); ?>"><?= $oLink->GetTitle(); ?></a>
			</div>
			<? } ?>
		</li>

	</ul>
	</div>
<?
}
?>

<?
if ($aPageOptions[ARTICLE_DISPLAY_OPT_REVIEW] != "f")
{
    $oReviewTemplate = $this->Get('oReview');
    if (is_object($oReviewTemplate)) { ?>
    <div class="row-fluid">
    <div class="span12">

    	<h3>Comments</h3>
    	<?php
    	print $oReviewTemplate->Render();
    	?>
    </div>
    </div><?
    }
}
?>


<?
if ($aPageOptions[ARTICLE_DISPLAY_OPT_ARTICLE] != "f")
{
    if (is_array($oArticle->GetArticleCollection()->Get()) && count($oArticle->GetArticleCollection()->Get()) >= 1) {
?>
    <!--  BEGIN Related Article -->
    <div class="span12 ">
	<h4>Related Articles</h4>
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
    </div>
    <!--  END Related Article -->
    <?php
    }
}
?>

<?php
/*
@deprecated featured profiles displayed in article body
if ($aPageOptions[ARTICLE_DISPLAY_OPT_PROFILE] != "f")
{
?>
    <!--  BEGIN Related Profile -->
    <div class="span12 article-profiles">
    <?
    if (count($aProfile) >= 1) {
    print "<h3>Related Opportunities</h3>";
		$limit = 8;
		print "<div class='search-result row-fluid'>";
        for($i=0; $i<$limit; $i++) {
            $oProfile = array_shift($aProfile);
            if (!is_object($oProfile)) continue;
						$oProfile->LoadTemplate("featured_project_list.php");
            print $oProfile->Render();
            }
        print "</div>";
    }
    ?>
    </div>
    <!--  END Related Profile --><?php
}
*/
?>


</section>
</div>
<!--  END article_01 -->
