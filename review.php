<?php

$bNoHTMLHeader = true;

include("./header.php");
require_once($_CONFIG['root_path']."/classes/review.class.php");


$oReview = new Review();

if (isset($_POST['review-submitted']))
{
	$aParams = array();
	foreach($_POST as $k => $v)
	{
		if (strstr($k, 'review-') !== false)
		{
			$k = str_replace('review-', '', $k);
			$aParams[$k] = htmlentities($v,ENT_QUOTES,"UTF-8");
		}
	}
	Validation::AddSlashes($aParams);

	$oReview->SetFromArray($aParams, false);
	$aResponse = array();
	if (!$oReview->Validate($aResponse))
	{
		$aResponse['status'] = 1;
		sendResponse($aResponse);
	}
	if (!$oReview->Add($aResponse))
	{
		$aResponse['status'] = 1;
		$aResponse['error'] = 'An error occured adding your review.  Please email info [at] oneworld365.org';
		sendResponse($aResponse);
	}

	$aResponse['status'] = 0;
	$aResponse['msg'] = 'Success: we have added your review.  It will appear on the site once we have approved it.';
	sendResponse($aResponse);
}

var_dump($_REQUEST);
$aResponse = array();

function sendResponse($aResponse) {
	header('Content-type: application/x-json');
	echo json_encode($aResponse);
	die();
}

?>
