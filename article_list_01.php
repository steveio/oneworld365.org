<?
/*
 * Display a list of article summaries
 * 
 * 1st 7 are full summaries
 * 
 * Next 25 are titles only
 * 
 * 
 */
$aArticle = $this->Get('ARTICLE_ARRAY');


if ((is_array($aArticle)) && (count($aArticle) >= 1)) {

	$i = 1;
	
	$iLarge = 4;
	
	foreach($aArticle as $oArticle) {
		$sTemplate = ($i++ > $iLarge) ? "article_summary_02.php" : "article_summary_01.php";		
		$oArticle->LoadTemplate($sTemplate);
		print $oArticle->Render();
		if ($i == $iLarge +1) {
			print "<p class='p_small'>More News...</p>";			
		
		}
		
	}
}
?>
