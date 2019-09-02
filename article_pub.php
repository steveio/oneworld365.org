<?php

$bNoHTMLHeader = true;

include("./header.php");


if (!$oAuth->oUser->isAdmin) AppError::StopRedirect($sUrl = $_CONFIG['url']."/client_login.php",$sMsg = "ERROR : You must be authenticated.  Please login to continue.");

if (!is_numeric($_REQUEST['id'])) AppError::StopRedirect($sUrl = $_CONFIG['url']."/article_mgr.php",$sMsg = "ERROR : You must be authenticated.  Please login to continue.");


$oArticle = new Article();

$oArticle->SetFetchMode(FETCHMODE__SUMMARY);

$oWebsite = new Website($db);
$sWebSiteListHTML = $oWebsite->GetSiteSelectList(array());


//Logger::Msg($_REQUEST);

/* unpublish (delete article mapping) */
$aMappingId = Mapping::GetIdByKey($_REQUEST,"map_");
if (count($aMappingId) >= 1) {
	foreach($aMappingId as $id) {
		$oArticle->MapDeleteById($id);
	} 
}	


if ($_REQUEST['publish']) {

	$oArticle->SetId($_REQUEST['article_id']);

	if ($oArticle->Publish($_REQUEST,$aResponse)) {
		$aResponse['msg'] = "SUCCESS : Published article to requested location(s)";
	}
	
}


if (!$oArticle->GetById($_REQUEST['id'])) {
	$aResponse['msg'] = "ERROR : Unable to retrieve article";
}


	




include("./header_html.php");

?>

<!-- BEGIN Page Content Container -->
<div class="page_content content-wrap clear">
<div class="row pad-tbl clear">


<h1>Article Publisher</h1>

<h3><b>Article:</b> <?= $oArticle->GetTitle(); ?></h3>


<div class="row800">
	<span class="input_col">
		<input type="submit" title="Create a new article" onclick="javascript: go('./article_edit.php?id=<?= $oArticle->GetId(); ?>'); return false;" name="new" value="Edit Article" class="sub_col_but" />
		<input type="submit" title="Create a new article" onclick="javascript: go('./article_edit.php'); return false;" name="new" value="New Article" class="sub_col_but" />
		<input type="submit" title="Create a new article" onclick="javascript: go('./article_mgr.php'); return false;" name="new" value="Article Manager" class="sub_col_but" />		
		<!-- <input type="submit" title="Create a new article" onclick="javascript: go('./section_edit.php'); return false;" name="new" value="New Section" class="sub_col_but" /> -->		
	</span>
</div>







<form enctype="multipart/form-data" name="edit_article" id="edit_article" action="<? $_SERVER['PHP_SELF'] ?>" method="POST">
<input type="hidden" name="article_id" value="<?= $oArticle->GetId(); ?>" />

	
<div class="row800">
<h2>Published To:</h2>

<div id="msgtext" style="color: red; font-size: 10px;">
<? 
$aResponse = (isset($aResponse['msg'])) ? $aResponse['msg'] : $aResponse; 
print AppError::GetErrorHtml($aResponse);  
?>
</div>

</div>

<div class="row800">

<table cellspacing="2" cellpadding="4" border="0" width="800px">
<tr>
	<th>Website / Section</th>
	<th>&nbsp;</th>
</tr>
<? 
$i = 1;
if (count($oArticle->GetMapping()) >= 1) {
	foreach($oArticle->GetMapping() as $oArticleMapping) {
	?>
	<? $class = ($class == "hi") ? "" : "hi"; ?>
	<tr class='<?= $class ?>'>
		<td width="80px" valign="top"><b><?= $oArticleMapping->GetLabel() ?></b></td>
		<td width="20px"><input type="submit" onclick="javscript: return confirm('Are you sure you wish to unpublish : <?= $oArticleMapping->GetLabel(); ?>?');" name="map_<?= $oArticleMapping->GetId() ?>" value="delete" /></td>
	</tr>	
	<tr class='<?= $class ?>'>
		<td width="100px" align="right" valign="top">
			<?php $checked = ($oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_PLACEMENT) == "t") ? "checked" : "" ; ?>
			Placement Results <input type="checkbox" name="opt_<?= $oArticleMapping->GetId() ?>_<?= ARTICLE_DISPLAY_OPT_PLACEMENT; ?>" <?= $checked ?> />&nbsp;&nbsp;
			<?php $checked = ($oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_ORG) == "t") ? "checked" : "" ; ?>
			Organisation Results <input type="checkbox" name="opt_<?= $oArticleMapping->GetId() ?>_<?= ARTICLE_DISPLAY_OPT_ORG; ?>" <?= $checked ?> />&nbsp;&nbsp;
			<?php $checked = ($oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_ARTICLE) == "t") ? "checked" : "" ; ?>
			Articles <input type="checkbox" name="opt_<?= $oArticleMapping->GetId() ?>_<?= ARTICLE_DISPLAY_OPT_ARTICLE; ?>" <?= $checked ?> />		
			<?php $checked = ($oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_PROFILE) == "f") ?  "" : "checked"; ?>
			Profiles <input type="checkbox" name="opt_<?= $oArticleMapping->GetId() ?>_<?= ARTICLE_DISPLAY_OPT_PROFILE; ?>" <?= $checked ?> />		
			
		</td>
		<td width="20px">&nbsp;</td>
	</tr>	
	<tr class='<?= $class ?>'>
		<td width="100px" align="right" valign="top">
			<?php $checked = ($oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_FEATURED_PROJECT) == "t") ? "checked" : "" ; ?>
			Hide Featured Projects Slider <input type="checkbox" name="opt_<?= $oArticleMapping->GetId() ?>_<?= ARTICLE_DISPLAY_OPT_FEATURED_PROJECT; ?>" <?= $checked ?> />&nbsp;&nbsp;
		
		   <?php $checked = ($oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_PARENT_TABS) == "t") ? "checked" : "" ; ?>
			Tabs from Parent URL <input type="checkbox" name="opt_<?= $oArticleMapping->GetId() ?>_<?= ARTICLE_DISPLAY_OPT_PARENT_TABS; ?>" <?= $checked ?> />&nbsp;&nbsp;
		</td>
		<td width="20px">&nbsp;</td>
	</tr>	
	<tr class='<?= $class ?>'>
		<td width="100px" align="right" valign="top">
			<i>Display article body text: </i>&nbsp;&nbsp;&nbsp;&nbsp;
			<?php $checked = ($oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_HEADER) == "t") ? "checked" : "" ; ?>
			in header <input type="radio" name="opt_rad_<?= $oArticleMapping->GetId() ?>_txtalign" value="opt_<?= $oArticleMapping->GetId() ?>_<?= ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_HEADER; ?>" <?= $checked ?> />&nbsp;&nbsp;
		
		   <?php $checked = ($oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_BODY) == "t") ? "checked" : "" ; ?>
			after results <input type="radio" name="opt_rad_<?= $oArticleMapping->GetId() ?>_txtalign" value="opt_<?= $oArticleMapping->GetId() ?>_<?= ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_BODY; ?>" <?= $checked ?> />&nbsp;&nbsp;

		   <?php $checked = ($oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_FOOTER) == "t") ? "checked" : "" ; ?>
			in footer <input type="radio" name="opt_rad_<?= $oArticleMapping->GetId() ?>_txtalign" value="opt_<?= $oArticleMapping->GetId() ?>_<?= ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_FOOTER; ?>" <?= $checked ?> />&nbsp;&nbsp;
			
		</td>
		<td width="20px">&nbsp;</td>
	</tr>	
	
	<tr class='<?= $class ?>'>	
		<td width="600px;" align="right" valign="top">
			<table cellpadding="0" cellspacing="0" border="0">
				<tr>
				<td width="400px;" align="right">Placement Result Title: <br /><input id="ptitle_<?= $oArticleMapping->GetId() ?>" type="text" name="ptitle_<?= $oArticleMapping->GetId() ?>" value="<?= $oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_PTITLE); ?>" maxlength="128" /></td>
				<td width="400px;" align="right">Org Result Title: <br /><input id="otitle_<?= $oArticleMapping->GetId() ?>" type="text" name="otitle_<?= $oArticleMapping->GetId() ?>" value="<?= $oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_OTITLE); ?>" maxlength="128" /></td>
				</tr>
			</table> 
		</td>
		<td width="20px"></td>
	</tr>	

	<tr class='<?= $class ?>'>
		<td width="600px;" align="right" valign="top">
			<table cellpadding="0" cellspacing="0" border="0">
				<tr>
				<td width="400px;" align="right">Placement Intro: <textarea id="pintro_<?= $oArticleMapping->GetId() ?>" type="text" name="pintro_<?= $oArticleMapping->GetId() ?>" value="<?= $oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_PINTRO); ?>" style="width: 300px; height: 60px;" maxlength="512"><?= $oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_PINTRO); ?></textarea></td>
				<td width="400px;" align="right">Organisation Results Intro: <textarea id="ointro_<?= $oArticleMapping->GetId() ?>" type="text" name="ointro_<?= $oArticleMapping->GetId() ?>" value="<?= $oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_OINTRO); ?>" style="width: 300px; height: 60px;" maxlength="512"><?= $oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_OINTRO); ?></textarea></td>
				</tr>
			</table>
		</td>
		<td width="20px"></td>
	</tr>	
	
	
	<tr class='<?= $class ?>'>
		<td width="600px" align="right" valign="top">
			<table cellpadding="0" cellspacing="0" border="0">
				<tr>
				<td width="400px;" align="right">News Title: <input id="ntitle_<?= $oArticleMapping->GetId() ?>" type="text" name="ntitle_<?= $oArticleMapping->GetId() ?>" value="<?= $oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_NTITLE); ?>" maxlength="128" /></td>
				<td width="400px;" align="right">Search Keywords: <input id="sphrase_<?= $oArticleMapping->GetId() ?>" type="text" name="sphrase_<?= $oArticleMapping->GetId() ?>" value="<?= $oArticleMapping->GetOptionById(ARTICLE_DISPLAY_OPT_SEARCH_KEYWORD); ?>" maxlength="30" /></td>
				</tr>
			</table>
		</td>
		<td width="20px"><input type="submit" name="opt_<?= $oArticleMapping->GetId() ?>" onclick="javascript: return ArticleMapOptions(<?= $oArticleMapping->GetId(); ?>);" value="update" /></td>
	</tr>	
	
<? 
	}
} else {
	print "<tr><td style='color: red;' colspan=3>Article is not currently published.</tr>";
}
?>
</table>


<h2>Publish To :</h2>



<div class="row800">
	<span class="label_col"><label for="title" class="f_label" style="<?= strlen($response['msg']['title']) > 1 ? "color:red;" : ""; ?>">Website(s)<span class="red"> *</span></label></span>
	<span class="input_col"><?= $sWebSiteListHTML ?></span>
</div>

<div class="row800">
	<span class="label_col"><label for="title" class="f_label" style="<?= strlen($response['msg']['title']) > 1 ? "color:red;" : ""; ?>">Section Uri<span class="red"> *</span></label></span>
	<span class="input_col"><input type="text" id="section_uri" class="text_input" style="width: 300px;" name="section_uri" value="" /><span class="p_small">eg /volunteer/animals or /country/australia/work</span></span>
</div>

<!-- 
<div class="row800">
	<span class="label_col"><label for="title" class="f_label" style="<?= strlen($response['msg']['options']) > 1 ? "color:red;" : ""; ?>">Display Options</label></span>
	<span class="input_col">
		Placement Results <input type="checkbox" name="opt_placement" checked />&nbsp;&nbsp;
		Organisation Results <input type="checkbox" name="opt_org" checked />&nbsp;&nbsp;
		News Articles <input type="checkbox" name="opt_news" checked />
	
	</span>
</div>
 -->


<!-- 
<div class="row800">
	<span class="label_col"><label for="title" class="f_label" style="<?= strlen($response['msg']['title']) > 1 ? "color:red;" : ""; ?>">Element Id</label></span>
	<span class="input_col"><input type="text" id="section_uri" class="text_input" style="width: 300px;" name="section_uri" value="" /><span class="p_small"> <br />(optional) identifier when multiple articles on one page eg. INTRO_PARAGRAPH, INFO_TEXT </span></span>
</div>
 -->

<div class="row800">
	<span class="label_col"><label class="f_label">&nbsp;</label></span>
	<span class="input_col">
		<input type="submit" title="publish article" name="publish" value="Publish" class="sub_col_but" />
	</span>
</div>

<div class="row800">
	<span class="label_col"><label class="f_label">&nbsp;</label></span>
	<span class="input_col"><a href='./article_mgr.php' title='Back to Article Manager'>Back to Article Manager >></a></span>
</div>

</form>
	
</div>


</form>

</div>
</div>



<?

include("./footer.php");

?>
	
