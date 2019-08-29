<?

require_once("./conf/config.php");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" >

<head>

<title><?= $_CONFIG['page_title']; ?></title>
<meta name="verify-v1" content="1jeQGf+kRXgKS8v02+5fum0AKYwOQi4o7hxGLX1Y0tY=" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="<?= $_CONFIG['page_description']; ?>" />




</head>


<body>


<!-- BEGIN Page Wrap -->
<div class="wrap">
   



<!-- BEGIN Header -->

<div class="header">
  <span class="crnr tl"></span>
  <span class="crnr tr"></span>


<div class="heading logo">
	<div class="col five clear">
		<div class="col two-sm">
			<p class="logo">
			
			<img src="<?= $_CONFIG['logo_url'] ?>" alt="<?= $_CONFIG['page_description'] ?>" border="0" />

			</p>
		</div>						
	</div>


</div>


<!-- BEGIN Page Content Container -->
<div class="page_content content-wrap clear">
<div class="row pad-tbl clear">

     <div class="col five clear">
             <h1><?= $_CONFIG['site_title']; ?> is Temporarily Unavailable</h1>
        <div class="col four-sm">
             <p class="logo"><?= $_CONFIG['site_title']; ?> is currently unavailable.  This means we are doing some upgrades or have experienced a technical problem.  We are working to bring the sites back up as soon as possible.</p>
	</div>
	<div class="col four-sm pad-tb">
             <p>Please visit again later or you can contact us admin info [at] oneworld365 dot org<p/>
	     <p>Kind Regards</p>
	     <p>One World 365</p>
        </div>
     </div>

</div>
</div>





        
</form>

</div>
<!-- END Page Wrap -->

</body>


</html>




