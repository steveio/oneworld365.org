<!--  BEGIN PROMOTED PROJECT -->
<?
$aProfile = & $this->Get('PROFILE_ARRAY');
?>
<div class="search-result">
<div class="span4" style="float: right;">

<h5>Featured Programs</h5><?php

for($j=0; $j<3;$j++) {
    $oProfile = array_shift($aProfile);
    if (!is_object($oProfile)) continue;
    $oProfile->GetImages();
    $aImageDetails = $oProfile->GetImageUrlArray(); ?>

    <div class="span12 featured-proj-s" style="display: inline-block; height: 100%; margin: 6px;  ">
      <div class="" style="width: 100%;  margin: 0 auto; padding: 6px;">
           <div class="" style="display: inline-block; vertical-align: top;">
              <? if (strlen($aImageDetails['MEDIUM']['URL']) > 1) { ?>
              <a title="<?= $oProfile->GetTitle(76) ?>" href="<?= "/company/".$oProfile->GetCompUrlName()."/".$oProfile->GetUrlName()  ?>" class="">
              <img class="img-responsive img-rounded" src="<?= $aImageDetails['MEDIUM']['URL'] ?>" alt="<?= $oProfile->GetTitle(); ?>" />
              </a>
              <? } ?>
            </div>

            <div class="" style="display: inline-block; width: 100%;">
                <div class="" style="display: inline-block; float: left; width: 20%; margin: 6px;" >
                  <a title="<?= $oProfile->GetCompanyName() ?>" href="<?= $oProfile->GetCompanyProfileUrl() ?>" target="_new" class="">
                  <?= $oProfile->GetCompanyLogoUrl() ?>
                  </a>
                </div>

                <div style="display: inline-block; float: left; width: 70%;">
                <h4><a href="<?= "/company/".$oProfile->GetCompUrlName()."/".$oProfile->GetUrlName() ?>" title="" target="_new"><?= $oProfile->GetTitle(); ?></a></h4>
               	<a class="btn btn-primary btn-primary-sm" href="" target="" onclick="" title="View">View Program</a>
                </div>
            </div>

      </div>
    </div><?php
}
?>
</div>
</div>
<!--  END PROMOTED PROJECT -->
