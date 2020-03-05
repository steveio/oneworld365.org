<!--  begin: FEATURED PROJECT -->
<div class="span12 search-result featured-proj-row">
<?
$aProfile = $this->Get('PROFILE_ARRAY');
$strProfileType = $this->Get('PROFILE_TYPE');


$i = 0;
foreach($aProfile as $oProfile)
{
if (!is_object($oProfile)) continue;
$oProfile->GetImages();
$aImageDetails = $oProfile->GetImageUrlArray();
?>
<div class="span4 featured-proj-s <?= ($i++ == 0) ? "" : "noshow"; ?>">

  <? if ($strProfileType == "aProfile") { ?>

  <div class="span12" style="">
        <div class="img-container">
          <? if (strlen($aImageDetails['MEDIUM']['URL']) > 1) { ?>
          <a title="<?= $oProfile->GetTitle(76) ?>" href="<?= "/company/".$oProfile->GetCompUrlName()."/".$oProfile->GetUrlName()  ?>" class="">
          <img class="img-responsive img-rounded" src="<?= $aImageDetails['MEDIUM']['URL'] ?>" alt="<?= $oProfile->GetTitle(); ?>" />
          </a>
          <? } ?>
         <div class="img-brand">
              <a title="<?= $oProfile->GetCompanyName() ?>" href="<?= $oProfile->GetCompanyProfileUrl() ?>" target="_new" class="">
              <?= $oProfile->GetCompanyLogoUrl() ?>
              </a>
         </div>
        </div>


	<div class="detail-container">


        <div class="details">
            <h4><a href="<?= "/company/".$oProfile->GetCompUrlName()."/".$oProfile->GetUrlName() ?>" title="" target="_new"><?= $oProfile->GetTitle(); ?></a></h4>
            <?php if ($oProfile->GetRating() >= 1) { ?>
            <div id="rateYo-<?= $oProfile->GetId(); ?>" class="span12 rating"></div>
            <?php } ?>
        <?php if ($oProfile->GetRating() >= 1) { ?>
        <script>
        $(document).ready(function(){

    		$("#rateYo-<?= $oProfile->GetId(); ?>").rateYo({
    			 rating: <?= $oProfile->GetRating(); ?>,
    			 starWidth: "16px",
    			 fullStar: true,
    			 readOnly: true
    		});

        });
        </script>
        <?php } ?>

        <ul class="">
            <?php if (strlen($oProfile->GetLocationLabel()) > 1) { ?>
            <li><?= $oProfile->GetLocationLabel(); ?></li>
            <?php } ?>
            <?php if (strlen($oProfile->GetDurationFromLabel()) > 1) { ?>
            <li><?= $oProfile->GetDurationFromLabel() ." - " .$oProfile->GetDurationToLabel() ." / From: ".$oProfile->GetCurrencyLabel(true)." ".$oProfile->GetPriceFromLabel(); ?></li>
            <?php } ?>
        </ul>
        </div>

	</div>
  </div>
  <? } ?>

  <? if ($strProfileType == "aCompany") { ?>
    <div style="padding: 6px;">
    <h4><a href="<?= "/company/".$oProfile->GetCompUrlName() ?>" title="" target="_new"><?= $oProfile->GetTitle(); ?></a></h4>
    <p style="font-size: .8em; line-height: 1em;">
      <?= $oProfile->GetDescShort() ?>
    </p>
    </div>
  <? } ?>
</div><?
}
?>
</div>
<!--  END FEATURED PROJECT -->
