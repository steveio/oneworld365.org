
/*
 * app.js  
 * JS controller logic for search result pages
 * 
 */


String.prototype.trunc =
     function(n,useWordBoundary){
         var toLong = this.length>n,
             s_ = toLong ? this.substr(0,n-1) : this;
         s_ = useWordBoundary && toLong ? s_.substr(0,s_.lastIndexOf(' ')) : s_;
         return  toLong ? s_ + '&hellip;' : s_;
      };

$(document).ready(function(){

	var facetTypes = ['category','country','continent','activity','price','duration','species','habitats'];
	
	var profileData;
	var rf; // refine search panel visibility
	
	var getProfileData = function(url,fqSet,start,rows) {
		
		var fq = '';
		
		if (typeof start == 'undefined') {
			var start = 0;
		}

		fq = fq+'&start='+start;

		if (typeof rows == 'undefined') {
			var rows = 24;
		}

		fq = fq+'&rows='+rows;
		
		// is this a project (1), org (0) or article (2) search?
		var profileType = $('input[name=search_type]').val();
		fq = fq+'&fq0=profile_type:'+profileType;

		if (profileType == 1) {
			$('#result-title-org').hide();
			$('#result-title-placement').show();
		} else {
			$('#result-title-org').show();
			$('#result-title-placement').hide();			
		}
		
		// query origin
		var o = $('#query-origin').val();
		fq = fq+'&o='+o;
		
		
		// append filter queries
		if (typeof fqSet !== 'undefined' && fqSet.length > 0) {
			for(i=0;i<fqSet.length;i++) {
				fquery = fqSet[i];
				// fq0 reserved for profile_type
				fq = fq+'&fq'+(i+1)+'='+fquery.field+':'+fquery.facet;
			}
		}

		// pass visibility of refine search panel
		fq = fq+'&rf='+rf;
		var apiUri = "http://api.oneworld365.org/search";
	
		$.ajax({
		     url:apiUri+url+fq,
		     dataType: 'jsonp', // Notice! JSONP <-- P (lowercase)
		     success:function(json){
		    	 if (json.status == 1) {
		    	 	process(json);
		     	 } else {
		     		 processError();
		     	 }
		     },
		     error:function(){
		    	processError();
		     },
		});
	};

	function processError() {
 		$('#spinner').hide();
 		$('#result-hdr').html('<h4 style="color: red;">Sorry, an error occured and it was not possible to run your search.  Please try again later or email admin@oneworld365.org.</h4>');
	}
	
	function doSearch(fqSet,start,rows) {

		var url = $('#search-query').val();
		
		if ($('#text-fltr').val() != undefined && $('#text-fltr').val().length > 1) {
			url = url +'/'+ $('#text-fltr').val();
		}
		
		if (url.length == 0) return false;
		
		if (url.substring(0, 2) == "q=") {
			url = "/?"+url;
		} else if (url.indexOf("/") !== 0) {
			url = "/"+url+'?';
		} else {
			url = url+'?';	
		}
		
		
		rf = $('#refine-search-panel').is(':visible') ? 1 : 0;
		$('#refine-search-panel').hide();
		
		$('#spinner').show();

		$('#result-hdr').html('');
		$('#profiles').html('');

		$('div[id^=facet-]').each(function () {
			$('#'+this.id).html('');
			$('#'+this.id).hide();
		});
		$('#facets-all').hide();
		
		getProfileData(url,fqSet,start,rows);
	
	}

	function refineSearch(el) {

		var fqSet = [];		
		var fqSet = getFiltersFromSelect();
		
		doSearch(fqSet);
	}

	
	function getFacetQuery(el) {
		var facet = '"'+el.name+'"';
		var a = el.id.split('_');
		var field = a[0];
	
		fq=new Object();
		fq.field=field;
		fq.facet=facet;	

		return fq;
	
	}

	
	function getFiltersFromCheckboxes() {

		var fqSet = [];

		$.each( facetTypes, function( idx, facetType ) {
			$('input:checkbox[id^='+facetType+'_]:checked').each(function () {
				$(this).prop("checked");
				fqSet.push(getFacetQuery(this));
			});
		});

		return fqSet; 
	}
	
	function getFiltersFromSelect() {

		var fqSet = [];

		$.each( facetTypes, function( idx, facetType ) {

			var el = $('#facet_'+facetType).val();

			if (el != undefined && el != 'NULL') {
				fq=new Object();
				fq.field=facetType;
				fq.facet=el;	
				fqSet.push(fq);
			}
			
		});

		return fqSet; 
	}	
	function process(json){
		
		if (json.total_results == 0) {
		
			if (json.profileType == "1") // rerun a company profile search
			{
				$('#spinner').hide();
				$('#search_orgs').prop('checked',true);
				doSearch();
				return false;
			} else {
		 		$('#spinner').hide();
		 		$('#result-hdr').append('<h4 style="color: red;">0 results found</h4>');
		 		$('#refine-search-panel').show();
		 		return false;
			}
		}

		
		processProfileData(json.data.profile,json.profileType);

		$.each(json.data.profile, function(idx,profile) {
			if (!profile.review_rating !== null)
			{
				$("#rateYo-"+profile.id).rateYo({
					 rating: profile.review_rating,
					 starWidth: "18px",
					 fullStar: true,
					 readOnly: true
				});
			}
		});
		
		if (typeof(json.data.facet) != "undefined") {

			$.each(json.data.facet, function(name,facet) {
				
				switch(facet.name) {
				  	case 'country' :
				  		facet.title = 'Country';
				  		facet.divName = 'facet-country';
					  	break;
				  	case 'continent' :
				  		facet.title = 'Continent';
				  		facet.divName = 'facet-continent';
					  	break;
				  	case 'activity' :
				  		facet.title = 'Activity';
				  		facet.divName = 'facet-activity';
					  	break;
				  	case 'category' :
				  		facet.title = 'Category';
				  		facet.divName = 'facet-category';
					  	break;
				  	case 'price' :
				  		// (<a name=\'currency_gbp\'>GBP &pound;</a> | <a name=\'currency_usd\'>USD &dollar;</a> | <a name=\'currency_eur\'>EUR &euro;</a>)
				  		facet.title = 'Price From';
				  		facet.divName = 'facet-price';
				  		facet = mapPriceLabels(facet);
					  	break;
				  	case 'duration' :
				  		facet.title = 'Duration';
				  		facet.divName = 'facet-duration';
				  		facet = mapDurationLabels(facet);
					  	break;
				  	case 'species' :
				  		facet.title = 'Species';
				  		facet.divName = 'facet-species';
					  	break;
				  	case 'habitats' :
				  		facet.title = 'Habitat';
				  		facet.divName = 'facet-habitats';
					  	break;
				}
				
				processFacetData(facet);

			}); // end each facet

			addTextFilter();

			// paginator
			
			if (json.data.hasPager == true) {

				$('#pager').html(json.data.pagerHtml);
				$('.page-links').one('click', 'a',processPagerClick);
								
			} else {

				$('#pager').html('');
				
			}

		}

                $('#result-hdr').append('<h4>Found '+json.total_results+' Results ('+json.pageNum+' of '+json.totalPages+')</h4>');

                if (json.rf == 1) {
                     $('#refine-search-panel').show();
                }

                $('#spinner').hide();

	}

	function mapDurationLabels(facetData) {
		
		var map = [];
		map['duration_0_1'] = '< 1 week';
		map['duration_1_2'] = '1 week+';
		map['duration_2_4'] = '2 weeks+';
		map['duration_4_8'] = '1 month+';
		map['duration_8_24'] = '2 months+';
		map['duration_24_*'] = '6 months+';
		map['duration_all'] = 'all';
		
		$.each( facetData.data, function( id,facet ) {
			facet.label = map[facet.facet];
		});
		return facetData;
	}

	function mapPriceLabels(facetData) {
		
		var csymbol_usd = "&dollar;";
		var csymbol_gbp = "&pound;";
		var csymbol_eur = "&euro;";
		
		var map = [];
		map['price_0_250'] = '< '+csymbol_gbp+'350  /  '+csymbol_usd+'250';
		map['price_250'] = csymbol_gbp+'350  / '+csymbol_usd+'250';
		map['price_500'] = csymbol_gbp+'750  / '+csymbol_usd+'500';
		map['price_750'] = csymbol_gbp+'1125  / '+csymbol_usd+'750';
		map['price_1000'] = csymbol_gbp+'1500  / '+csymbol_usd+'1000';
		map['price_2000'] = csymbol_gbp+'3000  / '+csymbol_usd+'2000';
		map['price_all'] = 'all';

		$.each( facetData.data, function( id,facet ) {
			facet.label = map[facet.facet];
		});
		return facetData;
	}

	function processPagerClick(e) {

  	  if(e.handled !== true)
	  {

  		e.preventDefault();
		var a = this.id.split('_');
		var start = a[1];
		
		var fqSet = [];		
		var fqSet = getFiltersFromSelect();

		$('#pager').html('');

		e.handled = true;

		doSearch(fqSet,start);

		$('html, body').animate({scrollTop:0}, 'slow');
	 }  
	
	
	}
	
	function addTextFilter() {		
		if ($('#text-flt-div').length && $('#text-fltr-div').html().length < 1) {
			$('#text-fltr-div').append('<div class="facet-col-inner">Keyword:<br /> <input type="text" id="text-fltr" name="text-fltr" value="" maxlength="64" class="facet" style="width: 180px;" /></div>');
		}
	}
	
	function processFacetData(facetData) {

		$("#"+facetData.divName).show();
		
		var list = $("#"+facetData.divName).append('<div class="facet-col-inner">'+facetData.title+':<select id="facet_'+facetData.name+'" class="facet"></select></div>').find('select');
		//var lmore = $("#"+facetData.divName+"-more").append('<div class="facet-col-inner"><ul class="unstyled"></ul></div>').find('ul');

		var i = 0;
		var dd = 10;
		var el;
		$.each( facetData.data, function( id,facet ) {
			var checked = (facet.checked) ? "checked" : "";
			el = list;
			if (i == 0) {
				el.append('<option class="facet" value="NULL"></option>');
			}
			if (facet.label != undefined) {
				var label = facet.label;
			} else {
				var label = facet.facet;	
			}
			//if (facetData.name != 'price') label = label,true); 
			
			var selected = (facet.checked) ? "selected" : "";
			el.append('<option class="facet" value="'+facet.facet+'" '+selected+'> '+label+' [ '+facet.count+' ]</option>');
			i++;
		});


	    $(".facet").live('change', function(event){
	    	  if(event.handled !== true)
	    	  {
				refineSearch(this);
					    	    
	    	    event.handled = true;
	    	  }
	    	  return false;
    	});		
			
	}
	
	function processProfileData(profileData,profileType) {

		// profile data -----------------------------------------
		var ProfileSummary = Backbone.Model.extend({
		    defaults: {
		        image_url_small: "/images/oneworld365_logo_small.png"
		    }
		});

		var ProfileList = Backbone.Collection.extend({
		    model: ProfileSummary
		});

		var profileTemplate = '';
		switch (profileType) {
			case '1' :
				profileTemplate = 'pTWide';
				break;
			case '0' :
				profileTemplate = 'oTWide';
				break;
			case '2' :
				profileTemplate = 'aTWide';
				break;
			case '(1 OR 0)' :
				profileTemplate = 'cTWide';
				break;		

		}
		

		var ProfileSummaryView = Backbone.View.extend({
		    tagName: "search-result",
		    className: "profile-container",
		    template: $("#"+profileTemplate).html(),
		 
		    render: function () {
		        var tmpl = _.template(this.template);
		 
		        this.$el.html(tmpl(this.model.toJSON()));
		        return this;
		    }
		});		

		var ProfileListView = Backbone.View.extend({
		    el: $("#profiles"),
		 
		    initialize: function () {
		        this.collection = new ProfileList(profileData);
		        this.render();
		    },
		 
		    render: function () {
		        var that = this;
		        _.each(this.collection.models, function (item) {
		            that.renderProfile(item);
		        }, this);
		    },
		 
		    renderProfile: function (item) {
		        var profileSummaryView = new ProfileSummaryView({
		            model: item
		        });
		        this.$el.append(profileSummaryView.render().el);
		    }
		});

	    var profileList = new ProfileListView();		
	
	}

	function go(url) {
        window.location = url;
	}
		

	$('#facet-clear').click(function(e) {
		e.preventDefault();
	
		$.each( facetTypes, function( idx, facetType ) {
			$('input:checkbox[id^='+facetType+'_]:checked').each(function () {
				$(this).prop("checked","");
			});
		});

		var fqSet = [];		
		doSearch(fqSet);
		
	});

	
	$('#facet-more').click(function(e) {
		e.preventDefault();
		$('#facets-all').toggle();	
	});
	
	$('#search-btn').click(function() {
		doSearch();
	});

	$('#clear-filters').click(function(e) {
		e.preventDefault();
		$('#text-fltr').val('');
		var fqSet = [];
		doSearch(fqSet);

	});
	
	

	$('input[name=search_type]').change(function() {
		var fqSet = [];		
		var fqSet = getFiltersFromSelect();
					
		doSearch(fqSet);
	
	});


	var fqSet = [];		
	var fqSet = getFiltersFromSelect();
				
	doSearch(fqSet);

});
