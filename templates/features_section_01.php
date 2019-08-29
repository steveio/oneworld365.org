<? $oArticle = $this->Get('ARTICLE_OBJECT'); ?>
<div id="rpanel" style="width: 260px;">

</div>


<div id="article-container-01" style="width: 800px;">

<div style="float: right;; padding: 10px; margin: 0px 0px 0px 20px;">
<?
?>
</div>

<!--
<h1><?= $oArticle->GetTitle() ?></h1>

<p><?= $oArticle->GetDescShort() ?></p>

<p><?= $oArticle->GetDescFull() ?></p>
-->

</div> <!--  end article container -->

<div id="article-container-01" style="width: 800px;">


<?
$oArticleCollection = $this->Get('ARTICLE_OBJECT')->oArticleCollection;
if ($oArticleCollection->Count() >= 1) {
?>
<?
//$oArticleCollection->LoadTemplate("article_list_01.php");

$aArticle = $oArticleCollection->Get();

if ((is_array($aArticle)) && (count($aArticle) >= 1)) {

	$i = 1;
	
	$iLarge = 4;
	
	foreach($aArticle as $oArticle) {
		?>

		<div id="featured_item" style="float: left; width: 600px; margin-top: 20px;">
		<div id="f_x" class="hpModule">
		
	
		  <div class="hpSideBar" style="">
		  
		  	<? 
		  	if (count($oArticle->GetAttachedLink()) >= 1) {
		  	?>  
			<div style="margin-top: 0px;">  
			<ul>
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
		</div>
	
	 	<div class="hpModuleContent">
			<p class="p_small"><?= $oArticle->GetPublishedDate(); ?></p>
			<h1><?= $oArticle->GetTitle(); ?></h1>
			<?
				/* @todo - add a layout class */
				switch($oArticle->GetImageCount()) {
					case 1 :
						$open_tag = "<div style=\"float: right; padding: 6px;\">";
						$size = "_m";  
						$close_tag = "</div>";
						break;
					case 2 :
						$size = "_m";  /*medium size */
						break;
					default :
						$size = "_s";  /*small size */
						break;
				}
				foreach($oArticle->GetImages() as $oImage) {
					if (is_object($oImage)) {
						print $open_tag;
						print $oImage->GetHtml($size,$oArticle->GetTitle());
						print $close_tag;
					}
				}
			?>
	
			<p><?= $oArticle->GetDescShort(); ?></p>
			<p><?= $oArticle->GetDescFull(); ?></p>
			<a class="p_small" href="<?= $oArticle->GetUrl(); ?>" title="View <?= $oArticle->GetTitle(); ?>">Read more...</a>
			
			<hr />

<div style="float: left; width: 500px; margin-top: 6px; margin-left: 6px;">
<?


if (count($oArticle->GetAttachedProfile()) >= 1) {
        $aProfile = $oArticle->GetAttachedProfile();
        //shuffle($aProfile);
	$count = count($aProfile);
        for($i=0; $i<$count; $i++) {
                $oProfile = array_shift($aProfile);
		if (!is_object($oProfile)) continue;
                $oProfile->LoadTemplate("profile_small_nobg_05.php");
                print "<div style='float: left; width: 140px; height: 150px; margin-top: 10px;'>";
                print $oProfile->Render();
                print "</div>";

        }

}

?>
</div>


		</div>




	</div>
	</div>
		
		
		<?
	}
}

?>

<?
}
?>

</div>
