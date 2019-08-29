<?
/*
 *
 * VIEW COMPANY
 *
 *
 */



$oProfile = new CompanyProfile();
$t = new Template();

/* check that we have been correctly invoked */
$view_mode = strtoupper($_REQUEST['m']);

if (!in_array($view_mode, array("VIEW"))) {
	Logger::DB(1,__FILE__."::".__LINE__." invalid mode: ".$_SERVER['REQUEST_URI']." ".$_SERVER['QUERY_STRING'] ." " . $_REQUEST['m']);	
	Error::StopRedirect($sUrl = $_CONFIG['url'],$sMsg = "ERROR : Bad request.");
}


/* decode message if supplied by referer */
if (strlen($_GET['m']) > 1) {
	$response['msg'] = base64_decode($_GET['m']);
}

/* get company id (if supplied) */
$id = $_REQUEST['id'];



/* an array of listing options */
/* get listing options including current rates */
$aListingOption = ListingOption::GetAll($_CONFIG['site_id'],$currency = 'GBP',$from = 0, $to = 3);


$oProfile->SetFromArray($oProfile->GetProfileById($id));
if ($oProfile->GetId() != $id) {
	Logger::DB(1,__FILE__."::".__LINE__." GetProfileById() failed id: ".$id.", ".$_SERVER['REQUEST_URI']." ".$_SERVER['QUERY_STRING']);
	Error::StopRedirect($sUrl = $_CONFIG['url'],$sMsg = "ERROR : Unable to retrieve company profile.");
}
$oProfile->GetImages();
$oProfile->GetCategoryInfo();
$oProfile->GetCountryInfo();
$oProfile->GetActivityInfo();
$oProfile->SetProfileCount();
//$oProfile->GetProfileVersionData();
//$oProfile->SetProfileVersionIdToFetch($_CONFIG['site_id']);


$oListing = new Listing();
if (!$oListing->GetCurrentByCompanyId($oProfile->GetId())) {
	$oProfile->SetListingRecordFl(false);
} else {
	$aDate = explode("-",$oListing->GetStartDate());
	$_REQUEST['ListingMonth'] = $aDate[1];
	$_REQUEST['ListingYear'] = $aDate[2];
}

if (is_object($oProfile->GetImage(0,LOGO_IMAGE))) $t->Set('logo_img',$oProfile->GetImage(0,LOGO_IMAGE)->GetHtml("",$oProfile->GetTitle()));
if (is_object($oProfile->GetImage(1,LOGO_IMAGE))) $t->Set('banner_img',$oProfile->GetImage(1,LOGO_IMAGE)->GetHtml("",$oProfile->GetTitle()));


/* extract selected cat/act/cty mappings from $_REQUEST */
$aResult = Mapping::GetFromRequest($_REQUEST);


$oActivity = new Activity($db);
$aSelected = (is_array($oProfile->activity_array)) ? $oProfile->activity_array : $aResult['act'];
$d['ACTIVITY_LIST'] = $oActivity->GetActivityLinkList($mode = "input",$aSelected);

$oCategory = new Category($db);
$aSelected = (is_array($oProfile->category_array)) ? $oProfile->category_array : $aResult['cat'];
$d['CATEGORY_LIST'] = $oCategory->GetCategoryLinkList($mode = "input",$aSelected,$slash = true,$all = false);

$oCountry = new Country($db);
$aSelected = (is_array($oProfile->country_array)) ? $oProfile->country_array : $aResult['cty'];
$d['COUNTRY_LIST'] = $oCountry->GetCountryLinkList($mode = "input",$aSelected);

$aPlacement = array();

if (!$oProfile->GetListingType() > BASIC_LISTING) {

	/* get placements associated with this company */
	$oPlacement = new PlacementProfile();	
	$aPlacement = $oPlacement->GetRelatedPlacementsByCountry($oProfile->GetCountryArray(0), $limit = 6);
	shuffle($aPlacement); // mix it up a bit..
} else {
    
    require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrSearch.php");
    require_once("/www/vhosts/oneworld365.org/htdocs/classes/SolrMoreLikeSearch.php");
 
    global $solr_config;
    
    // get some related placements
    $oSolrMoreLikeSearch = new SolrMoreLikeSearch($solr_config);
    $oSolrMoreLikeSearch->getPlacementsByPlacement($oProfile->GetOid(),null);
    $oSolrMoreLikeSearch->setRows(10);
    $aTmp = $oSolrMoreLikeSearch->getId();
    $aRelatedProfile = array();
    if (is_array($aTmp) && count($aTmp) >= 1) {
        $aRelatedId = array();
        foreach($aTmp as $idx => $a) {
            $aRelatedId[] = $a['profile_id'];
        }
        $aPlacement = PlacementProfile::Get("ID_LIST_SEARCH_RESULT",$aRelatedId);
        $aPlacement = is_array($aPlacement) && count($aPlacement) > 6 ? array_slice($aPlacement, 0, 6) : $aPlacement;
    }
}

// reviews
require_once($_CONFIG['root_path']."/classes/review.class.php");
$oReviews = new Review();
$aReview = $oReviews->Get($oProfile->GetCompanyId(),'COMPANY',1);
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
$oReviewTemplate->Set('LINK_TO', 'COMPANY');
$oReviewTemplate->Set('LINK_ID', $oProfile->GetCompanyId());
$oReviewTemplate->Set('LINK_NAME', " with ".$oProfile->GetCompanyName());
$oReviewTemplate->Set('REVIEWS',$aReview);
$oReviewTemplate->Set('REVIEWRATING',$iReviewRating);
$oReviewTemplate->Set('HASREVIEWRATING',$bHasReviewRating);
$oReviewTemplate->Set('HAS_REVIEW',true);



$oSolrMoreLikeSearch = new SolrMoreLikeSearch($solr_config);
$aFilterQuery = array();
$aFilterQuery['-title'] = "365";
$aFilterQuery['-desc_short'] = "365";
$oSolrMoreLikeSearch->setRows(10);
$aRelatedArticle = $oSolrMoreLikeSearch->getRelatedArticle($oProfile->GetOid(),$aFilterQuery);
$oRelatedArticle = new Article();
$oRelatedArticle->GetArticleCollection()->AddFromArray($aRelatedArticle);


// buttons
$aButtonHtml = array();

if (strlen($oProfile->GetUrl()) > 1 && $oProfile->GetUrl() != "http://") {
   $aButtonHtml['WEBSITE'] = "<a class=\"btn btn-primary\" href=\"".$oProfile->GetUrl()."\" target=\"_new\" onclick=\"javascript: hit('/outgoing/".$oProfile->GetUrlName()."/www');\" title=\"Visit Website\" target=\"_blank\">Visit Website</a>";
}

if (strlen($oProfile->GetApplyUrl()) > 1) {
	$aButtonHtml['APPLY'] = "<a class=\"btn btn-primary\"  href=\"".$oProfile->GetApplyUrl()."\" onclick=\"javascript: hit('/outgoing/".$oProfile->GetUrlName()."/www');\" title=\"Apply Online\" target=\"_blank\">Apply Online</a>";
}
if (strlen(trim($oProfile->GetEmail())) > 1) {
	if ($oProfile->HasEnquiryOption(ENQUIRY_BOOKING) && (strlen($oProfile->GetApplyUrl()) < 1)) {

		$aButtonHtml['BOOKING'] = "<a class=\"btn btn-primary\"  href=\"".Enquiry::GetRequestUrl('BOOKING',$oProfile->GetId(),PROFILE_COMPANY)."\" title=\"Booking Enquiry\">Booking Enquiry</a>";

	}
	if ($oProfile->HasEnquiryOption(ENQUIRY_GENERAL) && !$oProfile->HasEnquiryOption(ENQUIRY_BOOKING)) {
		$aButtonHtml['ENQUIRY'] = "<a class=\"btn btn-primary\"  href=\"".Enquiry::GetRequestUrl('GENERAL',$oProfile->GetId(),PROFILE_COMPANY)."\" title=\"Make an Enquiry\">Enquiry</a>";
	}
	if ($oProfile->HasEnquiryOption(ENQUIRY_JOB_APP)) {
		$aButtonHtml['JOB_APP'] = "<a class=\"btn btn-primary\"  href=\"".Enquiry::GetRequestUrl('JOB_APP',$oProfile->GetId(),PROFILE_COMPANY)."\" title=\"Apply Online\" target=\"_blank\">Apply</a>";
	}
}
if($oProfile->GetListingType() < BASIC_LISTING) {
	$aButtonHtml = array();
	$aButtonHtml['ENQUIRY'] = $aButtonHtml['ENQUIRY'] = "<a class=\"btn btn-primary\"  href=\"".Enquiry::GetRequestUrl('GENERAL',$oProfile->GetId(),PROFILE_COMPANY)."\" title=\"Make an Enquiry\">Enquiry</a>";;
}

//$sQs = base64_encode($oProfile->GetId()."::".PROFILE_COMPANY);
//$aButtonHtml['SEND'] = "<span><input title=\"Send to a Friend\" onclick=\"javascript: go('". $_CONFIG['url']."/email.php?&q=".$sQs."');\" type=\"submit\" value=\"Send to a friend\" class=\"submit\" /></span>";

$iRows = round(count($aButtonHtml) / 2);

if (!$oProfile->GetListingType() >= BASIC_LISTING) {
$sJSInclude = <<<EOL
<script type="text/javascript">
</script>
EOL;
}

$bNoPrototype = TRUE;	
require_once("./header_html.php");

?>






<div class="profile row">
<div class='span12'>

<div class="pull-right" style="margin: 20px;">
<div class="sharethis-inline-share-buttons"></div>
</div>



<div style="margin: 12px 0px 16px 0px;">
<? if ((strlen($t->Get('banner_img')) > 1) && ($oProfile->GetListingType() >= BASIC_LISTING)) { ?>
	<div style="div-center" class=""><?= $t->Get('banner_img'); ?></div>
<? } elseif ((strlen($t->Get('logo_img')) > 1) && ($oProfile->GetListingType() >= BASIC_LISTING)) { ?>
	<div class="div-center" style=""><?= $t->Get('logo_img'); ?></div>
<? }?>
</div>
<h1><?= $oProfile->GetTitle(); ?></h1>

<div class='lead'>

<div id="review-overallrating" style="margin-bottom: 10px;"></div>

<p class="lead"><strong><?= $oProfile->StripLinks($oProfile->GetDescShort()); ?></strong></p>

<?php
if (is_array($oProfile->GetAllImages()) && count($oProfile->GetAllImages()) >= 1) { ?>
<div class="profile-image span4 pull-right">
<ul class="unstyled"><?
        $i = 0;
        foreach($oProfile->GetAllImages() as $oImage) {
        if ($i++ == 4) break;
            if (strlen($oImage->GetHtml("_lf","")) > 1) {
                print "<li style='margin-bottom: 10px;'>".$oImage->GetHtml("_lf","")."</li>";
            } else {
                print "<li style='margin-bottom: 10px;'>".$oImage->GetHtml("_mf","")."</li>";
            }
	} ?>
</ul>
</div><?
}
?>



<? if ($oProfile->GetListingType() <=  BASIC_LISTING) { ?>
	<p><?= $oProfile->StripLinks(html_entity_decode($oProfile->GetDescLong())); ?></p>
<?php } else { ?>
	<p><?= html_entity_decode($oProfile->GetDescLong()); ?></p>
<?php } ?>


<? if ($oProfile->GetListingType() >= BASIC_LISTING) { ?>
	<? if (strlen(trim($oProfile->GetVideo())) > 1) { ?>
		<div class="span12">
		<h3>Video</h3>
		<hr />
			<?= $oProfile->GetVideo() ?>	
		</div>
	<? } ?>
<? } ?>

<? if ($oProfile->GetDuration() != "") { ?>
<h3>Duration / Dates</h3>
<p><?= nl2br($oProfile->GetDuration()) ?></p>
<? } ?> <? if ($oProfile->GetCosts() != "") { ?>
<h3>Costs / Pay</h3>
<p><?= nl2br($oProfile->GetCosts()) ?></p>
<? } ?> <? if ($oProfile->GetListingType() >= ENHANCED_LISTING) { ?>
<? if (strlen($oProfile->GetPlacementInfo()) > 1) { ?>
<h3>Placement Info</h3>
<p><?= nl2br(stripslashes($oProfile->GetPlacementInfo())); ?></p>
<? } ?>
<? } ?> 
<? if ($oProfile->GetListingType() >  BASIC_LISTING) { ?>
	<h3>Contact Info</h3>
	<? if ($oProfile->GetListingType() >= ENHANCED_LISTING) { ?>
		<p>Tel: <?= $oProfile->GetTel() ?></p>
		<? if ($oProfile->GetFax() != "") { ?> <br />
			Fax: <?= $oProfile->GetFax() ?> 
		<? } ?> 
	<? } ?> 
	<? if ($oProfile->GetAddress() != "") { ?>
		<p>Address : <?= $oProfile->GetAddress() ?></p>
	<? } ?> 
<? } ?> 

</div>


<div id="buttons" class="buttons span10">
<h3>Contact / Enquiry</h3>
<div class="span12" style="margin: 10px;">
<? for($i=0;$i<$iRows;$i++) { ?>

	<? for($j=0;$j<2;$j++) {
		print "<span style='padding-right: 7px;'>".array_shift($aButtonHtml)."</span>";
	} ?>	 		 
<? } ?>
</div>
</div>

<div class="row-fluid">
<div class="pull-left span12">
	<h2><?= $oProfile->GetCompanyName(); ?> Reviews</h2>
	<?php 
	$oReviewTemplate->LoadTemplate("/review.php");
	print $oReviewTemplate->Render();
	?>
</div>
</div>



<? 

if ($oProfile->GetListingType() >= BASIC_LISTING)
{
    $strRelatedProfileTitle = $oProfile->GetCompanyName() ." Programs <a href=\"#\" id=\"related-viewall\">( View All )</a>";
    $oPlacement = new Placement($db);
    $aPlacement = $oPlacement->GetPlacementById($oProfile->GetId(),$key = "company_id",$ret_type = "rows");
} else {
    $strRelatedProfileTitle = "Related Opportunities"; 
    if (is_array($aPlacement) && count($aPlacement) > 6) $aPlacement = array_slice($aPlacement, 0, 6);
}

if (count($aPlacement) >= 1)
{
?>
<div class="row-fluid">
<div class="search-result span12 pull-left">
	<h3><?= $strRelatedProfileTitle ?></h3>
	<div id="related-visible"><?
	
	if ((is_array($aPlacement)) && (count($aPlacement) >= 1)) {
		$i = 0;
		foreach ($aPlacement as $p) {
	
				if ($i==6) { ?>
				</div>
				<div id="related-more" style="display: none;"><?
				}
				$i++;
				$oRelatedProfile = new PlacementProfile();
				$oRelatedProfile->SetFromArray($p);
				$oRelatedProfile->GetCountryInfo();
				$oRelatedProfile->GetImages();
				$aImageDetails = $oRelatedProfile->GetImageUrlArray();	

				?>
        <div class="span4 featured-proj">
		<div class="img-container">
			<div class="featured-proj-img span12">
			<? if (strlen($aImageDetails['LARGE']['URL']) > 1) { ?>
      			<a title="<?= $oProfile->GetTitle(76) ?>" href="<?= "/company/".$oRelatedProfile->GetCompUrlName()."/".$oRelatedProfile->GetUrlName()  ?>" class="">
    			<img class="img-responsive img-rounded" src="<?= $aImageDetails['LARGE']['URL'] ?>" alt="<?= $oRelatedProfile->GetTitle(); ?>" />		
      			</a>
				<span class="frame-overlay"></span>
			<? } else if (strlen($aImageDetails['MEDIUM']['URL']) > 1) { ?>

      			<a title="<?= $oProfile->GetTitle() ?>" href="<?= "/company/".$oRelatedProfile->GetCompUrlName()."/".$oRelatedProfile->GetUrlName() ?>" class=""> 
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
    	</div><?
	}
	?>
	</div>
</div>
</div>
<script>

$(document).ready(function(){
	$('#related-viewall').click(function(e) {
	   e.preventDefault();
           $('#related-more').show();
           return false;
       });       	
});
</script>
<? 
}
} ?>

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


<? if(($oAuth->oUser->isAdmin) || ($oAuth->oUser->company_id == $oProfile->GetId())) { ?>
<div class="pull-left span12">
<h2>Admin</h2>
<p><a href="<?= $_CONFIG['url'] ?>/company/<?= $oProfile->GetUrlName() ?>/edit">Edit Company</a></p>
</div>
<? } ?>

</div>

<?php include("./footer.php"); ?>
