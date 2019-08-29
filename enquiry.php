<?php

$bNoHTMLHeader = true;

include("./header.php");


/* define which types of enquiry are available for each profile type */
$aEnqBooking = array(PROFILE_VOLUNTEER,PROFILE_TOUR,PROFILE_COMPANY);
$aEnqGeneral = array(PROFILE_VOLUNTEER,PROFILE_TOUR,PROFILE_COMPANY);
$aEnqBrochure = array(PROFILE_TOUR);
$aEnqJob = array(PROFILE_JOB);




$oAffiliateArticle = new Article;
$oAffiliateArticle->Get($_CONFIG['site_id'],"/enquiry-sent-page");
$oAffiliateArticle->LoadTemplate("article_01.php");


$oEnquiry = new Enquiry();
$t = new Template();

/*
 * An enquiry request will 
 * 	- always be in relation to a profile
 * 	- always specify the enquiry type
 * 
 * Get the enquiry type & referring profile details
 * 
 */

/* retrieve company id from url */
if (strlen($_REQUEST['q']) < 1) AppError::StopRedirect($sUrl = $_CONFIG['url'],$sMsg = 'Sorry, that was an invalid enquiry request.');	

$sParams = base64_decode($_REQUEST['q']);
$aBits = explode("::",$sParams);

$oEnquiry->SetEnquiryType($aBits[0]);
$oEnquiry->SetLinkId($aBits[1]);
$oEnquiry->SetLinkTo($aBits[2]);
$oEnquiry->SetId($aBits[3]);



if (!in_array($oEnquiry->GetEnquiryType(),array("BOOKING","GENERAL","BROCHURE","JOB_APP"))) AppError::StopRedirect($sUrl = $_CONFIG['url'],$sMsg = 'Sorry, that was an invalid enquiry request.'); 
if (!is_numeric($oEnquiry->GetLinkId())) AppError::StopRedirect($sUrl = $_CONFIG['url'],$sMsg = 'Sorry, that was an invalid enquiry request.');



/* retrieve referring profile details */
$oProfile = ProfileFactory::Get($oEnquiry->GetLinkTo());
$aProfile = $oProfile->GetProfileById($oEnquiry->GetLinkId());

if (!$aProfile) AppError::StopRedirect($sUrl = $_CONFIG['url'],$sMsg = 'Sorry, that was an invalid enquiry request.');
$oProfile->SetFromArray($aProfile);



/* process an enquiry */
if (isset($_POST['submit'])) {

	$response = array();
	
	
	if ($oEnquiry->Process($_POST,$response)) {
		$oEnquiry->GetById($oEnquiry->GetId());
		$bProcessed = true;
	}
} else { 
	$oEnquiry->GetNextId();
	$oEnquiry->SetFromArray($_POST);
	$_REQUEST['q'] = base64_encode($oEnquiry->GetEnquiryType()."::".$oEnquiry->GetLinkId()."::".$oEnquiry->GetLinkTo()."::".$oEnquiry->GetId());
}


/* get a drop down list of countries */
$oCountry = new Country($db);
$sCountryListHTML = $oCountry->GetCountryDropDown($_POST['country_id'],'country_id');




require_once("./header_html.php");


?>


<div class='enquiry row'>
<div class='span12'>


<? if (!$bProcessed) { ?>

	<?php if (is_array($response['msg']) && count($response['msg']) >= 1) { ?>
	<div class="row">
	<div class="search-page-notify span12">
	
		<div class="alert alert-error">
		<h4><?= AppError::GetErrorHtml($response['msg']);  ?></h4>
		</div>
	</div>
	</div>
	<?php } ?>


	<div class="row">
	<div class="span12">
	
	<h1><?= $oEnquiry->GetEnquiryTypeLabel(); ?></h1>
	
		<div class="span4 pull-right">
	
			<? if (strlen($oProfile->GetLogoUrl()) > 1) { ?>
				<div>
					<img src='<?=  $oProfile->GetLogoUrl() ?>' alt='' border='' class='thumbnail' />
				</div>
			<? } ?>
	
	
			<div>
				<? if ($oEnquiry->GetLinkTo() == PROFILE_PLACEMENT) { ?>
					<b>Recipient :</b> <a href="<?= $oProfile->GetCompanyProfileUrl(); ?>" class="link_sm" target="_new" title="View Profile" ><?= $oProfile->GetCompanyName(); ?></a><br />
					<b>Subject :</b> <a href="<?= $oProfile->GetProfileUrl(); ?>" class="link_sm" target="_new" title="View Profile" ><?= $oProfile->GetTitle(); ?></a><br />
				<? } elseif ($oEnquiry->GetLinkTo() == PROFILE_COMPANY) { ?>
					<b>Recipient :</b> <a href="<?= $oProfile->GetProfileUrl(); ?>" class="link_sm" target="_new" title="View Profile" ><?= $oProfile->GetTitle(); ?></a>
				<? } ?>
			</div>
	
						
		</div>
	
		<div class="row-fluid">
		<div class="span8 pull-left">
		
			<h2>Your Details</h2>
			

			<p><span class="red">(* indicates mandatory field)</span></p>
			
			<form enctype="multipart/form-data" name="" id="" action="<? $_SERVER['PHP_SELF'] ?>" method="POST">
			
			<input type="hidden" name="q" value="<?= $_REQUEST['q']; ?>" />
				
			<div class="span12">
				<span class="label_col"><label for="name" style="<?= isset($response['msg']['name']) ? "color:red;" : ""; ?>">Your Name <span class="red"> *</span></label></span>
				<span class="input_col"><input type="text" id="name" name="name" style="width: 160px" maxlength="45" value="<?= stripslashes($_POST['name']); ?>" /></span>
			</div> 
				
			<div class="span12">
				<span class="label_col"><label for="country_id" style="<?= isset($response['msg']['country_id']) ? "color:red;" : ""; ?>">Your Country <span class="red"> *</span></label></span>
				<span class="input_col"><?= $sCountryListHTML ?></span>
			</div>
	
			<div class="span12">
				<span class="label_col"><label for="email" style="<?= isset($response['msg']['email']) ? "color:red;" : ""; ?>">Contact Email <span class="red"> *</span></label></span>
				<span class="input_col"><input type="text" id="email" name="email" style="width: 160px" maxlength="50" value="<?= $_POST['email']; ?>" /></span>
			</div> 
			
			<div class="span12">
				<span class="label_col"><label for="email_conf" style="<?= isset($response['msg']['email']) ? "color:red;" : ""; ?>">Confirm Email <span class="red"> *</span></label></span>
				<span class="input_col"><input type="text" id="email_conf" name="email_conf" style="width: 160px" maxlength="50" value="<?= $_POST['email_conf']; ?>" /></span>
			</div> 
					
			<div class="span12">
				<span class="label_col"><label for="tel" style="<?= isset($response['msg']['tel']) ? "color:red;" : ""; ?>">Contact Telephone</label></span>
				<span class="input_col"><input type="text" id="tel" name="tel" style="width: 160px" maxlength="30" value="<?= $_POST['tel']; ?>" /></span>
			</div>
		
			<script type="text/javascript">
			<!--	
		        function setProfilePanelState(panel_id) {
		                var panels = ['BOOKING','GENERAL','BROCHURE','JOB_APP'];
		
		                for(var i = 0; i < panels.length; i++) {
		
		                        if (panels[i] == panel_id) {
		                                setLightSwitch(panels[i],1);
		                        } else {
		                                setLightSwitch(panels[i],0);
		                        }
		                }
		
				}
			-->
			</script>
		
			<input type="hidden" name="enquiry_type" value="<?= $oEnquiry->GetEnquiryType(); ?>" />
			
		
			<div id="BOOKING" style="<?= ($oEnquiry->GetEnquiryType() == "BOOKING") ? "visibility: visible; display: show;" : "visibility: hidden; display: none;"; ?>"> 
			<!--  BEGIN BOOKING ENQUIRY -->
					
			<div class="span12">
				<span class="label_col"><label for="title">Group Size / <br />No Travellers<span class="red"> *</span></label></span>
				<span class="input_col"><input type="text" id="grp_size" maxlength="4" style="width: 45px;" name="grp_size" value="<?= $_POST['grp_size']; ?>" /></span>
			</div>
		
			<div class="span12">
				<span class="label_col"><label for="title">Approx Budget<span class="red"> *</span></label></span>
	
				<span class="input_col">
					<input type="text" id="budget" maxlength="119" name="budget" value="<?= $_POST['budget']; ?>" />
					<!-- 
					<select name="currency">
						<option value="POUNDS" selected>pounds</option>
						<option value="EURO">euro</option>
						<option value="DOLLARS">US Dollars</option>
					</select>
					-->		
				</span>
			</div>
		
			<div class="span12">
				<span class="label_col"><label for="title">Est. Departure Date<span class="red"> *</span></label></span>
				<span class="input_col"><? print Date::GetDateInput('Departure',false,true,true); ?></span>
			</div>
		
			<div class="span12">
				<span class="label_col"><label for="enquiry" style="<?= isset($response['msg']['enquiry']) ? "color:red;" : ""; ?>">Enquiry <span class="red"> *</span></label></span>
				<span class="input_col"><textarea name="enquiry" rows="6" cols="40"><?= stripslashes($_POST['enquiry']) ?></textarea></span>
			</div>
		
			<div class="span12">
				<span class="label_col"><label for="contact_type" style="<?= isset($response['msg']['contact_type']) ? "color: red;" : ""; ?>">How to contact me <span class="red"> *</span></label></span>
				<span class="input_col">
					<select name="contact_type">
						<option value="null" <?= (!isset($_POST['contact_type'])) ? "selected" : ""; ?>></option>
						<option value="PHONE" <?= ($_POST['contact_type'] == "PHONE") ? "selected" : ""; ?>>telephone</option>
						<option value="EMAIL" <?= ($_POST['contact_type'] == "EMAIL") ? "selected" : ""; ?>>email</option>
					</select>
				</span>
			</div>
		
			<!--  END BOOKING ENQUIRY -->
			</div>
		
		
			<div id="GENERAL" style="<?= ($oEnquiry->GetEnquiryType() == "GENERAL") ? "visibility: visible; display: show;" : "visibility: hidden; display: none;"; ?>"> 
			<!--  BEGIN GENERAL ENQUIRY -->
			<div class="span12">
				<span class="label_col"><label for="general_enquiry" style="<?= isset($response['msg']['general_enquiry']) ? "color:red;" : ""; ?>">Enquiry <span class="red"> *</span></label></span>
				<span class="input_col">
					<textarea name="general_enquiry" maxlength="2000" rows="6" cols="40"><?= $_POST['general_enquiry'] ?></textarea>
				</span>
			</div>
			<!--  END GENERAL ENQUIRY -->
			</div>
			
			
		
			<div id="BROCHURE" style="<?= ($oEnquiry->GetEnquiryType() == "BROCHURE") ? "visibility: visible; display: show;" : "visibility: hidden; display: none;"; ?>"> 
			<!--  BEGIN BROCHURE REQUEST -->
		
			<div class="span12"><h1>Brochure Request</h1></div>
		
			<div class="span12">
			        <span class="label_col"><label for="brochure_type" class="f_label">I would prefer : <span class="red"> *</span></label></span>
			        <span class="input_col">
			        E-Brochure by Email <input type="radio" name="brochure_type" value="PDF" onclick="javascript: setLightSwitch('BROCHURE_ADDR',0);" <?= (($_POST['brochure_type'] == "PDF") || (!isset($_POST['brochure_type']))) ? "checked" : ""; ?>/>
			 		Printed Brochure <input type="radio" name="brochure_type" value="PRINT" onclick="javascript: setLightSwitch('BROCHURE_ADDR',1);" <?= ($_POST['brochure_type'] == "PRINT") ? "checked" : ""; ?>/>	        
			        </span>
			</div>
		
			<div id="BROCHURE_ADDR" style="<?= ($_POST['brochure_type'] == "PRINT") ? "visibility: visible; display: show;" : "visibility: hidden; display: none;"; ?>">
		
			<div class="span12">
				<span class="label_col"><label for="addr1" style="<?= isset($response['msg']['addr1']) ? "color:red;" : ""; ?>">Address <span class="red"> *</span></label></span>
				<span class="input_col"><input type="text" id="addr1" name="addr1" style="width: 160px" maxlength="80" value="<?= $_POST['addr1']; ?>" /></span>
			</div> 
		
			<div class="span12">
				<span class="label_col"><label for="addr2" style="<?= isset($response['msg']['addr2']) ? "color:red;" : ""; ?>">Town / City <span class="red"> *</span></label></span>
				<span class="input_col"><input type="text" id="addr2" name="addr2" style="width: 160px" maxlength="40" value="<?= $_POST['addr2']; ?>" /></span>
			</div> 
		
			<div class="span12">
				<span class="label_col"><label for="addr3" style="<?= isset($response['msg']['addr3']) ? "color:red;" : ""; ?>">Post / Zip code <span class="red"> *</span></label></span>
				<span class="input_col"><input type="text" id="addr3" name="addr3" style="width: 80px" maxlength="20" value="<?= $_POST['addr3']; ?>" /></span>
			</div> 
			
			</div>
		
			<!--  END BROCHURE REQUEST -->
			</div>
		
		
		
			<div id="JOB_APP" style="<?= ($oEnquiry->GetEnquiryType() == "JOB_APP") ? "visibility: visible; display: show;" : "visibility: hidden; display: none;"; ?>">
			<!--  BEGIN JOB APPLICATION -->
			
			<div class="span12"><h1>Job Application</h1></div>
			 
			<div class="span12">
				<span class="label_col"><label for="apply_letter" style="<?= isset($response['msg']['apply_letter']) ? "color:red;" : ""; ?>">Application letter <span class="red"> *</span></label></span>
				<span class="input_col">
					<textarea name="apply_letter" rows="6" cols="40"><?= $_POST['apply_letter'] ?></textarea>
				</span>
			</div>
	
			<!--	
			<div class="span12">
				<span class="label_col"><label for="experience" style="<?= isset($response['msg']['experience']) ? "color:red;" : ""; ?>">Relevant experience <span class="red"> *</span></label></span>
				<span class="input_col">
					<textarea name="experience" rows="6" cols="40"><?= $_POST['experience'] ?></textarea>
				</span>
			</div>
			-->
	

			<!--	
			<div class="span12">
				<span class="label_col"><label for="dob" style="<?= isset($response['msg']['dob']) ? "color:red;" : ""; ?>">Date of Birth <span class="red"> *</span></label></span>
				<span class="input_col">
					<? print Date::GetDateInput('DOB',true,true,true,$iYFrom = 40, $iTo = 0); ?>
				</span>
			</div>
			-->
		
			<div class="span12">
				<span class="label_col"><label for="candidate_cv" style="<?= isset($response['msg']['experience']) ? "color:red;" : ""; ?>">CV / Resume <span class="red"> *</span></label></span>
				<span class="input_col">
					<input type="file" name="candidate_cv">
					<input type="hidden" name="MAX_FILE_SIZE" value="1572864" />
					<br /><span class="p_small">(Word, PDF, TEXT: 1.5mb max size)</span>
				</span>
			</div>
			<!--  END JOB APPLICATION -->
			</div>
			
		
			
			<div class="span12">
				<span class="label_col">&nbsp;</span>
				<span class="input_col"><input class="btn-primary" type="submit" name="submit" id="submit" value="Submit" />
				</span>
			</div>
	
	
	
		</div><!--  end enquiry -->
		</div>

	</div>
	</div>
</form>

<? } else { ?>

	<div class="row">
	<div class="span12">
	
		<div class="alert alert-success">
		<h4><b>Thanks - We have sent your enquiry to <?= $oProfile->GetTitle() ?></b></h4>
		</div>
	</div>
	</div>

	<div class="span12">

		<h1><?= $oEnquiry->GetEnquiryTypeLabel(); ?> Sent</h1>
	
		<div class="pull-left" style="margin: 10px;">
			<? if ($oProfile->GetLogoUrl() != "") { ?>
				<img src="<?= $oProfile->GetLogoUrl() ?>" alt="" border="0" />
			<? } ?>
		</div>
		<h3><?= $oProfile->GetTitle(); ?></h3>
		<p><?= $oProfile->GetDescShort(); ?></p>
		<a class="btn btn-primary" href="<?= $oProfile->GetProfileUrl(); ?>" title="Back to <?= $oProfile->GetTitle(); ?>">Back to <?= $oProfile->GetTitle(); ?></a>
		
					
	</div>


	<?php 
	if (is_object($oAffiliateArticle)) { 
		print $oAffiliateArticle->Render();
	}
			
	?>

<? } ?>


</div><!--  end profile inner -->
</div><!--  end profile -->


<?

include("./footer.php");

?>
	
