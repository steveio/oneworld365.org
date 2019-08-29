<section id="review" class="">	
	<ul class="nav nav-tabs">
	  <li class="<?= ($this->Get('HAS_REVIEW')) ? "active" : ""; ?>"><a id="review-display-lnk" data-toggle="tab" href="#review-display">Comments</a></li>
	  <li class="<?= (!$this->Get('HAS_REVIEW')) ? "active" : ""; ?>"><a id="review-add-lnk" data-toggle="tab" href="#review-add">Leave a Reply</a></li>
	</ul>
	<div class="tab-content">
		<div id="review-display" class="tab-pane fade <?= ($this->Get('HAS_REVIEW')) ? " in active" : ""; ?>">
			<div class="row-fluid" style="margin-bottom: 20px;"><?php
				$iLimit = 5;
				$aReview = is_array($this->Get('REVIEWS')) ? $this->Get('REVIEWS') : array();
				for($i=0;$i<5;$i++) { 
				    $oReview = isset($aReview[$i]) ? $aReview[$i] : null; 
				    if (!is_object($oReview)) continue; ?> 
					<div class="col span12" style="margin-bottom: 20px;">
						<div class="span12">
							<p><?= nl2br(html_entity_decode($oReview->GetReview())); ?></p>
						</div>
						<div class="span12" style="font-size: 0.8em;">
							By: <?= $oReview->GetName(); ?><br /> 
						</div>
					</div><?php 
				} 
				if (count($aReview) == 0) { ?>
					<p>There a no comments, click 'Leave a Reply' to submit one </p>
				<?php 
				} ?>
			</div><?php
			if (count($aReview) >=5)
				{ ?>
    		<div class="span12">
    			<h3><a href="#" id="review-viewall">>> View All Comments</a></h3>
    		</div>
    		<div id="review-more" class="row-fluid hide" style="margin-bottom: 20px;"><?php
    			for($i=5;$i<count($aReview);$i++) { 
    			    $oReview = isset($aReview[$i]) ? $aReview[$i] : null; 
    			    if (!is_object($oReview)) continue; ?> 
    				<div class="col span12">
    					<div class="span12">
    						<p><?= nl2br(html_entity_decode($oReview->GetReview())); ?></p>
    					</div>
    					<div class="span12" style="font-size: 0.8em;">
    						By: <?= $oReview->GetName(); ?><br /> 
    					</div>
    				</div><?php 
    			 } ?>
    		</div><?php
    		} ?>			

            <script>
            	$(document).ready(function(){ 
            	
            		<?php
            		      $aReview = is_array($this->Get('REVIEWS')) ? $this->Get('REVIEWS') : array();
            			foreach($aReview as $oReview) { ?>
            				$("#rateYo-<?= $oReview->GetId(); ?>").rateYo({
            					 rating: <?= $oReview->GetRating(); ?>,
            					 fullStar: true,
            					 readOnly: true
            				}); <?php 
               			}
            			if ($this->Get('HASREVIEWRATING')) { ?>
            			    $("#review-overallrating").rateYo({
            				     rating: <?= $this->Get('REVIEWRATING') ?>,
            					 fullStar: true,
            					 readOnly: true
            				}); <?php 
            			} ?>
            			$('#review-viewall').click(function(e) {
            				   e.preventDefault();
            			       $('#review-more').show();
            			       return false;
            			}); 
            			$('#review-add-lnk').click(function(e) {
            				$('#review-display').hide();
            				$('#review-add').show();
            				$('#review-display').addClass('in');
            				$('#review-display').removeClass('fade');
            			});
            			$('#review-display-lnk').click(function(e) {
            				$('#review-add').hide();
            				$('#review-add').removeClass('fade');
            				$('#review-display').show();
            			});
            	
            	});
            </script>
		</div>
		
    	 <div id="review-add" class="tab-pane fade <?= (!$this->Get('HAS_REVIEW')) ? " in active" : ""; ?>" style="display: none;">
    
    		<p>Provide feedback, share your experience and submit your comment.</p>
    		
    		<div id="review-error" class="span12 text-error"></div>
    		<div id="review-msg" class="span12 text-success"></div>
    
    		<div id="review-add-form">
    			<form enctype="multipart/form-data" id="review-form" name="review-form" action="#" method="POST" class="form">
    		
    			<input type="hidden" id="review-link-id" name="review-link_id" value="<?= $this->Get('LINK_ID'); ?>" class="form-control" />
    			<input type="hidden" id="review-link-to" name="review-link_to" value="<?= $this->Get('LINK_TO'); ?>" class="form-control" />
	 			<input type="hidden" id="review-type" name="review-type" value="comment" />
	
    		  	<div class="form-group span3">
    				<label for="review-name">Name:</label>
    				<input type="text" id="review-name" name="review-name"  maxlength="45" class="form-control" />
    			</div>
    		
    		  	<div class="form-group span3">
    				<label for="review-email">Email:</label>
    				<input type="text" id="review-email" name="review-email"  maxlength="50" class="form-control" />
    			</div>
    		
    		  	<div class="form-group span12">
    				<label for="review-review">Comment:</label>
    				<textarea id="review-review" name="review-review" class="span6" style="height: 160px;" /><?= stripslashes($_POST['review-review']); ?></textarea>
    			</div>

    			<div class="form-group span12" style="margin-top: 20px;">
    				<input id="review-btn" class="btn-primary" type="submit" value=" Submit " name="review-submit" tabindex="2" />
    			</div>
    			
    			</form>
    		</div>	
		</div>
	
	</div>
</section>
