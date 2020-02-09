<?

/*
*
*  ADD / EDIT / VIEW PLACEMENT
* 
*/



$view_mode = strtoupper($_REQUEST['m']);

if (!in_array($view_mode, array("VIEW","EDIT","ADD","DELETE"))) {
	Logger::DB(1,__FILE__."::".__LINE__." invalid mode: ".$_SERVER['REQUEST_URI']." ".$_SERVER['QUERY_STRING'] ." " . $_REQUEST['m']);
	AppError::StopRedirect($sUrl = $_CONFIG['url']."/",$sMsg = "ERROR : Unable to retrieve placement.");
}


/* decode message if supplied by referer */
if (strlen($_GET['m']) > 1) {
	$response['msg'] = base64_decode($_GET['m']);
}


/* if we VIEW / EDIT an existing placement get the ID */
$id = $_REQUEST['id'];

$t = new Template();
$oProfile = new PlacementProfile(); /* base placement profile class */


if (($view_mode == "VIEW") && (!is_numeric($id))) {
	Logger::DB(1,__FILE__."::".__LINE__."  missing placement id : ". $id.", ".$_SERVER['REQUEST_URI']." ".$_SERVER['QUERY_STRING']);
	AppError::StopRedirect($sUrl = $_CONFIG['url']."/",$sMsg = "");
}

if (is_numeric($id)) {

	$iType = PlacementProfile::GetTypeById($id);
	
	if (!is_numeric($iType)) {
		Logger::DB(1,__FILE__."::".__LINE__."  invalid type: ".$_SERVER['REQUEST_URI']." ".$_SERVER['QUERY_STRING'] ." placement: " . $id.", type: ".$iType);
		AppError::StopRedirect($sUrl = $_CONFIG['url']."/",$sMsg = "");
	}
	
	$oProfile = ProfileFactory::Get($iType);

	if (!$oProfile->GetById($id)) {
		Logger::DB(1,__FILE__."::".__LINE__."  GetById() failed id : ".id.", ",$_SERVER['REQUEST_URI']." ".$_SERVER['QUERY_STRING']);		
		AppError::StopRedirect($sUrl = $_CONFIG['url']."/",$sMsg = "");
	}
	

	/* put the profile into the template scope */
	$t->SetFromObject($oProfile);

	$t->Set("desc_short", $oProfile->StripLinks(html_entity_decode($oProfile->GetDescShort())));
	$t->Set("desc_long", $oProfile->StripLinks(html_entity_decode($oProfile->GetDescLong())));
			
	
	$oProfile->SetCompanyLogo();
	if (is_object($oProfile->GetCompanyLogo())) {
		$t->Set('company_logo',$oProfile->GetCompanyLogo()->GetHtml(""));
	}
	$t->Set('url',$oProfile->GetUrl());
	$t->Set('company_url',$oProfile->GetCompanyProfileUrl());
	
	for($i = 0; $i < 4; $i++) {
		if (is_object($oProfile->GetImage($i))) {

		    if (($oProfile->GetImage($i)->GetWidth() < ImageSize::Get($size = '_m',$oProfile->GetImage($i)->GetAspect(),$type = "WIDTH")) && 
				($oProfile->GetImage($i)->GetWidth() < ImageSize::Get($size = '_m',$oProfile->GetImage($i)->GetAspect(),$type = "HEIGHT"))) {
				$t->Set('img'.($i + 1).'_m',$oProfile->GetImage($i)->GetHtml("",$oProfile->GetTitle()));
			} else {
				$t->Set('img'.($i + 1).'_m',$oProfile->GetImage($i)->GetHtml("_m",$oProfile->GetTitle()));
			}
			
			$t->Set('img'.($i + 1).'_s',$oProfile->GetImage($i)->GetHtml("_s",$oProfile->GetTitle()));
			$t->Set('img'.($i + 1).'_o',$oProfile->GetImage($i)->GetHtml("",$oProfile->GetTitle()));
		}
	} // end image init
} // end id check (EDIT)

/* ADD / EDIT -> get profile type and enquiry options */ 	
$oCProfile = new CompanyProfile();
if ($oAuth->oUser->isAdmin) {
	/* we are viewing the profile */
	$oCProfile->SetEnquiryOptionBitmap($oProfile->GetCompEnqOpt());

} elseif ($oAuth->oUser->isValidUser) { 
	/* get comp users prof/enq options from session (setup on user object) 
	 * not sure why we don't look at company profile here eg $oProfile->GetCompProfOpt() 
	*/
	$oCProfile->SetProfileOptionBitmap($oAuth->oUser->prof_opt);
	$oCProfile->SetEnquiryOptionBitmap($oAuth->oUser->enq_opt);		
} else {
	/* setup enquiry types based on defaults for the placement's company */
	$oCProfile->SetEnquiryOptionBitmap($oProfile->GetCompEnqOpt());
}


$t->Set("comp_profile",$oCProfile);	


// refdata for duration to/from labels
$oDuration = new Refdata(REFDATA_DURATION);
$oDuration->SetOrderBySql(' id ASC');
$oDuration->GetByType();
$oProfile->SetDurationRefdataObject($oDuration);

if (($oProfile instanceof TourProfile) || ($oProfile instanceof GeneralProfile)) {

	// refdata for price to/from labels
	$oPrice = new Refdata(REFDATA_APPROX_COST);
	$oPrice->SetOrderBySql(' sort_order ASC');
	$oPrice->GetByType();
	$oProfile->SetCostsRefdataObject($oPrice);

	// refdata for currency
	$oCurrency = new Refdata(REFDATA_CURRENCY);
	$oCurrency->GetByType();
	$oProfile->SetCurrencyRefdataObject($oCurrency);

}

$oOptionGroup = new OptionGroup();
$oOptionGroup->GetAll();

$aSelected = array();
$aSelected = $oOptionGroup->GetByPlacementId($oProfile->GetId(),"opt_");


if ($oProfile instanceof TourProfile) {

	/* get tour refadata options - travel / tour, accomodation, meals */
	$oTravelOptions = new Refdata(REFDATA_TRAVEL_TRANSPORT);
	$aSelected = array();
	$aSelected = $oProfile->GetTransportIdList();
	$oTravelOptions->SetOption(REFDATA_OPTION_CHECKBOXES_DISABLED, TRUE);
	$t->Set('REFDATA_TRAVEL_ARRAY',$oTravelOptions->GetLabelsFromSelectedIds($aSelected));

	// accomodation
	$oAccom = new Refdata(REFDATA_ACCOMODATION);
	$aSelected = array();
	$aSelected = $oProfile->GetAccomodationIdList();
	$oAccom->SetOption(REFDATA_OPTION_CHECKBOXES_DISABLED, TRUE);
	$t->Set('REFDATA_ACCOM_ARRAY',$oAccom->GetLabelsFromSelectedIds($aSelected));

	// meals
	$oMeals = new Refdata(REFDATA_MEALS);
	$aSelected = array();
	$aSelected = $oProfile->GetMealsIdList();
	$oMeals->SetOption(REFDATA_OPTION_CHECKBOXES_DISABLED, TRUE);
	$t->Set('REFDATA_MEALS_ARRAY',$oMeals->GetLabelsFromSelectedIds($aSelected));

}




// no id : we are adding a new placement
if ((($view_mode == "ADD") || ($view_mode == "EDIT")) && (!in_array($oAuth->oUser->access_level,array(1,3)))) die("ERROR: You are not permitted to do that.");


$oCompany = new Company($db);
if ($oAuth->oUser->isAdmin) {
	$t->Set("COMPANY_NAME_LIST",$oCompany->getCompanyNameDropDown($t->Get('company_id'),null,'company_id'));
} else {
	$t->Set("COMPANY_NAME_LIST",$oCompany->getCompanyNameDropDown($oAuth->oUser->company_id,$oAuth->oUser->company_id,'company_id'));
}

/* extract selected cat/act/cty mappings from $_REQUEST */
$aResult = Mapping::GetFromRequest($_REQUEST);
	
$oActivity = new Activity($db);
$aSelected = (is_array($t->Get('activity_array'))) ? $oProfile->activity_array : $aResult['act'];
$t->Set("ACTIVITY_LIST",$oActivity->GetActivityLinkList($mode = "input",$aSelected));

$oCategory = new Category($db);
$aSelected = (is_array($oProfile->category_array)) ? $oProfile->category_array : $aResult['cat'];
$t->Set("CATEGORY_LIST",$oCategory->GetCategoryLinkList($mode = "input",$aSelected));

$oCountry = new Country($db);
$aSelected = (is_array($oProfile->country_array)) ? $oProfile->country_array : $aResult['cty'];
$t->Set("COUNTRY_LIST",$oCountry->GetCountryLinkList($mode = "input",$aSelected));



/* process any message / error for display */
if (is_array($response['msg'])) { 
	$t->Set("msg",AppError::GetErrorHtml($response['msg']));
} else {
	$t->Set("msg",$response['msg']);
} 


/* Work out the correct profile type : Volunteer, Tour, Job */
if (isset($_POST['submit'])) {
	$sView = $_REQUEST['profile_type'];
} else {
	if (is_numeric($t->Get('profile_type'))) {
		$sView = $t->Get('profile_type'); 	
	} elseif ($oCProfile->HasProfileOption(PROFILE_VOLUNTEER)) {
		$sView = PROFILE_VOLUNTEER;
	} elseif ($oCProfile->HasProfileOption(PROFILE_TOUR)) {
		$sView = PROFILE_TOUR;
	} elseif ($oCProfile->HasProfileOption(PROFILE_JOB)) {
		$sView = PROFILE_JOB;
	}
}


/*
 * Determine which enquiry types are permitted for this profile
*
*/

$aEnquiryUrl['BOOKING'] = Enquiry::GetRequestUrl('BOOKING',$t->Get('id'),PROFILE_PLACEMENT);
$aEnquiryUrl['GENERAL'] = Enquiry::GetRequestUrl('GENERAL',$t->Get('id'),PROFILE_PLACEMENT);
$aEnquiryUrl['BROCHURE'] = Enquiry::GetRequestUrl('BROCHURE',$t->Get('id'),PROFILE_PLACEMENT);
$aEnquiryUrl['JOB_APP'] = Enquiry::GetRequestUrl('JOB_APP',$t->Get('id'),PROFILE_PLACEMENT);



/* get other placements associated with this company */
$oPlacement = new PlacementProfile();
$aPlacement = $oPlacement->GetProfileById($t->Get('company_id'), $key = "COMPANY_ID", $return = "PROFILE");


/* Get MORE like this (from other companies) */
require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrSearch.php");
require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrMoreLikeSearch.php");

global $solr_config;

// get some related placements
$oSolrMoreLikeSearch = new SolrMoreLikeSearch($solr_config);

$aFilterQuery = array();
if (is_array($oProfile->country_array) && count($oProfile->country_array) == 1)
    $aFilterQuery['country_id'] = $oProfile->country_array[0];

$oSolrMoreLikeSearch->getPlacementsByPlacement($oProfile->GetOid(),$oProfile->GetCompanyId(),1, $aFilterQuery);
$oSolrMoreLikeSearch->setRows(10);
$aTmp = $oSolrMoreLikeSearch->getId();

$aRelatedProfile = array();
if (is_array($aTmp) && count($aTmp) >= 1) {
	$aRelatedId = array();
	foreach($aTmp as $idx => $a) {
		$aRelatedId[] = $a['profile_id'];
	}
	$aRelatedProfile = PlacementProfile::Get("ID_LIST_SEARCH_RESULT",$aRelatedId);
	$aRelatedProfile = is_array($aRelatedProfile) && count($aRelatedProfile) > 6 ? array_slice($aRelatedProfile, 0, 6) : $aRelatedProfile;
}


$oSolrMoreLikeSearch = new SolrMoreLikeSearch($solr_config);
$aFilterQuery = array();
$aFilterQuery['-title'] = "365";
$aFilterQuery['-desc_short'] = "365";
$oSolrMoreLikeSearch->setRows(10);
$aRelatedArticle = $oSolrMoreLikeSearch->getRelatedArticle($oProfile->GetOid(),$aFilterQuery);
$oRelatedArticle = new Article();
$oRelatedArticle->GetArticleCollection()->AddFromArray($aRelatedArticle);


// reviews
require_once($_CONFIG['root_path']."/classes/review.class.php");
$oReviews = new Review();
$aReview = $oReviews->Get($oProfile->GetId(),'PLACEMENT',1);
$bHasReviewRating = false;
$iReviewRating = 0;
if (is_array($aReview) && count($aReview) >= 1)
{
    $bHasReviewRating = true;
    foreach($aReview as $oReview)
    {
        $iReviewRating += $oReview->GetRating();
    }
    $iReviewRating = floor($iReviewRating / count($aReview));
}
$oReviewTemplate = new Template();
$oReviewTemplate->Set('LINK_TO', 'PLACEMENT');
$oReviewTemplate->Set('LINK_ID', $oProfile->GetId());
$oReviewTemplate->Set('LINK_NAME', " with ".$oProfile->GetCompanyName());
$oReviewTemplate->Set('REVIEWS',$aReview);
$oReviewTemplate->Set('REVIEWRATING',$iReviewRating);
$oReviewTemplate->Set('HASREVIEWRATING',$bHasReviewRating);
//$bHasReview = (is_array($aReview) && count($aReview) >= 1) ? true : false;
$oReviewTemplate->Set('HAS_REVIEW',true);


$bNoPrototype = TRUE;
require_once("./header_html.php");

?>
<!-- start: View Placement -->
<section id="page-profile" class="">
<div class="row-fluid profile">

	<div class="row-fluid">	
	<div class="span12">

        <div class="span12" style="margin: 20px;">
    	<div class="pull-right sharethis-inline-share-buttons"></div>
        </div>  

		<? if (strlen($t->Get('company_logo')) >1) { ?>
			<div style=""><?= $t->Get('company_logo') ?></div>
		<? } ?>
	
		<h1><?= $t->Get('title'); ?></h1>

		<div id="review-overallrating" style="margin-bottom: 10px;"></div>
	

		<p>
		<? if (in_array($t->Get('profile_type'),array(PROFILE_VOLUNTEER, PROFILE_TOUR))) { ?>
			<b>Company :</b> <a href="<?= $t->Get('company_url'); ?>" title="Find out more about <?= $t->Get('company_name'); ?>" style="color: #DD6900;"><?= $t->Get('company_name'); ?></a><br/>
			<?php 
			if (count($oProfile->GetActivityArray()) > 1 && count($oProfile->GetActivityArray()) < 3) {
				$label = "Activities: "; ?>
				<b><?= $label; ?></b> <?= $t->Get('activity_txt'); ?><br/><?
			}
			?>
			<?php 
			if (count($oProfile->GetCountryArray()) > 1 && count($oProfile->GetCountryArray()) < 3) {
				$label = "Countries: "; ?>
				<b><?= $label; ?></b> <?= $t->Get('country_txt') ?><br/><?
			} 
			?>
			<? if (strlen($t->Get('location')) > 1) { ?>
				<b>Location :</b> <?= $t->Get('location'); ?><br/>
			<? } ?>
			<? if (is_numeric($oProfile->GetDurationFromId())) { ?>
				<b>Duration:</b> <?= $oProfile->GetDurationFromLabel(); ?> to <?= $oProfile->GetDurationToLabel(); ?><br />
			<? } ?>
			<?php if (is_numeric($oProfile->GetPriceFromId())) { ?>
				<b>Approx Costs:</b> <?= $oProfile->GetPriceFromLabel(); ?> to <?= $oProfile->GetPriceToLabel(); ?>
				<?= $oProfile->GetCurrencyLabel(); ?><br />
			<?php } ?>
	
			<? if (strlen($t->Get('code')) > 1) { ?>
				<b>Tour Code :</b> <?= $t->Get('code'); ?>
			<? } ?>
		<? } ?>

		<? if ($t->Get('profile_type') == PROFILE_JOB) { /* JOB */ ?>
			<b>Job Ref :</b> <?= $t->Get('reference'); ?><br/>
			<b>Company :</b> <?= $t->Get('company_name'); ?><br/>
			<b>Country :</b> <?= $t->Get('country_txt'); ?><br/>
			<? if (strlen($t->Get('location')) > 1) { ?>
				<b>Location :</b> <?= $t->Get('location'); ?><br/>
			<? } ?>
			<? if (strlen($t->Get('contract_type_label')) > 1) { ?>
				<b>Contract :</b> <?= $t->Get('contract_type_label'); ?><br/>
			<? } ?>
			<? if (strlen($t->Get('start_dt_exact')) > 1) { ?>
				<b>Start Date :</b> <?= $t->Get('start_dt_exact'); ?><br/>
			<? } elseif(strlen($t->Get('start_dt_multiple')) > 1) { ?>
				<b>Start Dates :</b> <?= $t->Get('start_dt_multiple'); ?><br/>
			<? } ?>
			<? if (strlen($t->Get('closing_dt')) > 1) { ?>
				<b>Apply By :</b> <?= $t->Get('closing_dt'); ?><br/>
			<? } ?>
		<? } ?>
		
		</p>
		
		<? if ($t->Get('profile_type') == PROFILE_JOB) { /* JOB */ ?>
		<h3>Job Description</h3>
		<? } ?>

		<div class='lead' style='padding-bottom: 20px;'>
		
		<p class="lead"><strong><?= $t->Get('desc_short'); ?></strong></p>			


        <div class="profile-image span4 pull-right">
                <?php  
                if (is_array($oProfile->GetAllImages()) && count($oProfile->GetAllImages()) >= 1) 
                {
                    $arrImage = $oProfile->GetAllImages();
                    $iDisplay = 3; ?>
                    <div class="">
                    <ul class="unstyled"><?php
                    for($i=0; $i<$iDisplay; $i++) 
                    {
                        $oImage = isset($arrImage[$i]) ? $arrImage[$i] : null;
                        if (is_null($oImage)) continue;
                        if (strlen($oImage->GetHtml("_lf","")) > 1) { ?>
                            <li style="margin-bottom: 10px;"><?= $oImage->GetHtml("_lf",""); ?></li><?php
                        } else { ?>
                            <li style="margin-bottom: 10px;"><?= $oImage->GetHtml("_mf",""); ?></li><?php
                        }
                    } ?>
                    </ul>
                    </div><?php
                    if (count($arrImage) >= $iDisplay) { ?>
					<div id="image-viewall-lnk" class="pull-right"><h5><a href="#" id="image-viewall">View All Images >></a></h5></div><?php 
                    }

                    if (count($arrImage) >= $iDisplay)
                    { ?>
                        <div id="image-all" class="hide">
                        <ul class="unstyled"><?php
                        for($i=$iDisplay; $i<count($arrImage); $i++) 
                        {
                            $oImage = isset($arrImage[$i]) ? $arrImage[$i] : null;
                            if (is_null($oImage)) continue;
                            if (strlen($oImage->GetHtml("_lf","")) > 1) { ?>
                                <li style="margin-bottom: 10px;"><?= $oImage->GetHtml("_lf",""); ?></li><?php
                            } else { ?>
                                <li style="margin-bottom: 10px;"><?= $oImage->GetHtml("_mf",""); ?></li><?php
                            }
                        } ?>
                        </ul>
                        </div><?php 
                    }
                } ?>
                <script>
            	$(document).ready(function(){ 
        			$('#image-viewall').click(function(e) {
     				   e.preventDefault();
     			       $('#image-all').show();
     			       $('#image-viewall-lnk').hide();
     			       return false;
    	 			});
            	}); 
                </script>
        </div>

		<p><?= $t->Get('desc_long'); ?></p>

		<? if (strlen(trim($t->Get('video1'))) > 1) { ?>
			<div class='span12'>
			<h3>Video</h3>
				<?= $t->Get('video1') ?>
			</div>
		<? } ?>		
		
		<? if ($t->Get('profile_type') == PROFILE_VOLUNTEER) { /* VOLUNTEER */ ?>
						
			<? if (strlen($t->Get('duration_txt')) >= 1) { ?>
				<h3>Duration</h3>
				<p><?= $t->Get('duration_txt') ?></p>
			<? } ?>
				
			<? if (strlen($t->Get('start_dates')) >= 1) { ?>
				<h3>Start Dates</h3>
				<p><?= nl2br($t->Get('start_dates')) ?></p>
			<? } ?>
			
	
			<? if (strlen($t->Get('benefits')) >= 1) { ?>
				<h3>Costs / Benefits</h3>
				<p><?= nl2br($t->Get('benefits')) ?></p>
			<? } ?>
	
			<? if (strlen($t->Get('requirements')) >= 1) { ?>
				<h3>Requirements</h3>
				<p><?= nl2br($t->Get('requirements')) ?></p>
			<? } ?>
	
		<? } ?>
		
		
		<? if ($t->Get('profile_type') == PROFILE_TOUR) { /* TOUR */ ?>
	
			<? if (strlen($t->Get('itinery')) >= 1) { ?>
						
				<h3>Itinerary</h3>
				<p><?= $t->Get('itinery'); ?></p>
			<? } ?>	
			
				<div>
				<?php if (is_array($t->Get('REFDATA_TRAVEL_ARRAY')) && count($t->Get('REFDATA_TRAVEL_ARRAY')) >= 1) { ?>
					<div>
					<h3>Travel</h3>
					<ul class='select_list'>
					<?php 
					foreach($t->Get('REFDATA_TRAVEL_ARRAY') as $li) {
						print $li;
					}
					?>
					</ul>
					</div>
				<?php } ?>
				
				<?php if (is_array($t->Get('REFDATA_ACCOM_ARRAY')) && count($t->Get('REFDATA_ACCOM_ARRAY')) >= 1) { ?>
					<div>
					<h3>Accomodation</h3>
					<ul class='select_list'>
					<?php 
					foreach($t->Get('REFDATA_ACCOM_ARRAY') as $li) {
						print $li;
					}
					?>
					</ul>
					</div>
				<?php } ?>
				
				<?php if (is_array($t->Get('REFDATA_MEALS_ARRAY')) && count($t->Get('REFDATA_MEALS_ARRAY')) >= 1) { ?>				
					<div>
					<h3>Meals</h3>
					<ul class='select_list'>
					<?php 
					foreach($t->Get('REFDATA_MEALS_ARRAY') as $li) {
						print $li;
					}
					?>
					</ul>
					</div>
				<?php } ?>
				</div>
			

			<div>
			<?  if (strlen($t->Get('tour_price')) > 1) { ?>
				<h3>Tour Price</h3>
				<p><?= $t->Get('tour_price'); ?></p>
			<? } ?>

			<?php if (strlen($t->Get('included')) > 1) { ?>
				<h3>Included in Price</h3>
				<p><?= nl2br($t->Get('included')) ?></p>
			<?php } ?>

			<?php if (strlen($t->Get('local_payment')) > 1) { ?>
				<h3>Local Payment</h3>
				<p><?= $t->Get('local_payment') ?></p>
			<?php } ?>
				
			<?php if (strlen($t->Get('not_included')) > 1) { ?>				
				<h3>Not Included in Price</h3>
				<p><?= nl2br($t->Get('not_included')); ?></p>
			<?php } ?>
				
			<?php if (strlen($t->Get('dates')) > 1) { ?>
				<h3>Start Dates</h3>
				<p><?= nl2br($t->Get('dates')) ?></p>
			<?php } ?>

			<?php if (strlen($t->Get('grp_size')) > 1) { ?>
				<h3>Group Size</h3>
				<p><?= $t->Get('grp_size') ?></p>
			<?php } ?>
				
			</div>
			

				
		<? } // end profile tour ?>

		<? if ($t->Get('profile_type') == PROFILE_JOB) { /* JOB */ ?>
				<h3>Salary / Pay</h3>
				<p><?= $t->Get('job_salary') ?></p>
	
				<? if (strlen($t->Get('job_benefits')) > 1) { ?>
					<h3>Benefits</h3>
					<p><?= $t->Get('job_benefits') ?></p>
				<? } ?>
	
				<? if ($t->Get('live_in') == "t" || $t->Get('meals_inc') == "t" || $t->Get('pickup_inc') == "t") { ?>
				<h3>Extras</h3>
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td width="40px" align="left" valign="top"><input type="checkbox" name="live_in" id="live_in" class="text_input" disabled <?= ($t->Get('live_in') == "t") ? "checked" : "";  ?>  /><label for="live_in" class="checkbox_label">Live In</label></td>
						<td width="40px" align="left" valign="top"><input type="checkbox" name="meals_inc" id="meals_inc" class="text_input" disabled <?= ($t->Get('meals_inc') == "t") ? "checked" : "";  ?> /><label for="meals" class="checkbox_label">Meals</label></td>
						<td width="40px" align="left" valign="top"><input type="checkbox" name="pickup_inc" id="pickup_inc" class="text_input" disabled <?= ($t->Get('pickup_inc') == "t") ? "checked" : "";  ?> /><label for="pickup_inc" class="checkbox_label">(Airport) Pickup</label></td>	
					</tr>
				</table>
				<? } ?>
	
	
				<? if (strlen($t->Get('experience')) > 1) { ?>
					<h3>Experience Required</h3>
					<p><?= $t->Get('experience') ?></p>
				<? } ?>
				
		<? } ?>

		</div>
	</div>
	</div>
	

	<div class="row-fluid" style="margin: 20px 0px 20px 0px;">
	<div class="span12">	
		
		<? if ($t->Get('profile_type') == PROFILE_JOB) { ?>
			<h3>Apply / More Info</h3>
		<? } else { ?>
			<h3>Booking / Enquiry</h3>
		<? } ?>

		<div class="span12" style="margin: 10px;">	
		
		<?
		/* defaults for the profile type being viewed */
		if (in_array($t->Get('profile_type'), array(PROFILE_VOLUNTEER,PROFILE_TOUR))) {			
			/* is this enquiry type enabled / disabled on the company profile? */
			if ($t->Get('comp_profile')->HasEnquiryOption(ENQUIRY_BOOKING)) {
				
				/* finally if apply/booking url is specified, button should redirect to external site */
				if (strlen($t->Get('apply_url')) > 1) {
					/* button links to external apply/booking page */
					?>
					<a class="btn btn-primary" href="#" onclick="javascript: travel('<?= $t->Get('apply_url') ?>','/outgoing/<?= $t->Get('comp_url_name'); ?>/<?= $t->Get('url_name') ?>/www');" title="Apply Online" >Apply Online</a>
				
					<?					
				} else {
					/* use our apply/booking enquiry form */
					?>
					<a class="btn btn-primary" href="<?= $aEnquiryUrl['BOOKING']; ?>" title="Book this placement">Booking Enquiry</a>			
					<?
				}
			}
		}
		if (in_array($t->Get('profile_type'), array(PROFILE_VOLUNTEER,PROFILE_TOUR)) && ! $t->Get('comp_profile')->HasEnquiryOption(ENQUIRY_BOOKING)) {
			if ($t->Get('comp_profile')->HasEnquiryOption(ENQUIRY_GENERAL)) {
			?>
			<a class="btn btn-primary" href="<?= $aEnquiryUrl['GENERAL']; ?>" title="Make an enquiry">Enquiry</a>
			<?
			}
		}
		if (in_array($t->Get('profile_type'), array())) {
			?>
			<a class="btn btn-primary"  href="<?= $aEnquiryUrl['BROCHURE']; ?>" title="Request a brochure">Brouchure Request</a>
			<?
		}		
	
		if (in_array($t->Get('profile_type'), array(PROFILE_JOB,PROFILE_VOLUNTEER))) {
			if ($t->Get('comp_profile')->HasEnquiryOption(ENQUIRY_JOB_APP)) {
				if (strlen($t->Get('apply_url')) > 1) {
					/* button links to external apply page */
					?>
					<a class="btn btn-primary" target="_blank"  href="<?= $t->Get('apply_url') ?>" onclick="javascript: travel('<?= $t->Get('apply_url') ?>','/outgoing/<?= $t->Get('comp_url_name'); ?>/<?= $t->Get('url_name') ?>/www');" title="Apply Online">Apply Online</a>
					<?					
				} else {				
					?>
					<a class="btn btn-primary"  href="<?= $aEnquiryUrl['JOB_APP'] ?>" title="Apply Online" target="_blank">Apply Online</a>			
					<?
				}
			}
		}
		?>
	
		<?
		if (strlen($t->Get('url')) > 1 && $t->Get('url') != "http://") {
		?>
		<a class="btn btn-primary" href="#" onclick="javascript: travel('<?= $t->Get('url'); ?>','/outgoing/<?= $t->Get('comp_url_name'); ?>/<?= $t->Get('url_name') ?>/www');">Visit Website</a>
		<? } ?>
		
		</div>
	</div>
	</div>


    <div class="row-fluid">
    <div class="span12">
    
    	<h2><?= $oProfile->GetCompanyName(); ?> <?= $oProfile->GetTitle(); ?> Reviews</h2>
    	<?php 
    	$oReviewTemplate->LoadTemplate("/review.php");
    	print $oReviewTemplate->Render();
    	?>
    </div>
    </div>		

<!--  BEGIN Related Article -->
<?
if (is_array($oRelatedArticle->GetArticleCollection()->Get())) { 
?>

    <div class="row-fluid "> 
	<h3>Related Articles</h3>
	<div class="span12"><?
 
            $aArticle = $oRelatedArticle->GetArticleCollection()->Get();
            $limit = 6;

            for ($i=0;$i<$limit;$i++) {
                    if (is_object($aArticle[$i])) {
                            $aArticle[$i]->SetImgDisplay(FALSE);
                            $aArticle[$i]->LoadTemplate("article_summary_search_result.php");
                            print $aArticle[$i]->Render();
                    }
            } ?>
    </div>
    </div><?php
}
?>
<!--  END Related Article -->


<?php 
if (count($aRelatedProfile) >= 1) { 
?>

<div class="row-fluid">
<div class="search-result span12 pull-left">
	<h3>Related Opportunities</h3><?php 
	foreach($aRelatedProfile as $oRelatedProfile) { 
	    $aImageDetails = $oRelatedProfile->GetImageUrlArray(); ?>
    <div class="span4 featured-proj">
	<div class="img-container">
		<div class="featured-proj-img span12">
		<? if (strlen($aImageDetails['LARGE']['URL']) > 1) { ?>
  			<a title="<?= $oRelatedProfile->GetTitle(76) ?>" href="<?= "/company/".$oRelatedProfile->GetCompUrlName()."/".$oRelatedProfile->GetUrlName()  ?>" class="">
			<img class="img-responsive img-rounded" src="<?= $aImageDetails['LARGE']['URL'] ?>" alt="<?= $oRelatedProfile->GetTitle(); ?>" />		
  			</a>
			<span class="frame-overlay"></span>
		<? } else if (strlen($aImageDetails['MEDIUM']['URL']) > 1) { ?>

  			<a title="<?= $oRelatedProfile->GetTitle() ?>" href="<?= "/company/".$oRelatedProfile->GetCompUrlName()."/".$oRelatedProfile->GetUrlName() ?>" class=""> 
			<img class="img-responsive img-rounded" src="<?= $aImageDetails['MEDIUM']['URL']  ?>" alt="<?= $oRelatedProfile->GetTitle() ?>" /> 		
  			</a>
			<span class="frame-overlay"></span>
		<? } ?>
		</div>
		<div class="overlay-img">
			<a title="<?= $oRelatedProfile->GetCompanyName() ?>" href="<?= $oRelatedProfile->GetCompanyProfileUrl() ?>" target="_new" class="">
			<?= $oRelatedProfile->GetCompanyLogoUrl() ?></div>
			</a>
		</div>
		<div class="details">
        		<h3><a href="<?= "/company/".$oRelatedProfile->GetCompUrlName()."/".$oRelatedProfile->GetUrlName() ?>" title="" target="_new"><?= $oRelatedProfile->GetTitle(); ?></a></h3>
		</div>
	</div><?php 
    }
	?>
</div>
</div><?php 
}
?>


</div>
</section>


<? include("./footer.php");
