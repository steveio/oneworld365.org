<?


/*
 * Cache Front End - Looks for cache version of requested URI 
 * Records cache misses to db for caching by back end process
*/


if (CACHE_ENABLED) {
	$uri = $_SERVER['REQUEST_URI'];



	if((isset($_GET['cacheupdate'])) || ($_GET['qs'] == "cacheupdate")) {
		$a = explode("?",$uri);
		Cache::Generate($_CONFIG['url'].$a[0],$a[0],$_CONFIG['site_id'],$sleep = false);
	}
	Cache::Get($uri,$_CONFIG['site_id']);

}
?>
