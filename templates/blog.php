<? $oArticle = $this->Get('ARTICLE_OBJECT'); ?>
<div class="row">
<!-- start: Page section -->
<section class="article span12">

<div class="span12" style="margin: 20px;">
<div class="pull-right sharethis-inline-share-buttons"></div>
</div>


<div class="span12 article-body">

	<div class="pull-right image">
	<?
	if (is_object($oArticle->GetImage(0)) && $oArticle->GetImage(0)->GetHtml("_l",'')) {
		print $oArticle->GetImage(0)->GetHtml("_l",$oArticle->GetTitle());
	} elseif (is_object($oArticle->GetImage(0)) && $oArticle->GetImage(0)->GetHtml("_mf",'')) {
		print $oArticle->GetImage(0)->GetHtml("_mf",$oArticle->GetTitle());
	}
	?>
	</div>

	<h1><?= $oArticle->GetTitle(); ?></h1>

	<p class="lead"><?= strip_tags($oArticle->GetDescShort()); ?></p>

	<?
		$oSearchResultPanel = $this->Get('oSearchResult');
		if (is_object($oSearchResultPanel))
			print $oSearchResultPanel->Render(); 
	?>

	<div class='lead'>	
	<p>	<?= Article::convertCkEditorFont2Html($oArticle->GetDescFull(),"h3"); ?> </p>
	</div>
	
</div>


<!-- BEGIN display blog articles -->
<div class="row-fluid">
        <?
        if (is_array($oArticle->GetArticleCollection()->Get())) {
            $aArticle = $oArticle->GetArticleCollection()->Get();

        $oPager = new PagedResultSet();
		$oPager->SetResultsPerPage(30);
		$oPager->GetByCount($oArticle->GetAttachedArticleTotal(),"Page");
        $count = count($aArticle);

        for ($i=0;$i<$count;$i++) {
                if (is_object($aArticle[$i])) {
                        $aArticle[$i]->SetImgDisplay(FALSE);
                        $aArticle[$i]->LoadTemplate("blog_article.php");
                        print $aArticle[$i]->Render();
                }
        }

		print '<div id="pager" class="span12 pagination pagination-large pagination-centered">';	
		print $oPager->RenderHTML();
		print '</div>';
    } ?>
</div>
<!--  END display blog articles -->


</section>
</div>
