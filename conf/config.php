<?


ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/www/vhosts/www.oneworld365.org/logs/php_error.log'); 
error_reporting(E_ALL &~ E_NOTICE &~ E_STRICT);

date_default_timezone_set('Europe/London');


// config.......

// db connection... (@todo - move into $_CONFIG for consistancy)
$dsn = array("dbhost" => "localhost","dbuser" => "oneworld365_pgsql", "dbpass" => "tH3a1LAn6iA","dbname" => "oneworld365","dbport" => "5432");


// Solarium

$solr_config = array(
		'adapteroptions' => array(
				'host' => '127.0.0.1',
				'port' => 8983,
				'path' => '/solr/collection1/'

		)
);


define('DEV',false);
define('DEBUG',false);

define("OFFLINE",true);
define("DISPLAY_ADDTHIS", false );

define('TEST_EMAIL','');

/* 0 = none, 1 = error, 2 = debug, 3 = verbose debug */
define('LOG_LEVEL',3);

define('ROOT_PATH','/www/vhosts/oneworld365.org/htdocs');
define('LOG_PATH',"/www/vhosts/oneworld365.org/logs/");
define('CONFIG_HOME','/www/vhosts/oneworld365.org/htdocs/conf/');

define('CACHE_PATH',ROOT_PATH."/cache");
define('CACHE_ENABLED',true);
define('CACHE_LOG',false);

/* listing types */
define("FREE_LISTING",0);
define("BASIC_LISTING",1);
define("ENHANCED_LISTING",2);
define("SPONSORED_LISTING",3);

/* default placement quotas */
define("FREE_PQUOTA",0);
define("BASIC_PQUOTA",1);
define("ENHANCED_PQUOTA",10);
define("SPONSORED_PQUOTA",25);


/* no of permitted login attempts before account is locked */ 
define("MAX_LOGIN_ATTEMPTS",20);

/* Password Encryption md5 Hash Salt Length - do not change */
define('SALT_LENGTH', 9);


define('IMAGE_MAX_UPLOAD_SIZE', 5120000);


$_CONFIG = array(
	'host_prefix' => 'http://www.',

	// site specific
	'site_id' => 0,
	'brand' => 'oneworld365.org',
	'url' => 'http://www.oneworld365.org',
	'root_path' => ROOT_PATH,
	'company_home' => "/company",
	'placement_home' => "/placement",
	'category_home' => "/category",
	'template_home' => "/templates",
	'login_redirect_url' => '/account/',

	'email_template_hdr' => '/email_html_header.php',
	'email_template_footer' => '/email_html_footer.php', 

	'sites' => array(
							0 => "oneworld365.org"
						),


	'site_title' => 'One World 365',
	'logo_url' => 'http://www.oneworld365.org/images/oneworld365_logo_small.png',
	'meta-tag' => '',	
	'admin_email' => 'admin@oneworld365.org',
	'website_email' => 'website@oneworld365.org',
	'bcc_list' => 'admin@oneworld365.org',

	'error_page' => 'http://www.oneworld365.org/error.php',
	'google_webtag' => "<meta name='google'></meta>",
	'css_file' => "oneworld365.css",
	'google_analytics_id' => 'UA-365400-11',


	// banners (defined in /www/vhosts/oneworld365.org/htdocs/conf/banner_config.php )
	'banner_id' => 'ONEWORLD365',
	'vertical_banner_id' => 'ONEWORLD365_VERTICAL',

	// search
	'results_per_page' => '12',
	'log_search' => true, // store details of search in a log table
	'no_featured_placements' => 10, // how many featured placements to display on homepage

	/* @depreciated - fragment cache system parameters */
	'cache_enabled' => false, // cache master on || off?
	'cache_create' => true, /* @depreciated - generate cache items via browser */
	
	/* default 8bit profile / enquiry option bitmaps for all comps added via this site */
	'prof_opt' => '11100000',
	'enq_opt' => '10000000',
	

	// homepage panels
	'has_enhanced' => false,
	'has_sponsored' => true,
	'has_recent_placements' => true,
	'has_company_by_category' => true,
	'has_partnerships' => true,
	'has_select_panel' => true,
	'has_vertical_ads' => true,
	'has_tabs' => true,
	'has_recent_paid_listing' => true,
	'has_ideas_panel' => true,

	// partnerships
	'has_partnership_01' => false,
	'has_partnership_02' => false,
	'has_partnership_03' => true,
	'has_partnership_05' => false,


	// parameterised table/view names
	'company_table' => 'company',
	'placement_table' => 'profile_hdr',
	'profile_hdr_table' => 'profile_hdr', /* placement table is a view in some sites, these must use profile_hdr for add/update */
	'index_table' => 'keyword_idx_view_0',
	'tagcloud_table' => 'keyword_idx_1',
	'comp_country_map' => 'comp_country_map',
	'image_map' => 'image_map',
	'image' => 'image',

	// also defines website id's eg.  summercampjobs = site_id 4
	'categories' => array(0 => "Volunteer",6 => "Seasonal Jobs",3 => "Summer Camp",4 => "Teaching",1 => "Work",2 => "Travel / Tour"),
	'category_images' => true,

		

	// global page specifc
	'intro_title' => 'One World 365 : Work, Volunteer, Teach, Study, Travel Worldwide.',
	'intro_para' => 'One World 365 - Search thousands of meaningful travel opportunities, travel ideas and family holidays with the worlds leading specialist travel and tour operators. Explore new countries and benefit local communities, wildlife, conservation and environment programs.',

        'page_title' => 'Meaningful Travel Ideas, Gap Years, Career Breaks, Activity & Adventure Holidays',

    'page_description' => 'Search thousands of travel opportunities, gap year trips and adventure holidays with local and international operators. Explore new countries, work abroad, learn a new language or volunteer to help wildlife, communities & the environment',

    'site_info' => 'One World 365 - Search thousands of meaningful travel opportunities, travel ideas and family holidays with the worlds leading specialist travel and tour operators. Explore new countries and benefit local communities, wildlife, conservation and environment programs.',

        'page_keywords' => 'meaningful travel ideas, adventure holidays, vacations, family holidays, responsible travel, overland tours, volunteer programs, hiking holidays, trekking tours, cycling tours, walking holidays, ethical travel, eco tourism, conservation projects, community programs, working holidays, scuba diving trips',


        'page_links' => "",
	
	
	
	// cookie specifc
	'cookiename' => "oneworld365",
	'cookie_domain' => '.oneworld365.org',
	'cookie_path' => '/',
	'session_expires' => 56000,

	'cookie_profile' => "oneworld365_profile",
	
	
	/* google analytics */
	'google_analytics_id' => "UA-365400-11", 

	
	
	'txt_pattern_generic' => 'Work, Volunteer, Travel, Tour ',

	'txt_pattern_country' => array('Work in %s','Travel / Tour in %s','Volunteer in %s'),
	'txt_pattern_continent' => array('Work in %s','Travel / Tour in %s','Volunteer in %s'),
	'txt_pattern_activity' => '%s',
	'txt_pattern_category' => '%s',

	/* Restrict access to known IP addresses (defined in database) */
	'IP_ADDRESS_ACCESS_CHECK' => false, 

	/* Captcha Anti-Spam (http://recaptcha.net/) */
	'CAPTCHA_CHECK_FL' => false, /* enable/disable recaptcha checking? */
	'CAPTCHA_PUBLIC_KEY' => "6LdhQgUAAAAAAKYnGOyjvbS4pvFagygPpCWqOQwm",
	'CAPTCHA_PRIVATE_KEY' => "6LdhQgUAAAAAALFRJ3UApJZE6cdAVoblOu4ZAWQc",
	
	
	/* Misc */
	'VALID_CHAR_REGEX' => "[a-zA-Z0-9@_\-\.]" /* permitted chars in username / password */
	

);





?>
