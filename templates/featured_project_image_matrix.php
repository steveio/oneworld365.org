<? if (strlen($this->Get('IMG_SM_01')) > 1) { ?>
<div class="pull-left image">
  <a title="<?= strip_tags($this->Get("TITLE")) ?>" href="<?= $this->Get("PROFILE_URL") ?>">
	<?= $this->Get('IMG_SM_01') ?>		
	
  </a>
 </div>
<? } ?>