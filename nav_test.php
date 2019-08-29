<?php 
require_once("./conf/config.php");
require_once("./classes/template.class.php");
?>

<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"><![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"><![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"><![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"><!--<![endif]-->

<head>
    <title><?= (strlen($_REQUEST['page_title']) >1) ? $_REQUEST['page_title'] : $_CONFIG['page_title'] ; ?></title>

    <meta name="verify-v1" content="<?= $_CONFIG['meta-tag']; ?>" />
	<meta name="description" content="<?= (strlen($_REQUEST['page_meta_description']) >1) ? $_REQUEST['page_meta_description'] : $_CONFIG['page_description'] ; ?>" />
	<meta name="keywords" content="<?= (strlen($_REQUEST['page_keywords']) >1) ? $_REQUEST['page_keywords'] : $_CONFIG['page_keywords'] ; ?>" />
    
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=100%; initial-scale=1; maximum-scale=1; minimum-scale=1; user-scalable=no;"/>
    <link rel="shortcut icon" href="images/favicon.ico"/>
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/images/apple-touch-icon-144-precomposed.png"/>
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/images/apple-touch-icon-114-precomposed.png"/>
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="images/apple-touch-icon-72-precomposed.png"/>
    <link rel="apple-touch-icon-precomposed" href="/images/apple-touch-icon-57-precomposed.png"/>


    <link rel="stylesheet" type="text/css" href="/css/bootstrap.css"/> 
    <link rel="stylesheet" type="text/css" href="/css/style.css?&r=<?= rand(100,1000); ?>"/>

    <link rel="stylesheet" type="text/css" href="/css/font-awesome.min.css"/>
    
    <link rel="stylesheet" type="text/css" href="/js/superfish/src/css/superfish.css"/>
    
    <!--[if IE 7]>
    <link rel="stylesheet" type="text/css" href="css/font-awesome-ie7.min.css"/>
    <![endif]-->

    <script type="text/javascript" src="/js/jquery.min.js"></script>
    <script type="text/javascript" src="/js/bootstrap.min.js"></script>
    <!-- <script type="text/javascript" src="/js/jquery.flexslider.js"></script> -->
    <script type="text/javascript" src="/js/jquery.easing.1.3.js"></script>
    <script type="text/javascript" src="/js/superfish/src/js/superfish.js"></script>
	<script type="text/javascript" src="/js/superfish/src/js/hoverIntent.js"></script>    
    <script type="text/javascript" src="/js/sftouchscreen.js"></script>
    <script type="text/javascript" src="/js/jquery.elastislide.js"></script>
    <script type="text/javascript" src="/js/smoothscroll.js"></script>
    <script type="text/javascript" src="/js/jquery.ui.totop.js"></script>
    <script type="text/javascript" src="/js/main.js"></script>
    <!--[if lt IE 9]>
    <script type="text/javascript" src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <?= $sJSInclude;?>
    
</head>

<body>

<!-- start: Top Menu -->
<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a class="brand" href="/"><img src="<?= $_CONFIG['logo_url'] ?>" alt="<?= $_CONFIG['page_title'] ?>" border="0" /></a>

			<?
			require_once("./navigation_builder.php");
			print $oNav->Render();
			?>
                        
        </div>
    </div>
</div>
<!-- start: Top Menu -->

<!-- start: Container -->
<div class="container">


</div>
<!-- end: Container -->

<!-- start: Footer -->
<footer id="footer">
    <div class="container">

    </div>
</footer>
<!-- end: Footer -->

<!-- start: Footer menu-->
<section id="footer-menu">
    <div class="container">
        <div class="row">
            <div class="span4">        
                <p class="copyright">&copy; Copyright <?= $_CONFIG['brand'] . " 2007 - ".date('Y'); ?>.</p>
            </div>
            <div class="span8 hidden-phone">
                <ul class="pull-right">
					<li><a title="About Gap Year 365" href="<?= $_CONFIG['url'] ?>/about-gapyear365">about us</a> |</li>
					<li><a title="Help &amp; Advice" href="<?= $_CONFIG['url'] ?>/help-and-advice">help &amp; advice</a> |</li>
					<li><a title="Contact Us" href="<?= $_CONFIG['url'] ?>/contact.php">contact</a> |</li>
					<li><a title="Conditions of Use" href="<?= $_CONFIG['url'] ?>/conditions.php">conditions of use</a> |</li>
					<li><a title="Privacy" href="<?= $_CONFIG['url'] ?>/privacy.php">privacy</a> |</li>
					<li><a title="Links" href="<?= $_CONFIG['url'] ?>/links">links</a> |</li>
					<li><a title="Sitemap" href="<?= $_CONFIG['url'] ?>/sitemap.php">sitemap</a></li>
                </ul>
            </div>
        </div>
    </div>
</section>
<!-- end: Footer menu-->

</body>
</html>


