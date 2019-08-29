<?

include("./header.php");


if (!is_object($db))
	$db = new db($dsn,$debug = false);


$oCompany = new Company($db);
$oPlacement = new Placement($db);

$d['company_select_ddlist'] = $oCompany->getCompanyNameDropDown();


// Is the user authenticated?
$oAuth = new Authenticate($db,$redirect = "./index.php");
$oAuth->main();

// this page is admin only 
if (!$oAuth->oUser->isAdmin) {
	die("Only admin can view this area.  Are you logged in?");
}



$oUser = new User($db);


// delete a user
if ($_GET['m'] == "del") {
	$id = $_GET['id'];
	$company_id = $_GET['p_company'];
	if ((is_numeric($id)) && (is_numeric($company_id))) {
		if ($oUser->deleteUser($id,$company_id)) {
			$response['msg'] = "Success : Deleted User Account";
		}
	}
}

// add a new user
if (isset($_POST['add_user'])) {
	$oUser->addUser();
	$response['msg'] = $oUser->msg;
}

// display edit user list
if (isset($_REQUEST['p_company'])) {
	$sUserEditListHTML = $oUser->getUserEditList($_REQUEST['p_company']);
}

/* account(s) pending */

$oAccount = new AccountApplication();
$aNewAccount = $oAccount->GetPendingList();
$aRecentAccount = $oAccount->GetRecentList();


/* recent activity */

$aCompany = $oCompany->GetCompanyList("RECENT",null,"OBJECTS");
$aPlacement = $oPlacement->GetPlacementById(null,$key = "recent",$ret_type = "objects");


?>

<div class="page_content content-wrap clear">
<div class="row pad-tbl clear">




<div id="edit_user">	


<div id="msgtext" style="color: red;">
<?= $response['msg'];  ?>
</div>




<form action="<?= $_CONFIG['url'] ?>/approve" id="user_edit_form" method="get">
	

	<div id="account_pending_list" class="table-border">

	<h1>Approve New Account Requests</h1>

		<table cellspacing='0' cellpadding='4' border='0' width="800px;">

		<tr>
		<th>&nbsp;</th>
		<th>Site</th>
		<th>Name</th>
		<th>Country</th>
		<th>Comp Name</th>
		<th>Comp Type</th>	
		<th>Type</th>
		<th>Comments</th>
		<th>Approve</th>
		<th>Reject</th>
		</tr>			

		<?
		
		$i = 0;
		
		if ((is_array($aNewAccount)) && (count($aNewAccount) >= 1)) { 
			foreach($aNewAccount as $oAccount) {
				$class = ($class == "hi") ? "" : "hi";  
		?>
			<tr class="<?= $class ?>">
			<td width="10px"><?= ++$i; ?></td>
			<td><?= $oAccount->website_name ?></td>
			<td width="80px"><?= $oAccount->name ?> <br /><?= $oAccount->role ?><br /><?= $oAccount->email ?> <br />tel: <?= $oAccount->tel ?></td>
			<td><?= $oAccount->country_name ?></td>
			<td><a href="<?= $oAccount->company_profile_link ?>" target="_new" title="view company profile" ><?= $oAccount->company_name ?></a></td>
			<td><?= $oAccount->company_type ?></td>
			<td><?= $oAccount->account_name ?></td>
			<td width="200px"><?= $oAccount->comments ?></td>
			<td><input type="submit" name="ac_<?= $oAccount->id ?>" value="approve" /></td>
			<td><input type="submit" onclick="javscript: return confirm('Are you sure you wish to reject this application?');" name="ac_<?= $oAccount->id ?>" value="reject" /></td>
			</tr>		
		<?  
			} // end foreach
		} else { 
		
		?>
			<tr><td colspan='6'>There are 0 accounts waiting to be approved.</td></tr>
		<? } ?>		
		</table>

	</div>
</form>



<div id="recent_account_list" class="table-border">

	<h1>Recent Account Requests</h1>

	<table cellspacing='0' cellpadding='4' border='0'>

	<tr>
	<th>&nbsp;</th>
	<th>Site</th>
	<th>Name</th>
	<th>Comp Name</th>
	<th>Comp Approved</th>
	<th>Comments</th>
	<th>Account</th>
	<th>Account Status</th>
	<th>Recieved Date</th>
	</tr>			

	<?
	
	$i = 0;
	
 
	foreach($aRecentAccount as $oAccount) {
		$class = ($class == "hi") ? "" : "hi";  
	?>
		<tr class="<?= $class ?>">
		<td><?= ++$i; ?></td>
		<td><?= $oAccount->website_name; ?></td>
		<td><?= $oAccount->name ?><br /><?= $oAccount->role ?><br /><?= $oAccount->country_name ?><br /><?= $oAccount->email ?><br />tel: <?= $oAccount->tel ?></td>
		<td><a href="<?= $oAccount->company_profile_link ?>" target="_new" title="view company profile" ><?= $oAccount->company_name ?></a><br /><?= $oAccount->company_type ?></td>			
		<td><?= ($oAccount->comp_status == 1) ? "Yes" : "No"; ?></td>
		<td width="200px"><?= $oAccount->comments ?></td>
		<td><?= $oAccount->account_name ?></td>
		<td><?= $oAccount->status ?></td>
		<td><?= $oAccount->receieved ?></td>
		</tr>		
	<?  
		} // end foreach
	?>
	</table>

</div>


<div class="table-border" style="float: left; width: 300px;">
<h1>Recently Updated Companies</h1>
<table cellpadding="2" cellspacing="2" border="0">
	<tr>
		<th width="140px">Title</th>
		<th>Added</th>
		<th>Last Updated</th>
		<th>Status</th>
		<th>Edit</th>
	</tr>

	<?
	foreach($aCompany as $oCompany) {
		$class = ($class == "hi") ? "" : "hi";
	?>
	<tr class="<?= $class ?>">
		<td><a href="<?= $_CONFIG['url']."/company/".$oCompany->url_name ?>"
			title="View <?= $oCompany->title ?>"><?= $oCompany->title ?></a></td>
		<td><?= $oCompany->added_date ?></td>
		<td><?= $oCompany->updated_date ?></td>
		<td><?= ($oCompany->status == 1) ? "Approved" : "Pending"; ?></td>
		<td><a
			href="<?= $_CONFIG['url']."/company/".$oCompany->url_name ?>/edit/"
			title="Edit <?= $oCompany->title ?>">edit</a></td>
	</tr>
	<? } ?>
</table>
</div>

<div class="table-border" style="margin-left: 20px; float: left; width: 400px;">

	<h1>Recently Updated Placements</h1>
	<table cellpadding="2" cellspacing="2" border="0">
	<tr>
	<th width="340px">Title</th>
	<th>Company</th>
	<th>Added</th>
	<th>Last Updated</th>
	<th>Edit</th>	
	
	</tr>
	
	<?
	foreach($aPlacement as $oPlacement) {
		$class = ($class == "hi") ? "" : "hi";
	?>
	<tr class="<?= $class ?>">
	<td><a href="<?= $_CONFIG['url'] ."/company/".$oPlacement->comp_url_name."/".$oPlacement->url_name ?>" title="view placement"><?= $oPlacement->title ?></a></td>
	<td><?= $oPlacement->company_name ?></td>
	<td><?= $oPlacement->added_date ?></td>
	<td><?= $oPlacement->updated_date ?></td>
	<td><a href="<?= $_CONFIG['url'] ."/company/".$oPlacement->comp_url_name."/".$oPlacement->url_name."/edit/" ?>" title="edit placement">edit</a></td>
	</tr>
	<? } ?>
	</table>

</div>

<div style="float: left; width: 600px;">

<h1>Add / Edit User</h1>
<p>Select company to edit existing users :</p>
<form action="#" id="user_edit_form" method="post">
	<?= $d['company_select_ddlist']; ?>
	<span class="input_col"><input type="submit" name="list_user" id="submit" value="Submit" />

	<div id="user_edit_list" style="margin-top: 10px;">
		<?= $sUserEditListHTML ?>
	</div>
</form>

<br />

<p>Or add a new user account :</p>
<div id="user_form" style="margin-top: 10px;">
<form action="#" id="add_user_form" method="post">
	<p><label for="name"><b>Name</b></label> <input type="text" id="title" class="text_input" name="p_name" value="<?= $_POST['p_name']; ?>" /></p>
	<p><label for="email"><b>Email</b></label> <input type="text" id="title" class="text_input" name="p_email" value="<?= $_POST['p_email']; ?>" /></p>
	<p><label for="uname"><b>Username</b></label> <input type="text" id="title" class="text_input" name="p_uname" value="<?= $_POST['p_uname']; ?>" /></p>
	<p><label for="pass"><b>Password</b></label> <input type="text" id="title" class="text_input" name="p_pass" value="<?= $_POST['p_pass']; ?>" /></p>
	<p><label for="company"><b>Company</b></label>
	<?= $d['company_select_ddlist']; ?>	</p>
	<input type="submit" name="add_user" id="submit" value="Submit" />	
</form>

</form>
</div>
</div>

</div>
</div>

