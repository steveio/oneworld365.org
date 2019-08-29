<?


class Cache {

	/* get a page from the cache, optionally record a miss */
	public static function Get($uri,$iSiteId) {

		$p = CACHE_PATH."/page/".md5($uri).".cache";
		if (file_exists($p)) {
			include($p);
			die();
		} else {
		   if (CACHE_LOG) {

			$f = "/tmp/cache_miss_".$iSiteId.".log";
	                $fh = fopen($f, 'a');
                	if (!$fh) return false;
        	        fwrite($fh, $uri."\n\r");
	                fclose($fh);

		   }

		}
	}

	/* save a cache page */
	public static function Save($f,$d) {
		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."()");
		$fh = fopen($f, 'w');
		if (!$fh) return false;
		fwrite($fh, $d);
		fclose($fh);
	}

	/* generate a cache page */
	public static function Generate($sUrl,$sUri,$iSiteId,$sleep = true) {

		global $db;

		$sUrl = $sUrl ."?&nocache=true";
		$sHTML = @file_get_contents($sUrl);
		if (!$sHTML) return false;

		switch($iSiteId) {
			case 0:
				$sPath = "/www/vhosts/oneworld365.org/htdocs/cache/page/";
				break;
			default: 
				return false;
		}
		
		$sCachePath = $sPath.md5($sUri).".cache";

		/*
		Logger::Msg($sUrl,'plaintext');
		Logger::Msg($sUri,'plaintext');
		Logger::Msg($iSiteId,'plaintext');
		Logger::Msg($sPath);
		*/
		
		Cache::Save($sCachePath,$sHTML);
		$db->query("UPDATE cache SET active = 'T', last_update = now()::timestamp WHERE uri = '".$sUri."' AND sid = ".$iSiteId);
		if ($sleep) { /* for batch updates */
			usleep(500000); /* throttle delay .5 sec */
		}		
	}

}


?>
