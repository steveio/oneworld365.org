<?php 

require_once("./classes/BalancedDistributor.php");


// slider includes
$oCssInclude = new CssInclude();
$oCssInclude->SetHref('/css/layerslider.css?&r=1');
$oCssInclude->SetMedia('screen');

$oJsInclude = new JsInclude();
$oJsInclude->SetSrc("/js/layerslider.kreaturamedia.jquery.js");

$sCSSInclude = $oCssInclude->Render();
$sJSInclude = $oJsInclude->Render();


// sponsored company logos in footer
$oCompany = new Company($db);
$aSponsoredCompanyProfile = $oCompany->GetHomepageCompHTML(30,80);

// intro article
$oPartnerArticle = new Article;
$oPartnerArticle->Get($_CONFIG['site_id'],'/homepage-partners-awards-featured');


// homepage news articles
$oNews = new Article();
$oNews->SetFetchMode(FETCHMODE__SUMMARY);
$oNews->SetAttachedArticleFetchLimit(8);
$oNews->Get($_CONFIG['site_id'],"/news");

$oBlogArticle = new Article();
$oBlogArticle->SetFetchMode(FETCHMODE__SUMMARY);
$oBlogArticle->SetAttachedArticleFetchLimit(12);
$oBlogArticle->Get($_CONFIG['site_id'],"/blog");


$oHomepageArticle = new Article;
$oHomepageArticle->SetFetchMode(FETCHMODE__SUMMARY);
$oHomepageArticle->SetAttachedArticleFetchLimit(10);
$oHomepageArticle->Get($_CONFIG['site_id'],"/homepage-intro",1);

require_once(ROOT_PATH."/classes/SolrSearch.php");
require_once(ROOT_PATH."/classes/SolrPlacementSearch.php");
require_once(ROOT_PATH."/classes/SolrCompanySearch.php");
require_once(ROOT_PATH."/classes/SolrQuery.php");
require_once(ROOT_PATH."/classes/SolrSearchPanel.php");
require_once(ROOT_PATH."/classes/SolrSearchPanelSearch.php");
require_once(ROOT_PATH."/classes/SolrMoreLikeSearch.php");


$oSolrSearchPanel = new SolrSearchPanel;

// clear any previous search
SolrSearchPanelSearch::clearFromSession();

$fq = array();
$fq['profile_type'] = "1";
//$fq['category_id'] = "7";
$oSolrSearchPanel->setFilterQuery($fq);

$aFacetField = array();
$aFacetField[] = array("country" => "country");
$aFacetField[] = array("continent" => "continent");
$aFacetField[] = array("activity" => "activity");
$oSolrSearchPanel->setFacetField($aFacetField);
$oSolrSearchPanel->setup($_CONFIG['site_id']);

$oSearchPanel = new Template;
$oSearchPanel->Set('HOSTNAME',$_CONFIG['url']);

$oSearchPanel->Set('ACTIVITY_LIST',Activity::getActivitySelectList());


    

require("./header_html.php");

?>

<?  if ($display_404) { ?>
	<div class="row">
	<div class="search-page-notify span12">
	
		<div class="alert alert-error">
		<h2>404 Not Found - Sorry the requested page was not found.</h2>
		</div>
	</div>
	</div>
<?php } ?>

	<div class="homepage row">

        <section id="" class="span12">

        <div class="intro row-fluid">

			<section class="news">
    			<div class="span12 container">
    				<div class="banner-img"><img id="" class="img-responsive img-rounded" src="/images/gapyearslider/v2/gap_year_banner.jpg" width="100%" alt='' /></div>
        	        <div class="overlay">
        	            <div class="search-panel">
     	                <h1><?= $oHomepageArticle->GetTitle(); ?></h1>
     	                <p><?= $oHomepageArticle->GetDescShort(); ?></p>
        				<?
        					$oSearchPanel->LoadTemplate("./search_panel.php");
        					print $oSearchPanel->Render(); 
        				?>
                    	</div>
                    </div>
                </div>
            </section>

	        <div class="span12" style="margin-top: 20px;">
        	<div class="pull-right sharethis-inline-share-buttons"></div>
	        </div>  

            <section class="news">
	            <div class="span12 row-fluid">
	            	<?php 
	            	$aArticle = $oNews->GetArticleCollection()->GetArticles();
	            	for($i = 0; $i<4; $i++) {
						$oArticle = array_shift($aArticle);
						if (!is_object($oArticle)) continue;
						?>
		                <div class="featured news-item span3">
							<div>
	                        <div class="">
	                        <?php 
							if (is_object($oArticle->GetImage(0))) {
								print "<a href='". $oArticle->GetUrl()."' title='".$oArticle->GetTitle()."' class'thumbnail'>";
								print $oArticle->GetImage(0)->GetHtml("_lf",$oArticle->GetTitle());
								print "</a>";
							} 
							?>
	                        </div>
	                        <h3><a href="<?= $oArticle->GetUrl() ?>" title="<?= $oArticle->GetTitle(); ?>"><?= $oArticle->GetTitle(); ?></a></h3>
	                        <p><?= $oArticle->GetDescShort(120); ?></p>
							</div>
		                </div>
	           		<?php 
					}
	            	?>
	            </div>
	        </section>
		            
		    <section class="news">
                <!-- BEGIN blog articles -->
                <div class="row-fluid featured"><?php 
                    if (is_array($oBlogArticle->GetArticleCollection()->Get()))
                    {
                        $aArticle = $oBlogArticle->GetArticleCollection()->Get();
                    } ?>

					<div class="span8"><?
    		        $limit = 5;
    		        for ($i=0;$i<$limit;$i++) {
                        $oArticle = array_shift($aArticle);
			if (!is_object($oArticle)) continue;
                        $oArticle->SetAttachedImages(); 
                        $css_class = "span12 row-fluid";
                        ?>
                        <!-- BEGIN article -->
                        <div class="<?= $css_class; ?>" style="">                                
                                <? if (is_object($oArticle->GetImage(0))) { ?>
                                <div class="img-responsive img-rounded">
                                  <a title="<?= $oArticle->GetTitle(); ?>" href="<?= $oArticle->GetUrl(); ?>">
                                        <?= $oArticle->GetImage(0)->GetHtml("",$oArticle->GetTitle()); ?>
                                  </a>
                                 </div><? } ?>
        
                                <div class="pad-b"></div>
                                <h2><a href="<?= $oArticle->GetUrl(); ?>" title="<?= $oArticle->GetTitle(); ?>"><?= $oArticle->GetTitle(); ?></a></h2>
                                <p><?= $oArticle->GetDescShort(160); ?></p>
                        </div>
                        <!-- END article --><?php
                    } ?>
                    </div>


				   <div class="span3 pull-right"><?
    		        $limit = 7;
    		        for ($i=0;$i<$limit;$i++) { 
                        $oArticle = array_shift($aArticle);
			if (!is_object($oArticle)) continue;
                        $oArticle->SetAttachedImages(); 
                        $css_class = "row-fluid span12";
                                //if ($i >= 1 && $i <= 3) $css_class = "span8";
                        ?>
                        <!-- BEGIN article -->
                        <div class="<?= $css_class; ?>" style="margin-bottom: 20px;">                                
                                <? if (is_object($oArticle->GetImage(0))) { ?>
                                <div class="img-responsive img-rounded">
                                  <a title="<?= $oArticle->GetTitle(); ?>" href="<?= $oArticle->GetUrl(); ?>">
                                        <?= $oArticle->GetImage(0)->GetHtml("",$oArticle->GetTitle()); ?>
                                  </a>
                                 </div><? } ?>
        
                                <div class="pad-b"></div>
                                <h2><a href="<?= $oArticle->GetUrl(); ?>" title="<?= $oArticle->GetTitle(); ?>"><?= $oArticle->GetTitle(); ?></a></h2>
                                <p><?= $oArticle->GetDescShort(160); ?></p>
                        </div>
                        <!-- END article --><?php
                    } ?>
                    </div>

                </div>
                <!--  END display blog articles -->

			</section>

            <section class="row-fluid news">
		<div class="span12">
		<?= $oHomepageArticle->GetDescFull(); ?>
		</div>

                <!-- 
		<?
                $aArticle = $oHomepageArticle->GetArticleCollection()->Get();
                shuffle($aArticle);  ?>

                    <div class="span12 ">    
                	<div class="row-fluid"><?
                        $limit = 6;                
                        for ($i=0;$i<$limit;$i++) {
                                if (is_object($aArticle[$i])) {
                                        $aArticle[$i]->LoadTemplate("article_homepage.php");
                                        print $aArticle[$i]->Render();
                                }
                        } ?>
                    </div>
                    </div>

                -->
	        </section>

            
            <hr />

        <section class="">

	<!--
	       <div class="row-fluid">
			<h3>Join our Social Network Pages</h3>
			<div class="span4">
				<div id="fb-root">&nbsp;</div>
							<script>(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>
				<div class="fb-like-box" data-colorscheme="light" data-header="true" data-height="330" data-href="http://www.facebook.com/oneworld365" data-show-border="true" data-show-faces="true" data-stream="false" data-width="250">&nbsp;</div>
			</div>
			<div class="span4">
				<div class="g-page" data-href="//plus.google.com/109004673989086435172" data-rel="publisher" data-width="250">&nbsp;</div>
				<script type="text/javascript">
				  (function() {
				    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
				    po.src = 'https://apis.google.com/js/plusone.js';
				    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
				  })();
				 </script>
			  </div>
			  <div class="span4">
				<a class="twitter-timeline" data-widget-id="263362466807947264" height="330" href="https://twitter.com/oneworld365" width="250">Tweets by @oneworld365</a> <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></td>
			  </div>
			</div>
		</section>
            
            
            <hr />

            <div class="row-fluid">
            	<div class="span12 sponsored-brands">
            	<h3>Top Rated Companies:</h3>
            	<ul class="unstyled">
            	<?php 
            	foreach ($aSponsoredCompanyProfile as $oProfile) {
					if (is_object($oProfile->GetImage(0,LOGO_IMAGE))) {
					?>
					<li><div class='span12' style='padding: 4px; height: 90px;'><a data-original-title="<?= $oProfile->GetTitle() ?>" data-placement="top" rel="tooltip" href=" <?= $oProfile->GetProfileUrl() ?>" title="<?= $oProfile->GetTitle() ?> - <?= strip_tags($oProfile->GetDescShort()) ?>"><?= $oProfile->GetImage(0,LOGO_IMAGE)->GetHtml("_sm",$oProfile->GetTitle(),'',FALSE); ?></a><div></li>
				<?php
					} 
				}
            	?>
            	</ul>
		</div>

		<div class="span12 sponsored-brands">
		<h3><?= $oPartnerArticle->GetTitle(); ?></h3>
		<?= $oPartnerArticle->GetDescFull(); ?>
            	</div>
            </div>
	-->	

        </div>

        </section>
        <!-- end: Page section -->

	        
    </div>

<?php 
require("./footer.php");
?>
