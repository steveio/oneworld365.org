<div class="row-fluid form-group">
	
<form enctype="multipart/form-data" name="" action="#" method="POST" class="form-horizontal" >
<input type="hidden" id="hostname" value="<?= $this->Get('HOSTNAME'); ?>" />


<style>
  .ui-autocomplete {
    max-height: 100px;
    overflow-y: auto;
    /* prevent horizontal scrollbar */
    overflow-x: hidden;
  }
  * html .ui-autocomplete {
    height: 100px;
  }
</style>

<div class="span4 form-control">
    <label for="destinations"></label>
    <input type="text" id="search-panel-destinations" class="form-control home-search-control-lg" value="Destination" />
</div>

<div class="span4 form-control">
    <label for="activity"></label> 
    <select id="search-panel-activity" class="form-control home-search-control-lg">
    	<option value="NULL">Activity</option>
    <?php
    	$strCurrentCategory = null; 
    	foreach ($this->Get('ACTIVITY_LIST') as $strCategory => $aActivity) {
    		if ($strCategory != $strCurrentCategory) { ?>
    			<option value="<?= $strCategory ?>"><?= $strCategory; ?></option><?
    			$strCategory = $strCategory;
    		} 
    		foreach($aActivity as $idx => $strActivity)
    		{?>
    			<option value="<?= $strActivity; ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $strActivity; ?></option><?
    		}?>
    		<?
    	} ?>
    </select>
</div>


<div class="span2 form-control">
	<input id="search-panel-btn" class="btn-primary search-panel-btn" type="submit" value=" Go " name="SEARCH" tabindex="2" />
</div>

</form>
</div>

<script src="/js/search_panel.js"></script>
