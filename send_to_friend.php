<?php

/*
 * send-to-friend.php
 * 
 * handles sending profile details by email
 * 
 */


include("./header_new.php");
include("./footer_new.php");





$oEmail = new Email2Friend();


/* retrieve params from url */
if (strlen($_REQUEST['q']) < 1) AppError::StopRedirect($sUrl = $_CONFIG['url'],$sMsg = 'Sorry, that was an invalid enquiry request.');	

$sParams = base64_decode($_REQUEST['q']);
$aBits = explode("::",$sParams);

$oEmail->SetLinkId($aBits[0]);
$oEmail->SetLinkTo($aBits[1]);
$oEmail->SetId($aBits[2]);


if (!is_numeric($oEmail->GetLinkId())) AppError::StopRedirect($sUrl = $_CONFIG['url'],$sMsg = 'Sorry, that was an invalid request.');
if (!in_array($oEmail->GetLinkTo(),array(PROFILE_COMPANY,PROFILE_PLACEMENT))) AppError::StopRedirect($sUrl = $_CONFIG['url'],$sMsg = 'Sorry, that was an invalid enquiry request.');



/* retrieve referring profile details */
$oProfile = ProfileFactory::Get($oEmail->GetLinkTo());
$aProfile = $oProfile->GetProfileById($oEmail->GetLinkId());

if (!$aProfile) AppError::StopRedirect($sUrl = $_CONFIG['url'],$sMsg = 'Sorry, that was an invalid enquiry request.');
$oProfile->SetFromArray($aProfile);
$oProfile->GetImages();





/* process an enquiry */
if (isset($_POST['submit'])) {

	$response = array();
	
	$oEmail->SetFromArray($_POST);
	$oEmail->SetProfile($oProfile);
	
	if ($oEmail->Process(&$response)) {
		$bProcessed = true;
	}
} else {
	$oEmail->GetNextId();	
	$_REQUEST['q'] = base64_encode($oEmail->GetLinkId()."::".$oEmail->GetLinkTo()."::".$oEmail->GetId());	
}





print $oHeader->Render();
require_once("./templates/send_to_friend.php");
print $oFooter->Render();

?>
	
