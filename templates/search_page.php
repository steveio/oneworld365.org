<?  if ($this->Get('DISPLAY_404')) { ?>
	<div class="row">
	<div class="search-page-notify span12">
	
		<div class="alert alert-error">
		<h2>404 Not Found - Sorry the requested page was not found.</h2>
		</div>
	</div>
	</div>
<?php } ?>
<div class="row">

<div class="search-page span12">


<section>

<?
$oArticle = $this->Get('ARTICLE');
?>

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


<div class="span12 destinations" style="margin: 0px">
<div class="row-fluid">
	<div class="destination-list">

	<?php 
	
	$aContinent = $this->Get('CONTINENT_ARRAY');
	$aCountry = $this->Get('COUNTRY_ARRAY');
	
	foreach ($aContinent as $oContinent) {
	
		print "<div class='row'>";
		print "<hr />";
		print "<ul class='unstyled'>";
		print "<li><h3><a name=".$oContinent->url_name." title='".$oContinent->name."' href='/continent/".$oContinent->url_name."' >".$oContinent->name."</a></h3></li>";
		print "<ul class='unstyled country'>";
		foreach ($aCountry as $oCountry) {

			if ($oCountry->continent_id == $oContinent->id) {
				print "<li><a href='".$_CONFIG['url']."/country/".$oCountry->url_name."' title='$oCountry->name'>".$oCountry->name."</a></li>";
			}
		}
		print "</ul>";
		print "</ul>";
		print "</div>";
	}
	
	
	?>
	</div>	
</div>
</div>

<div class="row-fluid">
<div class="span12 sponsored-brands">
<h5>Featured Organisations:</h5>
<?php
foreach ($this->Get('BRANDS') as $oProfile) {
                        if (is_object($oProfile->GetImage(0,LOGO_IMAGE))) {
                        ?>
                        <a data-original-title="<?= $oProfile->GetTitle() ?>" data-placement="top" rel="tooltip" href=" <?= $oProfile->GetProfileUrl() ?>" title="<?= $oProfile->GetTitle() ?> - <?= strip_tags($oProfile->GetDescShort()) ?>"><?= $oProfile->GetImage(0,LOGO_IMAGE)->GetHtml("_sm",$oProfile->GetTitle(),'',FALSE); ?></a>
                <?php
                        }
                }
?>
</div>
</div>


</section>


</div>
</div>
