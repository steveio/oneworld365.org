<?php

require_once($_CONFIG['root_path']."/classes/EmailSender.php");
require_once($_CONFIG['root_path']."/classes/htmlMimeMail.php");
require_once($_CONFIG['root_path']."/classes/RFC822.php");
require_once($_CONFIG['root_path']."/classes/smtp.php");
require_once($_CONFIG['root_path']."/classes/mimePart.php");


include("./header.php");



/* retrieve account application id from url */

foreach($_REQUEST as $account_str => $v) { // qs = ?ac_1262=approve 
	if (preg_match("/ac_/",$account_str)) {
		$aBits = explode("_",$account_str);
		$id = $aBits[1];
		$mode = $v; /* approve | reject */ 
		if (!is_numeric($id)) die("Error : Oops something seems to have gone wrong");
		break;
	}
}


/* get details of the account pending approval */
$oAccountService = new AccountApplication();
$oAccount = $oAccountService->GetById($id);


$status = "PENDING";
$response = array();


if (isset($_POST['do_approve'])) {
	if ($oAccountService->Approve($oAccount,$oAccount->email,$oAccount->password,$response)) {
		$response['msg'] = "Success : Approved New User Account";
		$status = "APPROVED";
	}
}

if ($mode == "reject") {
	if ($oAccountService->Reject($oAccount,$response)) {
		$response['msg'] = "Success : Rejected User Account";
		$status = "REJECTED";
		unset($mode);
	} else {
		print $response['msg'];
	}
}




?>

<div class="page_content content-wrap clear">
<div class="row pad-tbl clear">


<div style='position: relative; float: left; width: 618px; padding: 20px 20px 20px 20px; margin: 10px 0px 20px 0px; border: 1px dotted #cccccc;'>

<? if ($status == "PENDING") { ?>

<h1>Approve Account</h1>


<h2>Request Details</h2>

<table cellspacing='2' cellpadding='4' border='0'>
<tr>
<th>Name</th><td><?= $oAccount->name ?></td>
</tr>			

<tr>
<th>Role</th><td><?= $oAccount->role ?></td>
</tr>			

<tr>
<th>Email</th><td><?= $oAccount->email ?></td>
</tr>			

<tr>
<th>Tel</th><td><?= $oAccount->tel ?></td>
</tr>			

<tr>
<th>Country</th><td><?= $oAccount->country_name ?></td>
</tr>

<tr>
<th>Comments</th><td colspan="3"><?= $oAccount->comments ?></td>
</tr>			
</table>


<h2>Company Details</h2>

<table cellspacing='2' cellpadding='4' border='0'>
<tr>
<th>Name</th><td><?= $oAccount->company_name ?></td>
</tr>
<tr>
<th>View</th><td><a href="<?= $_CONFIG['url']."/company/".$oAccount->comp_url_name."/edit/" ?>" target="_new" title="view company"><?= $_CONFIG['url']."/company/".$oAccount->comp_url_name; ?></a></td>
</tr>
<tr>
<th>Edit</th><td><a href="http://admin.oneworld365.org/company/<?= $oAccount->comp_url_name; ?>/edit/" target="_new" title="edit company">http://admin.oneworld365.org/company/<?= $oAccount->comp_url_name; ?>/edit/</a></td>
</tr>

<tr>
<th>Status</th><td><?= $oAccount->status ?></td>
</tr>


</table>


<h2>Username / Password</h2>

<div id="msgtext" style="color: red;">
<?= $response['msg'];  ?>
</div>

<form enctype="multipart/form-data" name="" action="#" method="POST">
<input type="hidden" name="<?= $account_str ?>" value="<?= $mode ?>" />

<table cellspacing='0' cellpadding='4' border='0'>
<tr>
<td>User Name: </td><td><?= $oAccount->email ?></td>
</tr>
<tr>	
<td>Password: </td><td><?= $oAccount->password ?></td>
</tr>
<tr>
<td><input type="submit" name="do_approve" id="submit" value="Confirm" /></td>
</tr>
</table>
</form>	
<? } ?>



<? if ($status == "APPROVED") { ?>
	<h1>Account Approved</h1>
	<p>A new account has been setup for <?= $oAccount->name ?> from <?= $oAccount->company_name ?>.<br /><br />
	Username : <?= $oAccount->email ?><br />
	Password : <?= $oAccount->password ?>
	</p>
<? } ?>


<? if ($status == "REJECTED") { ?>
	<h1>Account Rejected</h1>
	<p>Requested account for <?= $oAccount->name ?> from <?= $oAccount->company_name ?> has been rejected.</p>
<? } ?>


</div>


</div>
</div>

<?

include("./footer.php");

?>
	
