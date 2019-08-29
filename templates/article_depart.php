<? $oArticle = $this->Get('ARTICLE_OBJECT'); ?>

<div class="span8">



	<h1><?= $oArticle->GetTitle(); ?></h1>

	<p class="lead"><?= strip_tags($oArticle->GetDescShort()); ?></p>

	<?
		$oSearchResultPanel = $this->Get('oSearchResult');
		if (is_object($oSearchResultPanel))
			print $oSearchResultPanel->Render(); 
	?>
	
	<p>	<?= Article::convertCkEditorFont2Html($oArticle->GetDescFull(),"h3"); ?> </p>
	
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



<div class="span8">
	<div class="pull-right">
			<!-- AddThis Button BEGIN -->
		<div class="addthis_toolbox addthis_default_style addthis_32x32_style">
		<a class="addthis_button_facebook"></a>
		<a class="addthis_button_twitter"></a>
		<a class="addthis_button_pinterest_share"></a>
		<a class="addthis_button_email"></a>
		</div>
		<script type="text/javascript">var addthis_config = {"data_track_addressbar":false};</script>
		<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5101240045c9c761"></script>
		<!-- AddThis Button END -->	
	
	</div>
</div>


</section>
</div>
<!--  END article_01 -->
