<?php
if ($_SERVER['REQUEST_URI'] == "/contact.php") {
	header('Location: '.$_CONFIG['url']."/contact-us");
}


$bNoHTMLHeader = true;
include("./header.php");

require_once("./classes/SalesEnquiry.php");
require_once("./classes/SecurityQuestion.php");


$oSalesEnquiry = new SalesEnquiry();

$sDisplay = "FORM";


$aQuestions = array( 
					1 => array('id' => 1, 
						   'question' => 'Please type the word <b>human</b>',
						   'answers' => array('human')
						   )
					   );


if ($_POST['enq_submitted'] == "TRUE") {
	
	$oSalesEnquiry->SetWebsiteId(0);
	$oSalesEnquiry->SetIpAddress(IPAddress::GetVisitorIP());
	$oSalesEnquiry->SetFromArray($_POST);

	$aQuestion = $aQuestions[$_REQUEST['security_qid']];
	$oSecurityQuestion = new SecurityQuestion($aQuestion['id'],$aQuestion['question'],$aQuestion['answers']);
	$oSecurityQuestion->SetResponse($_REQUEST['security_q']);
	$oSecurityQuestion->Verify();
	$oSalesEnquiry->SetSecurityQuestion($oSecurityQuestion);
	
	
	if ($oSalesEnquiry->Process()) {
		$sDisplay = "CONFIRMATION";
	}
} else {
	$oSalesEnquiry->GetNextId();
	
	// select a new security question
	shuffle($aQuestions);
	$aQuestion = array_shift($aQuestions);
	$oSecurityQuestion = new SecurityQuestion($aQuestion['id'],$aQuestion['question'],$aQuestion['answers']);   
	$oSalesEnquiry->SetSecurityQuestion($oSecurityQuestion);
	
}


?>



<?php 
include("./header_html.php");
?>




<div>
<div class="span12">


<form name="SalesEnquiryForm" action="/contact-us" method="post" id="SalesEnquiryForm">

<input type="hidden" name="enq_submitted" value="TRUE" />
<input type="hidden" name="id" value="<?= base64_encode($oSalesEnquiry->GetId()) ?>" />


<?php if ($sDisplay == "FORM") { ?>

<h1>Contact Us</h1>

<p>To add your organisation to our site or to update an existing listing <a href="http://admin.oneworld365.org/">please click here</a></p>

<p>For all other enquiries please contact us via the enquiry form below or call us on UK: +44 (20) 8123 8284.</p>


<div class="fieldsetWrapper">
	<fieldset>
		<div class="pad">

		<? if (strlen($oSalesEnquiry->GetErrorMessage()) > 1) { ?>
			<div><p class="red"><?= $oSalesEnquiry->GetErrorMessage(); ?></p></div>
		<? } ?>
		
		
		<div class="row">
			<?php $sErrorCss = (strlen($oSalesEnquiry->GetValidationErrorById('enq_name')) > 1) ? "red" : ""; ?>				
			<span class="label_col"><label for="name"><span class="<?= $sErrorCss; ?>">Your Name<span class='red'>*</span></span></label></span>
			<span class="input_col">
				<input type="text" name="enq_name" class="textbox250" value="<?= $oSalesEnquiry->GetName(); ?>"  />			
			<?php if (strlen($oSalesEnquiry->GetValidationErrorById('enq_name')) > 1) { ?>
				<br /><span class="error red"><?= $oSalesEnquiry->GetValidationErrorById('enq_name'); ?></span>			
			<?php } ?>
			</span>
			<span class='q_help'></span>			
		</div>

		<div class="row">
			<?php $sErrorCss = (strlen($oSalesEnquiry->GetValidationErrorById('enq_role')) > 1) ? "red" : ""; ?>				
			<span class="label_col"><label for="name"><span class="<?= $sErrorCss; ?>">Your Role / Position<span class='red'>*</span></span></label></span>
			<span class="input_col">
				<input type="text" name="enq_role" class="textbox250" value="<?= $oSalesEnquiry->GetRole(); ?>"  />			
			<?php if (strlen($oSalesEnquiry->GetValidationErrorById('enq_role')) > 1) { ?>
				<br /><span class="error red"><?= $oSalesEnquiry->GetValidationErrorById('enq_role'); ?></span>			
			<?php } ?>
			</span>
			<span class='q_help'></span>			
		</div>
		
		<div class="row">
			<?php $sErrorCss = (strlen($oSalesEnquiry->GetValidationErrorById('enq_comp_name')) > 1) ? "red" : ""; ?>				
			<span class="label_col"><label for="name"><span class="<?= $sErrorCss; ?>">Company / Organisation Name<span class='red'>*</span></span></label></span>
			<span class="input_col">
				<input type="text" name="enq_comp_name" class="textbox250" value="<?= $oSalesEnquiry->GetCompanyName(); ?>"  />			
			<?php if (strlen($oSalesEnquiry->GetValidationErrorById('enq_comp_name')) > 1) { ?>
				<br /><span class="error red"><?= $oSalesEnquiry->GetValidationErrorById('enq_comp_name'); ?></span>			
			<?php } ?>
			</span>
			<span class='q_help'></span>			
		</div>	

		<div class="row">
			<?php $sErrorCss = (strlen($oSalesEnquiry->GetValidationErrorById('enq_email')) > 1) ? "red" : ""; ?>				
			<span class="label_col"><label for="name"><span class="<?= $sErrorCss; ?>">Contact Email<span class='red'>*</span></span></label></span>
			<span class="input_col">
				<input type="text" name="enq_email" class="textbox250" value="<?= $oSalesEnquiry->GetEmail(); ?>"  />			
			<?php if (strlen($oSalesEnquiry->GetValidationErrorById('enq_email')) > 1) { ?>
				<br /><span class="error red"><?= $oSalesEnquiry->GetValidationErrorById('enq_email'); ?></span>			
			<?php } ?>
			</span>
			<span class='q_help'></span>			
		</div>	

		<div class="row">
			<?php $sErrorCss = (strlen($oSalesEnquiry->GetValidationErrorById('enq_tel')) > 1) ? "red" : ""; ?>				
			<span class="label_col"><label for="name"><span class="<?= $sErrorCss; ?>">Contact Telephone No<span class='red'>*</span></span></label></span>
			<span class="input_col">
				<input type="text" name="enq_tel" class="textbox250" value="<?= $oSalesEnquiry->GetTel(); ?>"  />			
			<?php if (strlen($oSalesEnquiry->GetValidationErrorById('enq_tel')) > 1) { ?>
				<br /><span class="error red"><?= $oSalesEnquiry->GetValidationErrorById('enq_tel'); ?></span>			
			<?php } ?>
			</span>
			<span class='q_help'></span>			
		</div>	

		<div class="row">
			<?php $sErrorCss = (strlen($oSalesEnquiry->GetValidationErrorById('enq_details')) > 1) ? "red" : ""; ?>				
			<span class="label_col"><label for="name"><span class="<?= $sErrorCss; ?>">Enquiry<span class='red'>*</span></span></label></span>
			<span class="input_col">
				<textarea name="enq_details" class="" style="width: 400px; height: 200px;" ><?= $oSalesEnquiry->GetEnquiry(); ?></textarea>
			<?php if (strlen($oSalesEnquiry->GetValidationErrorById('enq_details')) > 1) { ?>
				<br /><span class="error red"><?= $oSalesEnquiry->GetValidationErrorById('enq_details'); ?></span>			
			<?php } ?>
			</span>
			<span class='q_help'></span>			
		</div>	

		<div class="row">
			<?php $sErrorCss = (strlen($oSalesEnquiry->GetValidationErrorById('security_q')) > 1) ? "red" : ""; ?>				
			<span class="label_col"><label for="name"><span class="<?= $sErrorCss; ?>">Security Question<span class='red'>*</span></span></label></span>
			<span class="input_col">
				<label for="name"><?= $oSalesEnquiry->GetSecurityQuestion()->GetQuestion(); ?></label>
				<input type="text" name="security_q" class="textbox250" value="<?= $_REQUEST['security_q']; ?>"  />
				<input type="hidden" name="security_qid" value="<?= $oSalesEnquiry->GetSecurityQuestion()->GetId(); ?>"  />
			<?php if (strlen($oSalesEnquiry->GetValidationErrorById('security_q')) > 1) { ?>
				<br /><span class="error red"><?= $oSalesEnquiry->GetValidationErrorById('security_q'); ?></span>			
			<?php } ?>
			</span>
			<span class='q_help'></span>			
		</div>	


		<div class="row">
			<span class="label_col">&nbsp;</span>
			<span class="input_col">
				<input type="submit" title="Submit" name="submit_enquiry" value="Submit" />
			</span>
		</div>			
		
		
		</div>
	</fieldset>
</div>

</form>		

<? } ?>


<?php if ($sDisplay == "CONFIRMATION") { ?>


		<h1>Thanks for your Enquiry</h1>
		<p class="lead">We will get back to you shortly.</p>

		<br /><br />

		<img src="/images/photo_strip_05.jpg" alt="" border="0" />

<? } ?>


</div>
</div>


<?
include("./footer.php");
?>
	
