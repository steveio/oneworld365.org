<!-- start: Search Results-->
<div class="search-result row-fluid">
    <div class="span12">

    <?php
    $strQuery = '';
    if (strlen($this->Get('ARTICLE_DISPLAY_OPT_SEARCH_KEYWORD')) >= 1)
    {
        $strQuery = $this->Get('ARTICLE_DISPLAY_OPT_SEARCH_KEYWORD');
    } else {
        $strQuery = $this->Get('URI');
    }
    ?>
	<input id="search-query" class="" type="hidden" value="<?= $strQuery; ?>" name="query">
	<input id="query-origin" class="" type="hidden" value="0" name="query-origin">
	<input id="currency" class="" type="hidden" value="GBP" name="currency">
	
	<section class="search-result-panel">
	
	<div class="search-radio">

		<input id="search_projects" class="search_type" name="search_type" value="(1 OR 0)" type="hidden" />

	</div>
	
	<div id="spinner" style="margin: 40px 0px 0px 300px; display: none;">
		<img src="/images/loading_spinner.gif" alt="loading travel projects..." />
	</div>
	
	<?
	if (!$this->Get('HIDE_FILTERS')) { ?>
	<div id="refine-search-panel" class="span12">
	
	
		<div class="span12">
			<div id="facet-continent" class="facet-col span3"><?= $this->Get('FACET_CONTINENT'); ?></div>
			<div id="facet-country" class="facet-col span3"><?= $this->Get('FACET_COUNTRY'); ?></div> 
			<div id="facet-activity" class="facet-col span3"><?= $this->Get('FACET_ACTIVITY'); ?></div>
			<div id="text-fltr-div" class="facet-col span3"></div>
			
			<div id="" class="facet-col span3">&nbsp;</div>
		</div>
		
		<div class="span12">
			<div id="facet-duration" class="facet-col span3"></div>
			<div id="facet-price" class="facet-col span3"></div>
			<div id="facet-species" class="facet-col span3"></div>
			<div id="facet-habitats" class="facet-col span3"></div>
			<div id="" class="facet-col span3">&nbsp;</div>
		</div>

		<div class="span12">
			<input id="do-search" type="button" class="btn-success btn-small" value="update" />
			<input id="clear-filters" type="button" class="btn-success btn-small" value="clear filters" />		
		</div>
		
		
	</div>
	<?php } ?>
	</section>
		
    <section class="profiles">
    	<div id="">
    		<h2><?= $this->Get('ARTICLE_DISPLAY_OPT_PTITLE'); ?></h2>
    		<p class='lead'><?= $this->Get('ARTICLE_DISPLAY_OPT_PINTRO'); ?></p>
    	</div>
	    <div id="result-hdr"></div>
		<div id="profiles"></div>
		<div id="pager" class="span12 pagination pagination-large pagination-centered page-links"></div>
	</section>

		
	

	<script id="pTWide" type="text/template">
        <div class="span4 featured-proj">
		<div class="img-container">
			<div class="featured-proj-img span12">
			<% if (image_url_large.length > 1) { %>
      			<a title="<%= title %>" href="<%= profile_url %>" class="">
    			<img class="img-responsive img-rounded" src="<%= image_url_large %>" alt="<%= title %>" />		
      			</a>
				<span class="frame-overlay"></span>
			<% } else if (image_url_medium.length > 1) { %>
      			<a title="<%= title %>" href="<%= profile_url %>" class="">
    			<img class="img-responsive img-rounded" src="<%= image_url_medium %>" alt="<%= title %>" />		
      			</a>
				<span class="frame-overlay"></span>
			<% } %>
			</div>
			<div class="overlay-img">
				<a title="<%= company_name %>" href="<%= company_profile_url %>" target="_new" class="">
				<%= company_logo_url %></div>
			</a>
		</div>
			<div class="details">
            	<h3><a href="<%= profile_url %>" title="<%= desc_short %>" target="_new"><%= title %></a></h3>
			</div>
    	</div>
	</script>

	<script id="cTWide" type="text/template">
        <div class="span3 featured-proj">

        <% if (profile_type == 1) { %>
            <div class="img-container-4col">
			<div class="featured-proj-img-4col span12">
			<% if (image_url_medium.length > 1) { %>
      			<a title="<%= title %>" href="<%= profile_url %>" class="">
    			<img class="img-responsive img-rounded" src="<%= image_url_medium %>" alt="<%= title %>" />		
      			</a>
				<span class="frame-overlay"></span>
			<% } %>
			</div>
			<div class="overlay-img-4col">
				<a title="<%= company_name %>" href="<%= company_profile_url %>" target="_new" class="">
				<%= company_logo_url %>
                </a>
            </div>
		    </div>
			<div class="details">
                <div class="span12 proj-title">
                   <div class="span12 title">
            	   <h3><a href="<%= profile_url %>" title="<%= title %>" target="_new"><%= title %></a></h3>
                   </div>
                   <% if (review_rating >= 1) { %>
                   <div id="rateYo-<%= id %>" class="span12 rating"></div>
                   <% } %>
                </div>
                <div class="span12">
                <ul class="featured-proj-details">
                    <% if (location !== null && location.length > 1) { %>
                    <li><%= location %></li>
                    <% } %>
                    <% if (duration_from !== null && duration_from.length > 1) { %>
                    <li><%= duration_from %> - <%= duration_to %></li>
                    <% } %>
                    <% if (price_from !== null && price_from.length > 1) { %>
                    <li><%= price_from %> - <%= price_to %> <%= currency_label %></li>
                    <% } %>
                </ul>
                </div>
			</div>
        <% } else { %>

                <% if (logo_url.length > 1) { %>
                   <div class="pull-right logo">
                      <a title="<%= title %>" href="<%= profile_url %>" class="thumbnail">
                      <img src="<%= logo_url %>" alt="<%= title %>" />
                      </a>
                   </div>
                <% } else if (image_url_medium.length > 1) {  %>

      			   <a title="<%= title %>" href="<%= profile_url %>" class="thumbnail">
    			   <img class="img-responsive img-rounded" src="<%= image_url_medium %>" alt="<%= title %>" />		
      			   </a>
				   <span class="frame-overlay"></span>
  			    <% } %>

			     <div class="details">
	             <h4><a href="<%= profile_url %>" title="<%= desc_short %>"><%= title %></a></h4>

				 <% if (image_url_medium.length < 1) { %>
					<p><%= desc_short %>
				 <% } else { %>
                   <p><%= desc_short_160 %>
                 <% } %>

			</div>
        <% } %>
    	</div>
	</script>
	
	<script id="oTWide" type="text/template">
        <div class="span6 featured-org">

			<% if (logo_url.length > 1) { %>
				<div class="pull-right logo">
      			<a title="<%= title %>" href="<%= profile_url %>" class="thumbnail">
         		<img src="<%= logo_url %>" alt="<%= title %>" />		
      			</a>
				</div>
			<% } %>

			<% if (image_url_medium.length > 1) { %>
				<div class="pull-right photo">
				<a title="<%= title %>" href="<%= profile_url %>" class="thumbnail">
					<img class="img-responsive img-rounded" src="<%= image_url_small %>" alt="<%= title %>" />
				</a>
				</div>
			<% } %>

			<div class="details">
 				<h4><a href="<%= profile_url %>" title="<%= title %>"><%= title %></a></h4>
				<p><%= desc_short %></p>
			</div>
		</div>
		</script>
	
        
        <script src="/js/underscore-min.js"></script>
        <script src="/js/backbone-min.js"></script>
        <script src="/js/app.js?&version=3.4.<?php print(rand(0,10000)); ?>"></script>

    </div>
</div>
<!-- end: Search Results-->
        
