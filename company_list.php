<?


$bNoHTMLHeader = true;

include("./header.php");

require_once("./header_html.php");

?>
<div class='enquiry row'>
<div class='span12'>
<?php

// DISPLAY SPONSORED COMPANY INFO

$oCompany = new Company($db);
$aComp = $oCompany->GetFeaturedComp($type = "BOTH");


$oArticle = new Article();
$oArticle->SetFetchMode(FETCHMODE__SUMMARY);
$oArticle->SetAttachedArticleFetchLimit(8);
$oArticle->Get(0,"/company");

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


	<div class='lead'>	
	<p>	<?= Article::convertCkEditorFont2Html($oArticle->GetDescFull(),"h3"); ?> </p>
	</div>
	
</div>


<div class="span12 article-body">
<?

// DISPLAY COMPANY A-Z
$letters = range("A","Z");
print "<div id='profile' style='margin-bottom: 10px;'>";
print "<h1>Company A-Z :</h1>";
foreach($letters as $letter) {
	if ($letter == strtoupper($_REQUEST['letter'])) {
		$css_style = "color: red;";
	} else {
		$css_style = "color: #CCCCCC;";
	}
	print "<a style='letter-spacing: 4px; text-decoration: none; $css_style' title='Display Companies Beginning : $letter' href='".$_CONFIG['url']."/company/a-z/".strtolower($letter)."'>".$letter ."</a>";
}

if ($_REQUEST['letter'] != "") {
	
	$db->query("select title,desc_short,url_name from company where title like '".strtoupper($_REQUEST['letter'])."%';");
	
	$aCompany = $db->getObjects();
	print "<p style='font-size: 10px;'>Results : </p>";
	print "<div style='position: relative; float: left; width: 680px; margin: 3px 2px 13px 3px; '>";
	foreach ($aCompany as $c) {
		print "<div style='position: relative; float: left; width: 220px; height: 40px; margin: 0px 2px 0px 2px;'>";
		print "<a class='c_title_sm' href='".$_CONFIG['url']."/company/".$c->url_name."' title='".$c->title." :".$c->desc_short." '>".stripslashes($c->title)."</a><br>";
		print "</div>";
	}
	print "</div>";
}
print "</div>";



function cmp($a, $b) {
  if ($a->title == $b->title) {
    return 0;
  } else {
    return ($a->title > $b->title) ? 1 : -1; // reverse order
  }
}

print "</div>";


/*
print "<div id='profile' style='margin-top: 10px;'>";
print "<h2>Featured Companies</h2>";
$aAll = array_merge($aComp['SPONSORED'],$aComp['ENHANCED'],$aComp['BASIC']);
usort($aAll, 'cmp');

foreach ($aAll as $c) {
	
	$oProfile = new CompanyProfile();
	$oProfile->SetFromArray($c);		
	$oProfile->GetImages();

	print "<div style='float: left; height: 86px; width: 580px; margin: 10px 20px 10px 0px;'>";
	print "<div style='width: 240px; height: 80px; float:left;'>";
	print "<a href='".$oProfile->GetProfileUrl()."' title='".$oProfile->GetTitle().":".$oProfile->GetDescShort()."'>";
	if (is_object($oProfile->GetImage(0,LOGO_IMAGE))) {
		print $oProfile->GetImage(0,LOGO_IMAGE)->GetHtml("_sm",$oProfile->GetTitle(),'',$outputSize = FALSE);
	}
	print "</a></div>";
	print "<a class='c_title_sm' href='".$oProfile->GetProfileUrl()."' title='".$oProfile->GetTitle().":".$oProfile->GetDescShort()."'>".$oProfile->GetTitle()."</a>";
	print "<p class='c_desc_sm'>".$oProfile->GetDescShort(140)."</p>";
	print "</div>";
	

}
print "</div>";	
*/

?>
</div>
</div>
<?

include("./footer.php");

?>
