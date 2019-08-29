<?

/* enforce UTF-8 rendering */
header('Content-Type: text/html; charset=utf-8');


require_once("./conf/config.php");
require_once("/www/vhosts/oneworld365.org/htdocs/conf/banner_config.php");



require_once($_CONFIG['root_path']."/classes/db_pgsql.class.php");
require_once($_CONFIG['root_path']."/classes/authenticate.class.php");
require_once($_CONFIG['root_path']."/classes/user.class.php");
require_once($_CONFIG['root_path']."/classes/category.class.php");
require_once($_CONFIG['root_path']."/classes/activity.class.php");
require_once($_CONFIG['root_path']."/classes/country.class.php");
require_once($_CONFIG['root_path']."/classes/continent.class.php");
require_once($_CONFIG['root_path']."/classes/tag_cloud.class.php");
require_once($_CONFIG['root_path']."/classes/json.class.php");
require_once($_CONFIG['root_path']."/classes/cache.class.php");
require_once($_CONFIG['root_path']."/classes/logger.php");
require_once($_CONFIG['root_path']."/classes/mapping.class.php");
require_once($_CONFIG['root_path']."/classes/validation.class.php");
require_once($_CONFIG['root_path']."/classes/name_service.class.php");
require_once($_CONFIG['root_path']."/classes/website.class.php");
require_once($_CONFIG['root_path']."/classes/feature.class.php");
require_once($_CONFIG['root_path']."/classes/search.class.php");
require_once($_CONFIG['root_path']."/classes/indexer.class.php");
require_once($_CONFIG['root_path']."/classes/stemmer.class.php");
require_once($_CONFIG['root_path']."/classes/date.class.php");
require_once($_CONFIG['root_path']."/classes/select.php");
require_once($_CONFIG['root_path']."/classes/enquiry.class.php");
require_once($_CONFIG['root_path']."/classes/error.class.php");
require_once($_CONFIG['root_path']."/classes/template.class.php");
require_once($_CONFIG['root_path']."/classes/option.class.php");
require_once($_CONFIG['root_path']."/classes/image.class.php");
require_once($_CONFIG['root_path']."/classes/file.class.php");
require_once($_CONFIG['root_path']."/classes/listing.class.php");
require_once($_CONFIG['root_path']."/classes/email2friend.class.php");
require_once($_CONFIG['root_path']."/classes/sql_builder.class.php");
require_once($_CONFIG['root_path']."/classes/paypal.class.php");
require_once($_CONFIG['root_path']."/classes/ip_address.class.php");
require_once($_CONFIG['root_path']."/classes/link.class.php");
require_once($_CONFIG['root_path']."/classes/article.class.php");
require_once($_CONFIG['root_path']."/classes/search_result.class.php");
require_once($_CONFIG['root_path']."/classes/file_upload.class.php");

/* @depreciated - to be replaced by Profile* classes below */
require_once($_CONFIG['root_path']."/classes/company.class.php");
require_once($_CONFIG['root_path']."/classes/placement.class.php");

/* Profile System */
require_once($_CONFIG['root_path']."/classes/ProfileInterface.php");
require_once($_CONFIG['root_path']."/classes/ProfileAbstract.class.php");
require_once($_CONFIG['root_path']."/classes/ProfileFactory.class.php");
require_once($_CONFIG['root_path']."/classes/ProfilePlacement.class.php");
require_once($_CONFIG['root_path']."/classes/ProfileCompany.class.php");
require_once($_CONFIG['root_path']."/classes/ProfileGeneral.class.php");
require_once($_CONFIG['root_path']."/classes/ProfileTour.class.php");
require_once($_CONFIG['root_path']."/classes/ProfileJob.class.php");



require_once($_CONFIG['root_path']."/classes/EmailSender.php");
require_once($_CONFIG['root_path']."/classes/htmlMimeMail.php");
require_once($_CONFIG['root_path']."/classes/RFC822.php");
require_once($_CONFIG['root_path']."/classes/smtp.php");
require_once($_CONFIG['root_path']."/classes/mimePart.php");




if(!is_object($db))
	$db = new db($dsn,$debug = false);



// Is the user authenticated?
$oAuth = new Authenticate($db,$redirect = $_CONFIG['url']."/");
$oAuth->main();


if (!is_numeric($oAuth->oUser->id)) {
	include("./cache.php");
}


if (!$bNoHTMLHeader) {
	include("./header_html.php");
}

?>
