<?php

$bNoHTMLHeader = true;

include("./header.php");



$aArticle = Article::GetBySectionId($_CONFIG['site_id'],$sSectionUri = '/article');


//Logger::Msg($aArticle);


require_once("./header_html.php");


?>

<div id="rpanel" style="width: 260px;">
</div>

<div id='profile'>
<div id='profile_inner'>

<?
if (count($aArticle) >= 1) {
	foreach($aArticle as $oArticle) {
		$oArticle->LoadTemplate("article_summary_01.php");
		?>
	
		<?= $oArticle->Render(); ?>	
		
		<hr />	
	<?	
	}
} else {
	print "There are no articles published under this section yet.";
}
?>

</div><!--  end profile inner -->
</div><!--  end profile -->



<?

include("./footer.php");

?>
