<?

/*
 * index.php 
 * Simple MVC controller / router
 * Handles request -> resource routing and sets page meta data
 *
 * Used w/ apache mod_rewrite rules : 
 * RewriteEngine on
 * RewriteCond %{SCRIPT_FILENAME} !-f
 * RewriteCond %{SCRIPT_FILENAME} !-d
 * RewriteRule ^(.*)$ index.php/$1
 * 
*/


require_once("./conf/config.php");
require_once("./header_php.php");



/* start a new session */
session_start();


/* Global Exception Handler */
function exception_handler($e) {
	//Logger::DB(1,"Uncaught Exception: ".$e->getMessage());
	print_r("<pre>");
	var_dump($e);
	print_r("</pre>");
	die();
	/*
	global $_CONFIG;
	header('HTTP/1.1 503 Service Temporarily Unavailable');
	require_once("./back_soon.php");
	die();
	*/
}

set_exception_handler('exception_handler');

// $sUri is setup in header_php.php

/* split request into segments */
$a = explode("?",$sUri);
$aUri = explode("/",$a[0]);
$sUri = $a[0];



/* set page meta data defaults */
$_REQUEST['page_title'] = $_CONFIG['page_title'];
$_REQUEST['page_keywords'] = $_CONFIG['page_keywords'];
$_REQUEST['page_description'] = $_CONFIG['page_description'];

$url = $_CONFIG['url'];
$aBreadCrumb = array();
$aBreadCrumb[$url] = "home";

foreach($aUri as $str) {
        if ($str != "" ) {
                $url = $url."/".$str;
                $aBreadCrumb[$url] = $str;
        }
}
$oRouter = new RequestRouter();
$aUri[0] = $sUri;
$oRouter->SetRequestUri($aUri);
$oRouter->Route();





function redirect() {
	global $_CONFIG, $db;
	

	//Logger::DB(1,"404 Not Found: ".$_SERVER['REQUEST_URI']." ".$_SERVER['QUERY_STRING']);
	
	ob_end_clean();
	header('HTTP/1.0 404 Not Found');
	$display_404 = TRUE;
	require("./header_html.php");
	require_once("./home.php"); // display the homepage as a 404 page
	require("./footer.php");
	die();
}


function cleanMetaDesc($str) {
	$str = html_entity_decode(trim(strip_tags(stripslashes(html_entity_decode($str)))));
	return trunc($str,160);
}

function trunc($s,$trunc) {
	if (strlen($s) > $trunc) {
		$s = $s." ";
		$s = substr($s,0,$trunc);
		$s = substr($s,0,strrpos($s,' '));
		$s = $s."...";
		$s = strip_tags($s); // in case we left an open <b> tag
	}
	return $s;
}




?>
