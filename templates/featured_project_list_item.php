        <!--  begin: FEATURED PROJECT -->
		<li class="span12">

		  	<? if (strlen($this->Get('IMG_M_01')) > 1) { ?>
			<div class="span2 image">
			  <a title="<?= $this->Get("TITLE") ?>" href="<?= $this->Get("PROFILE_URL") ?>">
				<?= $this->Get('IMG_SM_01') ?>		
			  </a>
			 </div>
			<? } ?>
   		
   			<div class="span10 title">
				<h4><a  data-original-title="<?= $this->Get("DESC_SHORT") ?>" data-placement="top" rel="tooltip"  href="<?= $this->Get("PROFILE_URL"); ?>" title="<?= $this->Get("DESC_SHORT"); ?>"><?= $this->Get("TITLE"); ?></a></h4>
			</div>
		</li>
		<!--  END FEATURED PROJECT -->