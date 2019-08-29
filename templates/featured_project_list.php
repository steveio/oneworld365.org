<!--  begin: FEATURED PROJECT -->
<?
$oProfile = $this->Get('PROFILE_OBJECT');
$oProfile->GetImages();
$aImageDetails = $oProfile->GetImageUrlArray();
?>
<div class="span3 featured-proj-m">
  <div class="img-container">
    <div class="featured-proj-img span12">
    <? if (strlen($aImageDetails['MEDIUM']['URL']) > 1) { ?>
    <a title="<?= $oProfile->GetTitle(76) ?>" href="<?= "/company/".$oProfile->GetCompUrlName()."/".$oProfile->GetUrlName()  ?>" class="">
    <img class="img-responsive img-rounded" src="<?= $aImageDetails['MEDIUM']['URL'] ?>" alt="<?= $oProfile->GetTitle(); ?>" />
    </a>
    <span class="frame-overlay"></span>
    <? } ?>
    </div>
    <div class="overlay-img-m">
    <a title="<?= $oProfile->GetCompanyName() ?>" href="<?= $oProfile->GetCompanyProfileUrl() ?>" target="_new" class="">
    <?= $oProfile->GetCompanyLogoUrl() ?></div>
    </a>
    </div>
    <div class="details">
    <h4><a href="<?= "/company/".$oProfile->GetCompUrlName()."/".$oProfile->GetUrlName() ?>" title="" target="_new"><?= $oProfile->GetTitle(); ?></a></h4>
    </div>
</div>

<!--  END FEATURED PROJECT -->
