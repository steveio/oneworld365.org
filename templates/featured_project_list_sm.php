<!--  begin: FEATURED PROJECT -->
<div class="search-result">
<?
$aProfile = $this->Get('PROFILE_ARRAY');
$strProfileType = $this->Get('PROFILE_TYPE');

foreach($aProfile as $oProfile)
{
$oProfile->GetImages();
$aImageDetails = $oProfile->GetImageUrlArray();
?>
<div class="span4 featured-proj-s">

  <? if ($strProfileType == "aProfile") { ?>

  <div class="" style="width: 100%;  margin: 0 auto;">
        <div class="" style="display: inline-block; vertical-align: top; width: 110px;">
          <? if (strlen($aImageDetails['MEDIUM']['URL']) > 1) { ?>
          <a title="<?= $oProfile->GetTitle(76) ?>" href="<?= "/company/".$oProfile->GetCompUrlName()."/".$oProfile->GetUrlName()  ?>" class="">
          <img class="img-responsive img-rounded" src="<?= $aImageDetails['MEDIUM']['URL'] ?>" alt="<?= $oProfile->GetTitle(); ?>" />
          </a>
          <? } ?>
        </div>

        <div class="" style="display: inline-block; float: right;  width: 60%;">
            <div class="" style="display: inline-block; vertical-align: top; height: 70px;">
	      <div style="height: 76px;">
              <a title="<?= $oProfile->GetCompanyName() ?>" href="<?= $oProfile->GetCompanyProfileUrl() ?>" target="_new" class="">
              <?= $oProfile->GetCompanyLogoUrl() ?>
              </a>
              </div>
            </div>

	</div>

        <div class="details" style="vertical-align: top;">
            <h4><a href="<?= "/company/".$oProfile->GetCompUrlName()."/".$oProfile->GetUrlName() ?>" title="" target="_new"><?= $oProfile->GetTitle(); ?></a></h4>
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
