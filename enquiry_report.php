<?php


include("./init.php");
include("./header_new.php");
include("./footer_new.php");


if (!$oAuth->oUser->isValidUser) AppError::StopRedirect($sUrl = $_CONFIG['url']."/client_login.php",$sMsg = "ERROR : You must be authenticated.  Please login to continue.");



$oEnquiry = new Enquiry();

if ($oAuth->oUser->isAdmin) {
	$oCompany = new Company($db);
	$d['company_select_ddlist'] = $oCompany->getCompanyNameDropDown($_REQUEST['p_company'],null,'p_company',true);

	$aApprove = array();
	$aReject  = array();
	
	foreach($_REQUEST as $k => $v) {
		if (preg_match("/enq_/",$k)) {
			$id = preg_replace("/enq_/","",$k);
			if (isset($_REQUEST['bulk_action'])) {
				if ($_REQUEST['bulk_action'] == "approve") $aApprove[] = $id;
				if ($_REQUEST['bulk_action'] == "reject") $aReject[] = $id;
			} else {
				if ($v == "approve") $aApprove[] = $id;
				if ($v == "reject") $aReject[] = $id;
			}
		}
	}
	
	if (count($aApprove) >= 1) {
		foreach($aApprove as $id) {
			if (DEBUG) Logger::Msg("Approve : id = ".$id);
			$oEnquiry->SetStatus($id,1);			
		}
	}
	
	
	
	if (count($aReject) >= 1) {
		foreach($aReject as $id) {
			if (DEBUG) Logger::Msg("Reject : id = ".$id);
			$oEnquiry->SetStatus($id,3);			
		}
	}

}


$iLimit = (is_numeric($_REQUEST['p_company'])) ? "null" : 50; 

/* filtering by company */
$company_id = NULL;
if (!$oAuth->oUser->isAdmin) {
	$company_id = $oAuth->oUser->company_id; /* non admin can see only their own enquiries */ 
} elseif (is_numeric($_REQUEST['p_company'])) {
	$company_id = $_REQUEST['p_company']; /* admin is viewing report filtered by company */
}


if ($oAuth->oUser->isAdmin) {
	$aEnquiryPending = $oEnquiry->GetAll($iStatusFrom = 0, $iStatusTo = 0, $iLimit,$company_id);
}

if ($oAuth->oUser->isAdmin) {
	$aEnquiryProcessed = $oEnquiry->GetAll($iStatusFrom = 1, $iStatusTo = 5, $iLimit,$company_id);	
} else { /* only show approved enquiries */
	$aEnquiryProcessed = $oEnquiry->GetAll($iStatusFrom = 0, $iStatusTo = 2, $iLimit,$company_id);
}




  

if (is_numeric($company_id)) { /* filter results */
	if (is_array($aEnquiryPending)) {
		foreach($aEnquiryPending as $oEnquiry) {
			if ($oEnquiry->company_id == $company_id) {
				$aEnquiryPendingFiltered[] = $oEnquiry; 
			}
		}
		$aEnquiryPending = $aEnquiryPendingFiltered;
	}
	if (is_array($aEnquiryProcessed)) {
		foreach($aEnquiryProcessed as $oEnquiry) {
			if ($oEnquiry->company_id == $company_id) {
				$aEnquiryProcessedFiltered[] = $oEnquiry; 
			}
		}
		$aEnquiryProcessed = $aEnquiryProcessedFiltered; 
	}
}


print $oHeader->Render();

?>

<!-- BEGIN Page Content Container -->
<div class="page_content content-wrap clear">
<div class="row pad-tbl clear">


<? if ($oAuth->oUser->isAdmin) {  ?>

<div id='row800' style='margin: 20px 0px 40px 0px;'>
	<form enctype="multipart/form-data" name="placement_edit" id="placement_edit" action="#" method="POST">		
		<?= $d['company_select_ddlist']; ?>
		<input type="submit" name="doEnquiryByCompany" value=" Go " style="width: 45px;" class="textinput" />
	</form>
</div>
<? } ?>


<? if ($oAuth->oUser->isAdmin) {  ?>
<? if (!is_numeric($_REQUEST['p_company'])) { ?>

	<form enctype="multipart/form-data" name="process_enquiry" id="process_enquiry" action="#" method="POST">

	<div id='row800'>
	
	<h1>50 Most Recent Enquiries (Pending)</h1>
	
	<table cellspacing="2" cellpadding="4" border="0" width="800px">	
	
	<tr>
		<th>date</th>
		<th>site</th>
		<th>type</th>
		<th>about</th>
		<th>from</th>
		<th>country</th>
		<th>enquiry</th>
		<th>&nbsp;</th>		
	</tr>
		
	<? foreach($aEnquiryPending as $oEnquiry) { ?>
		<? $class = ($class == "hi") ? "" : "hi"; ?>
		<tr class='hi'>
			<td width="80px" valign="top"><?= $oEnquiry->GetDate() ?></td>
			<td width="80px" valign="top"><?= $oEnquiry->GetSiteName() ?></td>
			<td width="80px" valign="top"><?= $oEnquiry->GetEnquiryTypeLabel() ?></td>
			<td width="160px" valign="top">
				<? if (strlen($oEnquiry->GetPlacementName()) > 1) { ?>
				<a class="p_small" href="./company/<?= $oEnquiry->GetCompanyUrlName() ."/". $oEnquiry->GetPlacementUrlName() ?>" title="<?= $oEnquiry->GetPlacementName() ?>"><?= $oEnquiry->GetPlacementName() ?></a>
				<br/>
				<? } ?>
				<a class="p_small" href="./company/<?= $oEnquiry->GetCompanyUrlName(); ?>" title="<?= $oEnquiry->GetCompanyName(); ?>"><?= $oEnquiry->GetCompanyName(); ?></a>
			</td>
			<td width="160px" valign="top"><?= $oEnquiry->GetName() ."<br /> (".$oEnquiry->GetEmail().")" ?></td>
			<td width="80px" valign="top"><?= $oEnquiry->GetCountryName() ?></td>
			
			<td width="20px"><input type="submit" name="enq_<?= $oEnquiry->GetId() ?>" value="approve" /></td>
			<td width="20px"><input type="submit" onclick="javscript: return confirm('Are you sure you wish to reject this enquiry?');" name="enq_<?= $oEnquiry->GetId() ?>" value="reject" /></td>
			<td width="20px" valign="top"><input type="checkbox" id="enq_<?= $oEnquiry->GetId() ?>" name="enq_<?= $oEnquiry->GetId() ?>" value="approve" /></td>
		</tr>
		<tr class='hi'>
			<td>&nbsp;</td>
			<td colspan="6" width="200px" valign="top"><?= $oEnquiry->GetEnquiry() ?></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		
		<!-- 
		<? if ($oEnquiry->GetEnquiryType() == "BOOKING") { ?>
		<tr>
			<td colspan=2></td>
			<td><i>No Travellers</i></td>
			<td><i>Budget</i></td>
			<td><i>Est Dept Date</i></td>
			<td><i>Contact Method</i></td>
		</tr>
		<tr>
			<td colspan=2>&nbsp;</td>
			<td valign="top"><?= $oEnquiry->GetGroupSize(); ?></td>
			<td valign="top"><?= $oEnquiry->GetBudget(); ?></td>
			<td valign="top"><?= $oEnquiry->GetDeptDate(); ?></td>
			<td valign="top"><?= $oEnquiry->GetContactType(); ?></td>
		</tr>
		<? } ?>
		 -->
		
		
	<? } ?>
	<tr class="hi">
		<td colspan="9" align="right">
			<select name="bulk_action">
				<option value="approve">approve selected</option>
				<option value="reject">reject selected</option>
			</select>
			<input type="submit" name="go_batch" value="go" onClick="this.form.submit()" />
		
		</td>	
	</tr>
	</table>
	
	</div>
	</form>
<? } ?>
<? } ?>


<hr />


<div id='row800' style='margin-top: 40px;'> 

<? 
if (is_numeric($company_id)) { 
	print "<h1>Recent Enquiries</h1>";
} else {
	print "<h1>50 Most Recent Enquiries</h1>";
}
?>



<table cellspacing="2" cellpadding="4" border="0" width="800px">

<tr>
	<th>date</th>
	<th>type</th>
	<th>about</th>
	<th>from</th>
	<th>enquiry</th>
	<? if ($oAuth->oUser->isAdmin) {  ?>
	<th>status</th>
	<? } ?>
</tr>
<? 
foreach($aEnquiryProcessed as $oEnquiry) {
?>
	<? $class = ($class == "hi") ? "" : "hi"; ?>
	<tr class='<?= $class ?>'>
		<td width="80px" valign="top"><?= $oEnquiry->GetDate() ?></td>
		<td width="80px" valign="top"><?= $oEnquiry->GetEnquiryTypeLabel() ."<br />(".$oEnquiry->GetSiteName() ?>)</td>
		<td width="160px" valign="top">
			<? if (strlen($oEnquiry->GetPlacementName()) > 1) { ?>
			<a class="p_small" href="./company/<?= $oEnquiry->GetCompanyUrlName() ."/". $oEnquiry->GetPlacementUrlName() ?>" title="<?= $oEnquiry->GetPlacementName() ?>"><?= $oEnquiry->GetPlacementName() ?></a>
			<br/>
			<? } ?>
			<a class="p_small" href="./company/<?= $oEnquiry->GetCompanyUrlName(); ?>" title="<?= $oEnquiry->GetCompanyName(); ?>"><?= $oEnquiry->GetCompanyName(); ?></a>
		</td>
		<td width="160px" valign="top"><?= $oEnquiry->GetName() ."<br /> (".$oEnquiry->GetEmail().")" . "<br />". $oEnquiry->GetCountryName() ?></td>
		<td width="200px" valign="top"><?= $oEnquiry->GetEnquiry() ?></td>
		<? if ($oAuth->oUser->isAdmin) {  ?>
		<td width="20px" valign="top"><?= $oEnquiry->GetStatusLabel() ?></td>
		<? } ?>
	</tr>
<?
} 
?>
</table>


</div>

<? if ($oAuth->oUser->isAdmin) {  ?>
</form>
<? } ?>

</div>
</div>
<!-- END Page Content Container -->

<?
print $oFooter->Render();
?>
