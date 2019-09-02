<?php

include("./header.php");


$db = new db($dsn,$debug = false);

// Is the user authenticated?
$oAuth = new Authenticate($db,$redirect = true);
$oAuth->main();


if ($oAuth->oUser->isAdmin) {
	
	$oActivity = new Activity($db);
	$oCategory = new Category($db);	
	$oWebsite = new Website($db);
	
	$iSiteId = (is_numeric($_REQUEST['website_id'])) ? $_REQUEST['website_id'] : $_CONFIG['site_id']; 
	
	
	/* update category mappings */
	if (isset($_POST['update_website_category'])) {
		if (!Mapping::Update($bAdminRequired = true,$sTbl = "website_category_map",$sKey = "website_id",$iId = $iSiteId,$aFormValues = $_POST,$sFormKeyPrefix = "cat_",$sKey2 = "category_id")) {
			$response['msg'] = "Error : An problem occured updating the category mapping.  The dude has been notified...";
		} else {
			$response['msg'] = "Success : Updated website mappings.";
		}
	}
	
	/* update activity mappings */
	if (isset($_POST['update_website_activity'])) {
		if(!Mapping::Update($bAdminRequired = true,$sTbl = "website_activity_map",$sKey = "website_id",$iId = $iSiteId,$aFormValues = $_POST,$sFormKeyPrefix = "act_",$sKey2 = "activity_id")) {
			$response['msg'] = "Error : An problem occured updating the activity mapping.  The dude has been notified...";	
		} else {
			$response['msg'] = "Success : Updated website mappings.";
		}
	}
	
		
	
	$aSelected = $oCategory->GetSelected("website",$iSiteId);
	$sCatCheckBoxListHtml = $oCategory->GetCategoryLinkList($mode = "input",$aSelected,$slash = true,$all = true);
		
	$aSelected = $oActivity->GetSelected("website",$iSiteId);
	$sActivityCheckBoxListHtml = $oActivity->GetActivityLinkList($mode = "input",$aSelected,$slash = true,$all = true);

	
} // end is admin check



?>

<form enctype="multipart/form-data" name="edit_website" id="edit_website" action="<? $_SERVER['PHP_SELF'] ?>" method="POST">

<div id="container" style="float: left; width: 800px;">

<div id="msgtext" style="color: red;">
<?= $response['msg'];  ?>
</div>



<? if ($oAuth->oUser->isAdmin) { ?>


	<h1>Website Admin :</h1>

	<div class="row800">
	<?= $oWebsite->GetSiteDropDownList($iSiteId) ?>
	</div> 
	
	<div class="row800">
		<p>Categories</p>		
		<div class="boxItem">
		<p><?= $sCatCheckBoxListHtml ?></p> 
		<div style="float: right;" >
		<div style="text-align: right;"><p>Update categories : <input type="submit" name="update_website_category" value="go" /></p></div>
		</div>
		</div>
	</div>
	
	
	<div class="row800">
		<p>Activities</p>
		<div class="boxItem">
		<p><?= $sActivityCheckBoxListHtml ?></p> 
		<div style="float: right;" >
			<div style="text-align: right;"><p>Update activities : <input type="submit" name="update_website_activity" value="go" /></p></div>
		</div>
		</div>
	</div>


	

	
	<div class="row800">
	<h1>Navigation</h1>
	
	<p>The following items appear in the primary navigation on this website :</p>
	</div>
	
	<div class="row800">
	<h1>Homepage Content</h1>
	
	<p>The following items are displayed on this websites hompage :</p>
	
	
	</div>
	
	<div class="row800">
	<h1>Ad Banners</h1>
	<p>The following ad banners & vertical banners are displayed on this website :</p>
	
	</div>
	
	</div> <!--  end container -->

<? 
} else {
	print "<div class='msgtext'>ERROR : Only admin is permitted to manage websites.  Are you logged in as admin?</div>";
}
?>

</form>
<?



include("./footer.php");




?>