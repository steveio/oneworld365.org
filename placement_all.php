<?


$oProfile = new CompanyProfile();

/* check that we have been correctly invoked */
$view_mode = strtoupper($_REQUEST['m']);

if (!in_array($view_mode, array("VIEW"))) {
	Error::StopRedirect($sUrl = $_CONFIG['url'],$sMsg = "ERROR : Bad request.");
}


/* get company id (if supplied) */
$id = $_REQUEST['id'];


if (!is_numeric($id)) {
	Error::StopRedirect($sUrl = $_CONFIG['url'],$sMsg = "ERROR : Bad request.");
}


/*
 * Get the COMPANY profile details
 *  
 */
$oProfile->SetFromArray($oProfile->GetProfileById($id));
if ($oProfile->GetId() != $id) Error::StopRedirect($sUrl = $_CONFIG['url'],$sMsg = "ERROR : Unable to retrieve company profile.");
$oProfile->GetImages();
$oProfile->GetCategoryInfo();
$oProfile->GetCountryInfo();
$oProfile->GetActivityInfo();
$oProfile->SetProfileCount();


if ($oProfile->GetListingType() < BASIC_LISTING) { /* only paid listings have placements */
	AppError::StopRedirect($sUrl = $_CONFIG['url'],$sMsg = "ERROR : Bad request.");
}

/*
 * Get the placements...
 * 
 */

$oPlacement = new Placement($db);
$aPlacement = $oPlacement->GetPlacementById($oProfile->GetId(),$key = "company_id",$ret_type = "rows");

$_REQUEST['page_title'] = "Gap year, Year out and Career Break placements from ".$oProfile->GetTitle();
$_REQUEST['page_meta_description'] = "Find a placement for your Gap Year, Career Break or Year out with ".$oProfile->GetTitle()."."; 
$_REQUEST['page_keywords'] = "Gap Year, Year Out, Career Break, ".$oProfile->GetTitle().", Gap Year 365";
?>


<? require_once("./header_html.php"); ?>



<div class="row">
	<div class="span12">
	<? 
	if (is_object($oProfile->GetImage(1,LOGO_IMAGE))) { ?>
		<div style=""><?= $oProfile->GetImage(1,LOGO_IMAGE)->GetHtml("",$oProfile->GetTitle()); ?></div>
	<? } elseif (is_object($oProfile->GetImage(0,LOGO_IMAGE))) { ?>
		<div style=""><?= $oProfile->GetImage(0,LOGO_IMAGE)->GetHtml("",$oProfile->GetTitle()); ?></div>
	<? }?>
	<h1 class='title1'><?= $oProfile->GetTitle(); ?></h1>
	<p class="lead"><?= $oProfile->GetDescShort(); ?></p>
	<h4>All Programs with <?= $oProfile->GetTitle() ?></h4>
	</div>
</div>

<div class="row profile">
	
	<div class="span10">
	<?
	if ((is_array($aPlacement)) && (count($aPlacement) >= 1)) {
		foreach ($aPlacement as $p) {
	
				$oProfile = new PlacementProfile();
				$oProfile->SetFromArray($p);
				$oProfile->GetCountryInfo();
				$aImg = $oProfile->GetImages();
	
				
				print  "<div class='profile-list-item' style='height: 230px;'>";
	
				if ((is_array($aImg)) && (count($aImg) >= 1)) {
					print "<span class='pull-right'><a title='' href=\"".$_CONFIG['url']."/company/".$p['comp_url_name']."/".$p['url_name']."\">".$aImg[0]->GetHtml("_mf",$p['title'])."</a></span>";
				}
					
				if ($oProfile->GetLogoUrl() != "") {
					//print "<span style='display: bloc; float: left; width: 100%; margin-bottom: 10px;'><a href='".$oProfile->GetProfileUrl()."/' title='".$oProfile->GetTitle()."'><img src='".$oProfile->GetLogoUrl()."' border='0' alt='".$oProfile->GetCompanyName()."' /></a></span>";
				}
				
				print "<h3><a href='".$oProfile->GetProfileUrl()."' title='".$oProfile->GetTitle()."'>".$oProfile->GetTitle()."</a></h3>";
	
				if (strlen(trim($oProfile->GetCountryTxt())) < 26) {
					print "<p>".$oProfile->GetCountryTxt()."</p>";
				}
				
				print "<p>".$oProfile->GetDescShort() ."</p>";
				print "<a class='btn btn-primary' class='p_small' href='".$oProfile->GetProfileUrl()."' title='".$oProfile->GetTitle()."'>View Program</a>";
				
				print "<hr /></div>\n";
				
			
		}
		
		print "<div id=\"main_pager\">";
		print $sPager;
		print "</div>";
		
	} else {
		 print "<div style='float: left; width: 100%'><h1>There a 0 matching placements.</h1></div>";
	}
	?>
	</div>
</div>



<?php include("./footer.php"); ?>
