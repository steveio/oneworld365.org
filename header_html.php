<!DOCTYPE html>
<head>
    <title><?= (strlen($_REQUEST['page_title']) >1) ? $_REQUEST['page_title'] : $_CONFIG['page_title'] ; ?></title>

    <meta name="verify-v1" content="<?= $_CONFIG['meta-tag']; ?>" />
    <meta name="description" content="<?= (strlen($_REQUEST['page_meta_description']) >1) ? $_REQUEST['page_meta_description'] : $_CONFIG['page_description'] ; ?>" />
    <meta name="keywords" content="<?= strip_tags((strlen($_REQUEST['page_keywords']) >1) ? $_REQUEST['page_keywords'] : $_CONFIG['page_keywords']) ; ?>" />

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

   <meta property="og:title" content="<?= $_REQUEST['page_title']; ?>" />
   <meta property="og:url" content="<?= $_REQUEST['page_url'] ?>" />
   <meta property="og:description" content="<?= $_REQUEST['page_meta_description'] ?>" />
   <meta property="og:image" content="<?= $_REQUEST['page_imageUrl'] ?>" />

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    <link rel="stylesheet" type="text/css" href="/css/bootstrap.css?&r=true"/>
    <link rel="stylesheet" type="text/css" href="/css/style.css?&r=<?= rand(100,1000); ?>"/>
    <link rel="stylesheet" type="text/css" href="/css/font-awesome.min.css"/>
    <link href="/js/featherlight-1.3.4/release/featherlight.min.css" type="text/css" rel="stylesheet" />
    <link href="/js/autocomplete/jquery-ui.min.css" type="text/css" rel="stylesheet" />
    <link rel="stylesheet" href="/js/rateYo/min/jquery.rateyo.min.css"/>
    <?= $sCSSInclude;?>

    <script type="text/javascript" src="/js/jquery.min.js?&r=true"></script>
    <script type="text/javascript" src="/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/jquery.easing.1.3.js"></script>
    <script type="text/javascript" src="/js/sftouchscreen.js"></script>
    <script type="text/javascript" src="/js/jquery.elastislide.js"></script>
    <script type="text/javascript" src="/js/smoothscroll.js"></script>
    <script type="text/javascript" src="/js/featherlight-1.3.4/release/featherlight.min.js"></script>
    <script type="text/javascript" src="/js/jquery.ui.totop.js"></script>
    <script type="text/javascript" src="/js/main.js?&r=<?= rand(100,1000); ?>"></script>
    <script type="text/javascript" src="/js/autocomplete/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/js/rateYo/min/jquery.rateyo.min.js"></script>
    <script type="text/javascript" src="/js/review.js?&r=<?= rand(100,1000); ?>"></script>
    <script type='text/javascript' src='//platform-api.sharethis.com/js/sharethis.js#property=5bca02abddd6040011604f41&product=inline-share-buttons' async='async'></script>    
    <?= $sJSInclude;?>
    
    <!--[if lt IE 9]>
    <script type="text/javascript" src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    
    <?php if (!OFFLINE) { ?>
	<script type="text/javascript">
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
		document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
		</script>
		<script type="text/javascript">
		try {
			var pageTracker = _gat._getTracker("<?= $_CONFIG['google_analytics_id'] ?>");
			pageTracker._trackPageview();
		} catch(err) {}
		try {
			var pageTracker = _gat._getTracker("UA-365400-21");
			pageTracker._trackPageview();
		} catch(err) {}
	</script>
	<?php 
	}
	?>
<?
if ($sUri != "")
{
?>
    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <script>
     (adsbygoogle = window.adsbygoogle || []).push({
          google_ad_client: "ca-pub-9874604497476880",
          enable_page_level_ads: true
     });
    </script> 
<?
} 
?>
	
</head>

<body>

<?php if (!OFFLINE && DISPLAY_ADDTHIS) { ?>
<div class="addthis_left">
<!-- AddThis Button BEGIN -->
<div class="addthis_toolbox hidden-phone addthis_floating_style addthis_counter_style" style="left:10px;top:190px;">
<a class="addthis_button_facebook_like" fb:like:layout="box_count"></a>
<a class="addthis_button_tweet" tw:count="vertical"></a>
<a class="addthis_button_google_plusone" g:plusone:size="tall"></a>
<a class="addthis_counter"></a>
</div>
<script type="text/javascript">var addthis_config = {"data_track_addressbar":false};</script>
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5101240045c9c761"></script>
<!-- AddThis Button END -->
</div>
<?php } ?>


<!-- start: Top Menu -->
<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">

    	    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            
            <div class="span12" style="margin-left: 0px;">
		<div class="span3">
	            	<a class="brand" href="/"><img src="<?= $_CONFIG['logo_url'] ?>" alt="<?= $_CONFIG['page_title'] ?>" border="0" /></a>
		</div>
		<div class="span8 adbanner_web">
		<?php if (!OFFLINE) { ?>
		
			<script type="text/javascript"><!--
			google_ad_client = "ca-pub-9874604497476880";
			/* Gap Year Header New */
			google_ad_slot = "7047104262";
			google_ad_width = 728;
			google_ad_height = 90;
			//-->
			</script>
			<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
			</script>
		</div>
		<div class="span8 adbanner_mob">
		<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
		<!-- One World365 Mobile Banner Header -->
		<ins class="adsbygoogle"
		     style="display:inline-block;width:320px;height:100px"
		     data-ad-client="ca-pub-9874604497476880"
		     data-ad-slot="1198653468"></ins>
		<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
		</script> 
		<?php } ?>
		</div>
 	   </div>

           <div class="span12 pull-left" style="margin-left: 0px;">
                <?
                require_once("./navigation_builder.php");
                print $oNav->Render();
                ?>
           </div> 

        </div>
    </div>
</div>
<!-- start: Top Menu -->

<!-- start: Container -->
<div class="container">
<div class="container-inner" style="margin-top: 54px;">

