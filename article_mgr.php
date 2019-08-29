<?php

$bNoHTMLHeader = true;

include("./header.php");


if (!$oAuth->oUser->isAdmin) AppError::StopRedirect($sUrl = $_CONFIG['url']."/client_login.php",$sMsg = "ERROR : You must be authenticated.  Please login to continue.");


$oArticle = new Article();


$oWebsite = new Website($db);
$sWebSiteListHTML = $oWebsite->GetSiteSelectList(array(),$checked = TRUE, $markup = "DIV");




foreach($_REQUEST as $k => $v) {
	if (preg_match("/art_/",$k)) {
		$id = preg_replace("/art_/","",$k);
		if ($v == "delete") $aDelete[] = $id;
	}
}

if (count($aDelete) >= 1) {
	foreach($aDelete as $id) {
		if (DEBUG) Logger::Msg("Delete : id = ".$id);
		$oArticle->SetId($id);
		if ($oArticle->Delete()) {
			$aResponse['msg'] = "SUCCESS : Deleted article.";
		}
	}
}


$aFilter = array();

if (strlen($_REQUEST['filter_uri']) >= 1) $aFilter['URI'] = $_REQUEST['filter_uri'];  

if (count($aFilter) >= 1) $aArticle = $oArticle->GetAll($aFilter);





include("./header_html.php");

?>


<!-- BEGIN Page Content Container -->
<div class="page_content content-wrap clear">
<div class="row pad-tbl clear">


<div id="msgtext" style="color: red; font-size: 10px;">
<?= AppError::GetErrorHtml($aResponse['msg']);  ?>
</div>


<form enctype="multipart/form-data" name="linkadmin" action="<? $_SERVER['PHP_SELF'] ?>" method="POST">



<div id='row800'>
	
<h1>Content Manager</h1>

<div class="row800">
	<span class="input_col">
		<input type="submit" title="Create a new article" onclick="javascript: window.location = './article_edit.php'; return false;" name="new" value="New Article" class="sub_col_but" />
		<!-- <input type="submit" title="Create a new article" onclick="javascript: go('./section_edit.php'); return false;" name="new" value="New Section" class="sub_col_but" /> -->		
	</span>
</div>



<div class="row800">

<h2>Manage Articles :</h2>

<p class="p_small">
	<ul>
		<li>Patterns: <span class="p_small">"%" = all  OR  "%africa" = contains "africa" OR  "/volunteer/animals" OR "UNPUBLISHED" = new articles</i></span></li>
	</ul>
</p>

<table cellspacing="2" cellpadding="4" border="0">	
<tr class="hi">
	<td align="right" width="800px">
	From:
	<input type="text" id="search_phrase" style="width: 150px;" value="<?= $_REQUEST['filter_uri'] ?>" />
	<input type="submit" onclick="javascript: ArticleSearch('<?= $_CONFIG['url'] ?>','search','','article_search_result_list_03.php'); return false;" name="article_search" value="Search" />
	Exact? <input type="checkbox" id="search_exact" name="search_exact" />
	</td>
</tr>
<tr>
	<td align="right">
		<div id="website_id" style="float: right;">
		<?= $sWebSiteListHTML; ?>
		</div>
	</td>
</tr>
</table>

<div id="article_search_msg"></div>
<div id="article_search_result"></div>

</div>	
</div>


</form>


</div>	
</div>

<?

include("./footer.php");

?>
	
