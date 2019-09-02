<?php

$bNoHTMLHeader = true;

include("./header.php");



if (!$oAuth->oUser->isAdmin) AppError::StopRedirect($sUrl = $_CONFIG['url']."/client_login.php",$sMsg = "ERROR : You must be authenticated.  Please login to continue.");


$oJsInclude = new JsInclude();
$oJsInclude->SetSrc("/lib/ckeditor/ckeditor.js");
$oHeader->SetJsInclude($oJsInclude);

$ckeditor_js = <<<EOT

CKEDITOR.replace( 'desc_short',
    {
    filebrowserImageUploadUrl : '/image_upload.php',
    	toolbar : 'MyToolbar',
    	height:"291", 
    	width:"800"
    });
CKEDITOR.replace( 'desc_long',
    {
    filebrowserImageUploadUrl : '/image_upload.php',
		
		toolbar : 'MyToolbar',
    	height:"391", 
    	width:"800"
        
    });
    
EOT;
$oHeader->SetJsOnload($ckeditor_js);
$oHeader->Reload();




$mode = (is_numeric($_REQUEST['id'])) ? "EDIT" : "ADD"; 

$sTitle = ($mode == "EDIT") ? "Edit Article" : "Create New Article";

$oWebsite = new Website($db);
$sWebSiteListHTML = $oWebsite->GetSiteSelectList(array(),$checked = TRUE, $markup = "DIV");


$aResponse = array();
$oArticle = new Article();

$article_id = $_REQUEST['id'];


/*
 * EVENT HANDLERS
 * 
 */


if (isset($_REQUEST['attach_profile'])) {
	$oArticle->SetId($article_id);
	if (!$oArticle->AttachProfile($_REQUEST,$aResponse)) {
		$aResponse['placement_id'] = "ERROR : Please select a profile to attach";
	}
}


if (isset($_REQUEST['remove_profile'])) {
	$oArticle->SetId($article_id);
	if (is_numeric($_REQUEST['profile_id'])) {
		 $oArticle->RemoveProfile($_REQUEST,$aResponse);
	} else {
		$aResponse['placement_id'] = "ERROR : Please select a placement to remove";
	}	
}


if (isset($_REQUEST['attach_article'])) {
	$oArticle->SetId($article_id);
	$aId = Mapping::GetIdByKey($_REQUEST,"art_");
	if ($oArticle->AttachArticleId($aId)) {
		$aResponse['msg'] = "SUCCESS : Attached selected articles;";
	}
}


if (isset($_REQUEST['remove_article'])) {
	$oArticle->SetId($article_id);
	$aId = Mapping::GetIdByKey($_REQUEST,"art_");
	if ($oArticle->RemoveAttachedArticle($aId)) {
		$aResponse['msg'] = "SUCCESS : Removed selected articles;";
	}
}




$max_size = IMAGE_MAX_UPLOAD_SIZE;
$max_uploads  = 4;
$path = ROOT_PATH.'/upload/images/';

if (isset($_REQUEST['do_file_upload'])) {

	if (DEBUG) Logger::Msg("Upload: Begin...");
	
	if (count($_FILES['file']['name'])<=$max_uploads) {
		if (DEBUG) Logger::Msg("Upload: Multiple...");
		$upload = new File_upload();
		$upload->allow('images');
		$upload->set_path($path);
		$upload->set_max_size($max_size);		

		$aResult = $upload->upload_multiple($_FILES['file']);
		
		$error = false;
		if ($upload->is_error()) {
			$error = true; 
			$errstr= $upload->get_error();
		}

		if (is_array($aResult['TMP_PATH']) && count($aResult['TMP_PATH']) >= 1) {
			/* Now call ImageProcessor to generate proxy images */
			$oIP = new ImageProcessor_FileUpload;
			$result = $oIP->Process($aResult['TMP_PATH'],"ARTICLE",$article_id,$iImgType = PROFILE_IMAGE);
			if (!$result) {
				$error = TRUE; 
				$errstr= 'An error occured during image thumbnail processing';
			}
			
		}
		
	} else {
		$error = TRUE; 
		$errstr= 'Trying to upload to many files';
	}   

		
	if ($error) {
		$aResponse['msg'] = "ERROR : ".$errstr;
	} else {
		$plural = (count($aResult['FILENAME']) > 1) ? "s" : "";
		$aResponse['msg'] = "SUCCESS : uploaded ".count($aResult['FILENAME']) ." file".$plural."<br/>".implode("<br />",$aResult['FILENAME']); 
	}
	
}




if (isset($_REQUEST['save'])) {

	$oArticle->SetFromArray(array(
								"id" => $_POST['id']
								,"title" => $_POST['title']
								,"short_desc" => $_POST['desc_short']
								,"full_desc" => $_POST['desc_long']
								,"created_by" => $oAuth->oUser->id
								,"published_status" => 0 /* DRAFT */
								),"SET", $escape_chars = FALSE /* Save()->Santitize() does escaping */
							);
							
	if ($oArticle->Save($aResponse)) {
		$aResponse['msg'] = "SUCCESS : Article saved OK";
		$_REQUEST['id'] = $oArticle->GetId(); 
	}
	
	
	

}

if(($mode == "EDIT") || ($mode == "ADD")) {

	$oArticle->SetFetchMode(FETCHMODE__FULL);
	$oArticle->SetFetchAttachedTo(TRUE);
	$oArticle->SetFetchProfiles(TRUE);
	$oArticle->SetFetchAttachedTo(TRUE);	

	/* get article from DB */
	if ($mode == "EDIT") {
		if (!$oArticle->GetById($_REQUEST['id'])) {
			$aResponse['get'] = "ERROR : Unable to retrieve article";
		}
	}
}

$oCompany = new Company($db);
$sCompDDList = $oCompany->getCompanyNameDropDown($_REQUEST['company_id'],null,'company_id',true,1,$sOnChange = "javascript: doPlacementListRequest('".$_CONFIG['url']."/placement_list_ajax.php','edit_article','company_id');");
$sPlacementDDList = "<select id='placement_id' disabled><option value='NULL'>select</option></select>";



//Logger::Msg($oArticle);


include("./header_html.php");

?>



<div id="msgtext" style="color: red; font-size: 10px;">
<? 
$aResponse = (isset($aResponse['msg'])) ? $aResponse['msg'] : $aResponse; 
print AppError::GetErrorHtml($aResponse);  
?>
</div>

<div class="page_content content-wrap clear">
<div class="row pad-tbl clear">


<div id="profile">	
<div id="profile_inner" style="width: 800px">


<div id='row800'>
	
<h1><?= $sTitle ?></h1>


<form enctype="multipart/form-data" name="edit_article" id="edit_article" action="#" method="POST">

<input type="hidden" name="id" value="<?= $oArticle->GetId(); ?>" />


<div class="row800">
	<span class="label_col"><label for="title" class="f_label" style="<?= strlen($response['msg']['title']) > 1 ? "color:red;" : ""; ?>">Title<span class="red"> *</span></label></span>
	<span class="input_col"><input type="text" id="title" class="text_input" style="width: 300px;" name="title" value="<?= $oArticle->GetTitle(); ?>" /></span>
</div>
 
<div class="row800">
	<span class="label_col"><label for="desc_short" class="f_label" style="<?= strlen($response['msg']['desc_short']) > 1 ? "color:red;" : ""; ?>">Short Desc<span class="red"> *</span></label></span>
	<span class="input_col"><textarea id="desc_short" name="desc_short" /><?= $oArticle->GetDescShort(); ?></textarea></span>
</div>

<div class="row800">
	<span class="label_col"><label class="f_label">Full Description</label></span>

	<? $desc_full = $oArticle->GetDescFull(); ?>
	
	<span class="input_col"><textarea id="desc_long" name="desc_long" /><?= $desc_full; ?></textarea></span>
</div>




<div class="row800">
	<span class="label_col"><label class="f_label">&nbsp;</label></span>
	<span class="input_col">
		<input type="submit" title="save article" name="save" value="Save" class="sub_col_but" />
		<? if (is_numeric($oArticle->GetId())) { ?>
		<input type="submit" title="publish article" onclick="javascript: go('./article_pub.php?&id=<?= $oArticle->GetId() ?>'); return false;" name="new" value="Publish" class="sub_col_but" />
		<? } ?>
	</span>
</div>

<div class="row800">
	<span class="label_col"><label class="f_label">&nbsp;</label></span>
	<span class="input_col"><a href='./article_mgr.php' title='Back to Article Manager'>Back to Article Manager >></a></span>
</div>



<div class="row800" style="width: 800px;"><hr /></div>


<? if (is_numeric($oArticle->GetId())) { ?>


<div class="row800" style="width: 400px;">

	<div class="row800" style="width: 400px;">
	<h2>Attached Images :</h2>
	<div id="image_msg"></div>
	
	<div class="row800" style="width: 400px;">
	<?
	if (count($oArticle->aImage) >= 1) {
		foreach($oArticle->aImage as $oImage) {
			?>
			<div id="img_<?= $oImage->GetId() ?>" style="float: left; padding-right: 6px;">
			<a class="p_small" title="Remove Image" href="javascript: void(null);" onclick="javascript: RemoveImage('<?= $_CONFIG['url'] ?>','ARTICLE',<?= $oArticle->GetId() ?>,<?= $oImage->GetId() ?>)">[REMOVE]</a>
			<br />
			<?= $oImage->GetHtml("_s",$oArticle->GetTitle()) ?>
			</div>
			<?
		}
	}
	?>
	</div>
		
	<h2>Upload Images :</h2>

		<!-- MULTIPLE FILE UPLOAD -->
		
		<script type="text/javascript">
		<!--
			var gFiles = 0;
			function addFile() {
				var tr = document.createElement('tr');
				tr.setAttribute('id', 'file-' + gFiles);
				var td = document.createElement('td');
				td.innerHTML = '<input type="file" size="30" name="file[]"><span onclick="removeFile(\'file-' + gFiles + '\')" style="cursor:pointer;">Remove</span>';
				tr.appendChild(td);
				document.getElementById('files-root').appendChild(tr);
				gFiles++;
			}
			function removeFile(aId) {
				var obj = document.getElementById(aId);
				obj.parentNode.removeChild(obj);
			}
		-->
		</script>

		<span onclick="addFile()" style="cursor:pointer;cursor:hand;">Add More +</span>

		<input type="hidden" name="mode" value="misc" />
		<input type="hidden" name="action" value="upload" />
		<input type="hidden" name="upload" value="1" />
		
		<input type="hidden" name="MAX_FILE_SIZE" value="<?= IMAGE_MAX_UPLOAD_SIZE; ?>" />
		<table>
		<tbody id="files-root">
			<tr><td><input type="file" name="file[]" size="30"></td></tr>
		</table>
		<input type="submit" name="do_file_upload" value="Upload Image">

		<p style="font-size:8pt;">allowed extensions are: <strong>JPG, JPEG PNG, GIF</strong>; max size per file: <strong>5mb</strong>; max number of files per upload <strong><?php echo $max_uploads; ?></strong></p>
		<?php
		if ($is_upload && $error) {
			print '<strong>Error: '.$errstr.'</strong><br />';
		} else if ($is_upload) {
			foreach ($files as $file) {
				$image = _URL_.'uploads/'.$file;
				print '<input type="text" size="'.strlen($image).'" value="'.$image.'"><br />';
				print '<img src="'.$image.'">';
			}
		}
		?>
		
	</div>

</div>



<div class="row800" style="width: 380px; margin-left: 20px;">

	<div class="row800" style="width: 400px;">
	<h2>Attached Links :</h2>
	
	<div id="link_msg"></div>
	<div id="link_result">
	<?= $oArticle->oLinkGroup->Render(); ?>
	</div>
	
	
	<h2>Attach a Link :</h2>
	
	<div id="image_msg"></div>

		<table>
		<tbody id="links-root">
			<tr>
			<td>Url:</td>
			<td><input type="text" id="link_url" maxlength="255" name="link_url" size="30" value="<?= $_REQUEST['link_url'] ?>"></td>
			</tr>
			<td>Label:</td>
			<td><input type="text" id="link_title" maxlength="127" name="link_title" value="<?= $_REQUEST['link_title'] ?>" size="30"></td>
			</tr>
		</table>
		<input type="submit" onclick="javascript: AttachLink('<?= $_CONFIG['url']; ?>','ARTICLE',<?= $oArticle->GetId() ?>); return false;" name="do_add_link" value="Add Link">

</div>

</div>

	<div class="row800" style="width: 800px;"><hr /></div>


	<div class="row800" style="width: 800px;">
	<h2>Attached Profiles :</h2>
	
	<?
	
	foreach($oArticle->GetAttachedProfile() as $oProfile) {
				
		if ($oProfile->GetType() == PROFILE_COMPANY) {
			$oProfile->LoadTemplate("profile_summary_01_sm.php");
		} else { /* assume everything else is a placement profile varient */
			$oProfile->LoadTemplate("profile_summary_01_sm.php");	
		}
		
		?>
		<div class="border" style="float: left; width: 250px;">
		<div style="float: right; padding-right: 10px;"><a class="p_small" title="Remove Profile" href="<?= $_SERVER['PHP_SELF'] ?>?&id=<?= $oArticle->GetId() ?>&profile_id=<?= $oProfile->GetId() ?>&profile_type=<?= $oProfile->GetType(); ?>&remove_profile=1">[REMOVE]</a></div>
		<div style="height: 200px;">
		<?= $oProfile->Render(); ?>
		</div>
		
		</div>
		<?
	}
	
	?>
	
	</div>
	
	
	<div class="row800" style="width: 800px;">
	
	<h2>Attach Profiles :</h2>
	
	<div class="row800">
		<span class="label_col"><label class="f_label">Company</label></span>
		<span class="input_col"><?= $sCompDDList ?></span>
	</div>
	
	<div class="row800">
		<span class="label_col"><label class="f_label">Placement</label></span>
		<span class="input_col"><div id="placement_list"><?= $sPlacementDDList ?></div></span>
	</div>
	
	<div class="row800">
		<span class="label_col"><label class="f_label">&nbsp;</label></span>
		<span class="input_col">
			<input type="submit" onclick="javascript: return validateAttachProfile();" title="attach profile" name="attach_profile" value="Attach" class="sub_col_but" />
		</span>
	</div>
	
	</div>
	
	<div class="row800" style="width: 800px;"><hr /></div>
		

	<div class="row800" style="width: 800px;">
	<h2>Attached Articles :</h2>
	
	<?
	
	if ($oArticle->oArticleCollection->Count() >= 1) {

		$oArticle->oArticleCollection->LoadTemplate("article_search_result_list_02.php");
		
		print $oArticle->oArticleCollection->Render();
		
	}
	
	?>
	
	</div>

	
	<div class="row800">
	
	<h2>Attach Articles :</h2>
	
	<p class="p_small">
		<ul>
			<li>Patterns: <span class="p_small">"%" = all  OR  "%africa" = contains "africa" OR  "/volunteer/animals"</i></span></li>
		</ul>
	</p>
	
	<table cellspacing="2" cellpadding="4" border="0">	
	<tr class="hi">
		<td align="right" width="800px">
		From:
		<input type="text" id="search_phrase" style="width: 150px;" value="<?= $_REQUEST['filter_uri'] ?>" />
		<input type="submit" onclick="javascript: ArticleSearch('<?= $_CONFIG['url'] ?>','search',<?= $oArticle->GetId()  ?>,'article_search_result_list_01.php'); return false;" name="article_search" value="Search" />
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

	<div class="row800">
	
	<h2>Attached To :</h2>
	
	<p class="p_small">Shows other articles this article is attached to and where these are published</p>
	
	<div id="article_deattach_msg" style="color: red; font-size: 12px;"></div>
	
	<table cellspacing="2" cellpadding="4" border="0" width="800px">
		<tr>
			<th>Id</th>
			<th>Article Title</th>
			<th>Publised to Urls</th>
			<th>De-attach</th>
		</tr>
		<?php
		$prev_id = null; 
		foreach ($oArticle->GetAttachedTo() as $row) {
			$url = "<a href='/article_edit.php?id=".$row['id']."' target='_new' title='View / Edit this Article'>".$row['title']."</a>";
			$deattach_link = "<input type=\"submit\" onclick=\"javascript: ArticleDeattach('". $_CONFIG['url'] ."',".$row['id'].",". $oArticle->GetId() ."); return false;\" name=\"deattach\" value=\"Deattach\" />";  
		?>
		<tr id="deattach_row<?= $row['id']; ?>">
			<td valign="top"><?= $row['id']; ?></td>
			<td valign="top"><?= $url; ?></td>
			<td><?= $row['published_url']; ?></td>
			<td><?= $deattach_link; ?></td>
		</tr>
		<?php
			$prev_id = $row['id']; 
		} 
		?>
	</table>	
	
	</div>

<? } // end is published check ?>



</div><!--  end row800 -->

</div><!--  end profile_inner -->
</div><!--  end profile -->


</form>


</div>	
</div>

<?
include("./footer.php");
?>
	
