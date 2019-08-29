<?

/*
 *
 * Search - legacy SQL search methods
 *
 * deprecated - SOLR search used instead
 */
class Search {

	/*
	 * parse search query from URI
	 */
	public static function ProcessUri($sUri) {
		
		/* retrieve query from uri */
		$aUri = explode("/",$sUri);
		$sQuery = preg_replace("/\-/"," ",$aUri[2]);
		return $sQuery = preg_replace("/\_/","-",$sQuery);// replace - for searches like i-to-i
	}

	/* main entry point into keyword search
	 *
	 * @param string search query phrase
	 * @param int limit results per page
	 * @param int offset result starting index
	 * @param int reference to total result count
	 * @return array
	 *
	 * 
	 */
	public static function KeywordSearch($phrase, $iLimit, $iOffset, &$iResultCount,$mode = "UNION",$sWhere = "DEFAULT",$sSort = "WEIGHT") {

		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."()");

		
		global $_CONFIG;

		/* 1. Email Search */
		if (Validation::IsValidEmail( $phrase )) {
			if (DEBUG) Logger::Msg("email search");
			return Search::doEmailSearch(trim($phrase));
		}

		/* 2.  url search */
		if (preg_match("/www\./",$phrase )) {
			if (DEBUG) Logger::Msg("url search");
			return Search::doUrlSearch(trim($phrase));
		}

		/* sanitise search phrase */
		$phrase = Search::SanitisePhrase($phrase);
		if (strlen($phrase) < 1) return array(); /* no results if blank search phrase */

		/* generate an array of stemmed search keywords */
		$oIndexer = new Indexer($db);
		$aWords    = array_values($oIndexer->getWords($phrase,$weight = 0,$split = true,$stem = true,$count = false));

		/* run an exact keyword match (AND) on all search terms */
		return Search::Query($aWords,$type = "COMPANY", $iLimit, $iOffset, $iResultCount,$mode,$sWhere,$bCountOnly = false,$sSort);

	}

	/*
	 *  Execute keyword query against stemmed keyword index table
	 *
	 *  @param array words
	 * 	@param string target ($t) is COMPANY || PLACEMENT profile
	 *  @param int limit results per page
	 * 	@param int offset starting index
	 *  @param int reference to total results count
	 * 		- after paging only $_CONFIG['results_per_page'] rows are fetched / returned
	 *  @return array
	 *
	 */
	public static function Query($aWord, $t = "COMPANY", $iLimit = 12, $iOffset = 0, &$iResultCount,$mode = "UNION",$sWhere = "DEFAULT",$bCountOnly = false,$sSort = "WEIGHT",$bPageResults = TRUE) {

		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."() target=$t");
		
		global $db, $_CONFIG;

		$a = array();

		/* no recognised search words */
		if (count($aWord) < 1) return false;

		for($i=0;$i<count($aWord); $i++) {

			/* inner select returns keyword matches ordered by relevancy (frequency / weighting) */
			if ($t == "COMPANY") {
				/* dynamic where clause allows caller to specify additional constraint */
				$sWhereClause = ($sWhere == "DEFAULT") ? $_CONFIG['company_table']." c WHERE s.id = c.id " : $sWhere;
				$inner_sql .= "SELECT c.id,c.prod_type,s.total_weight,'".$aWord[$i]."' as word FROM ( SELECT distinct(id),COUNT(*) AS nb, SUM(count) AS total_weight, $i as idx FROM keyword_idx_2 WHERE word = '".$aWord[$i]."' AND type = 1 GROUP BY id HAVING count(*) >= 1 )  s, ".$sWhereClause;
			}
			if ($t == "PLACEMENT") {
				$sWhereClause = ($sWhere == "DEFAULT") ? $_CONFIG['company_table']." c WHERE s.id = p.id AND p.ad_active = 't' AND p.company_id = c.id " : $sWhere;
				$inner_sql .= "SELECT p.id,s.total_weight,'".$aWord[$i]."' as word, c.id as company_id FROM ( SELECT distinct(id),COUNT(*) AS nb, SUM(count) AS total_weight, $i as idx FROM keyword_idx_2 WHERE word = '".$aWord[$i]."' AND type = 2 GROUP BY id HAVING count(*) >= 1 )  s, ".$_CONFIG['placement_table']." p, ".$sWhereClause;				
			}
				
			/* UNION each keyword sql */
			if ($i < count($aWord) -1) $inner_sql .= "UNION ";

		}

		/* outer select ensures only results matching all words, 
		 * applies sort: by listing type, by relevancy
		 */ 		
		if ($t == "COMPANY") {

			if (count($aWord) > 1) {
				$sql = "SELECT r2.id,r2.prod_type,sum(r2.weight) FROM (";
				$sql .= "SELECT distinct(r.id),r.word,r.prod_type,sum(r.total_weight) as weight FROM ( ".$inner_sql." ) r GROUP BY r.id,r.word,r.prod_type HAVING count(*) >= 1 ORDER BY r.prod_type DESC, sum(r.total_weight) DESC";
				$sql .= ") r2 WHERE r2.word in ('".implode("','",$aWord)."') GROUP BY r2.id,r2.prod_type HAVING count(*) = ".count($aWord)." ORDER BY r2.prod_type DESC, sum(r2.weight) DESC;";
			} else {
				$sql = $inner_sql;
				$sql .= "ORDER BY c.prod_type DESC, s.total_weight DESC";
			}
		}
		if ($t == "PLACEMENT") {
			
			if (count($aWord) > 1) {
				$sql = "SELECT r2.id,sum(r2.weight), r2.company_id FROM (";
				$sql .= "SELECT distinct(r.id),r.word,sum(r.total_weight) as weight, r.company_id FROM ( ".$inner_sql." ) r GROUP BY r.id,r.word, r.company_id HAVING count(*) >= 1";		
				$sql .= ") r2 WHERE r2.word in ('".implode("','",$aWord)."') GROUP BY r2.id, r2.company_id HAVING count(*) = ".count($aWord)." ORDER BY sum(r2.weight) DESC;";
			} else {
				$sql = $inner_sql;
				if ($sSort == "WEIGHT") {
					$sql .= "ORDER BY s.total_weight DESC";
				} elseif ($sSort == "RANDOM") {
					$sql .= "ORDER BY RANDOM() DESC";
				} elseif ($sSort == "NONE") {
					//$sql .= "ORDER BY RANDOM() DESC";
				}
			}
			
		}

		if (DEBUG) {
			Logger::Msg("Word: ".$aWord[$i]);
			Logger::Msg($sql);
			$time_start = microtime(true);
		}

		//Logger::Msg($sql);
		
		/* run the query */
		$db->query($sql);

		/* get a count of all results */
		$iResultCount = $db->getNumRows();

		if (DEBUG) {
			$time_end = microtime(true);
			$time = $time_end - $time_start;
			Logger::Msg("ExecTime: ".$time);
			Logger::Msg("ResultCount: ".$iResultCount);
			Logger::Msg("Offset: ".$iOffset);
			Logger::Msg("Limit: ".$iLimit);
				
			$time_start = microtime(true);
		}
		if ($bCountOnly) return true;

		if ($bPageResults) {
			/* fetch only rows corresponding to requested page of results */
			if ($iResultCount >= 1) {
				$k = (($iResultCount < $iLimit) || ($iOffset + $iLimit) > $iResultCount) ? $iResultCount : ($iOffset + $iLimit);
				for($i = $iOffset; $i <  $k; $i++) {
					$aTmp = $db->getRow($fetchmode = PGSQL_ASSOC,$i);
					$aResult[$i] = $aTmp['id'];
				}
			}
		} else {
			$aTmp = $db->getRows($fetchmode = PGSQL_ASSOC,$i);
			$aResult = array();
			foreach($aTmp as $row) {
				$aResult[$row['company_id']][] = $row['id'];
			}
			// reindex the array so keys are a sequential numeric index
			$aIdIndexedNumeric = array();
			
			$i = 0;

			foreach($aResult as $company_id => $aPlacementId) {
				$aIdIndexedNumeric[$i++] = $aPlacementId;
			}

			return $aIdIndexedNumeric;
			
		}
			
		if (DEBUG) {
			$time_end = microtime(true);
			$time = $time_end - $time_start;
			Logger::Msg("ExecTime: ".$time);
		}

		if (count($aResult) < 1) return array();

		return $aResult;
	}

	/*
	 *  Placement Search : Main Entry Point
	 *
	 * @param string search query
	 * @param int limit results per page
	 * @param int offset result starting index
	 * @param int reference to total result count
	 * @parm bool count only - only compute project count, do not return results
	 * @return array
	 *
	 */
	public static function PlacementSearch($phrase, $iLimit, $iOffset, &$iResultCount,$bCountOnly = false,$sWhere = "DEFAULT",$sSort = "WEIGHT",$bPageResults = TRUE) {

		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."()");

		global $_CONFIG;

		/* sanitise search phrase */
		$phrase = Search::SanitisePhrase($phrase);
		if (strlen($phrase) < 1) return array(); /* no results if blank search phrase */

		/* generate an array of stemmed search keywords */
		$oIndexer = new Indexer($db);
		$words    = array_values($oIndexer->getWords($phrase,$weight = 0,$split = true,$stem = true,$count = false));

		/* run an exact keyword match (AND) on all search terms */
		
		return Search::Query($words,$type = "PLACEMENT", $iLimit, $iOffset, $iResultCount,$mode = "UNION",$sWhere,$bCountOnly,$sSort,$bPageResults);

	}


	/*
	 * Advanced Search : Main Entry Point
	 * 
	 * @depreciated - everything runs off Query() above
	 *
	 * @param $_POST submitted search form criteria
	 * @param string target match (COMPANY || PLACEMENT) profiles
	 * @param int limit results per page
	 * @param int offset starting result index
	 * @param int reference to total result count
	 * @param array reference to message container
	 *
	 */
	public static function AdvancedSearch($http_post,$t = "COMPANY", $iLimit = 12, $iOffset = 0, &$iResultCount,&$response) {

		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."() mode=$t");

		global $db,$_CONFIG;

		/* get id's of selected category/activity/country/continent */
		$aSelected = Mapping::GetFromRequest($http_post,$mode = "KEYS");

		/* apply search constraints */
		if (count($aSelected['ctn']) > 1) {
			$response['msg'][] = "Error : You can only search one continent at a time.";
			$bIsError = true;
		}

		if (count($aSelected['cty']) > 7) {
			$response['msg'][] = "Error : You can only search up to 7 countries at a time.";
			$bIsError = true;
		}

		if (count($aSelected['cat']) > 2) {
			$response['msg'][] = "Error : You can only search up to 2 categories at a time.";
			$bIsError = true;
		}

		if (count($aSelected['act']) > 5) {
			$response['msg'][] = "Error : You can only search up to 5 activities at a time.";
			$bIsError = true;
		}

		if ($bIsError) return array();

		foreach ($aSelected as $k => $v) {

			if (count($v) < 1) continue;

			/* inner sql matches profiles according to category, activity, country, continent mappings */
			switch($k) {
				case "cat" :
					$sConstraint = (count($v) > 1) ? "IN (".implode(",",$v).")" : "= $v[0]";
					if ($t == "COMPANY") {
						$sql[] = "SELECT ccm.company_id as id FROM comp_cat_map ccm WHERE ccm.category_id ".$sConstraint;
					} elseif ($t == "PLACEMENT") {
						$sql[] = "SELECT ccm.prod_id as id FROM prod_cat_map ccm WHERE ccm.category_id ".$sConstraint;
					}
					break;
				case "act" :
					$sConstraint = (count($v) > 1) ? "IN (".implode(",",$v).")" : "= $v[0]";
					if ($t == "COMPANY") {
						$sql[] .= "SELECT cam.company_id as id FROM comp_act_map cam WHERE cam.activity_id ".$sConstraint;
					} elseif ($t == "PLACEMENT") {
						$sql[] = "SELECT cam.prod_id as id FROM prod_act_map cam WHERE cam.activity_id ".$sConstraint;
					}
					break;
				case "cty" :
					$sConstraint = (count($v) > 1) ? "IN (".implode(",",$v).")" : "= $v[0]";
					if ($t == "COMPANY") {
						$sql[] .= "SELECT ctm.company_id as id FROM comp_country_map ctm WHERE ctm.country_id ".$sConstraint;
					}  elseif ($t == "PLACEMENT") {
						$sql[] .= "SELECT ctm.id as id FROM placement ctm WHERE ctm.country_id ".$sConstraint;
					}
					break;
				case "ctn" :
					$sConstraint = (count($v) > 1) ? "IN (".implode(",",$v).")" : "= $v[0]";
					if ($t == "COMPANY") {
						$sql[] .= "SELECT ctm.company_id as id FROM comp_country_map ctm, continent cn, country cty WHERE cn.id  ".$sConstraint." AND cn.id = cty.continent_id AND cty.id = ctm.country_id";
					} elseif ($t == "PLACEMENT") {
						$sql[] .= "SELECT ctm.id  FROM placement ctm, continent cn, country cty WHERE cn.id  ".$sConstraint." AND cn.id = cty.continent_id AND cty.id = ctm.country_id";
					}
			}

		}

		/* outer sql removes duplicates, retrieves full profile details, applies sort */
		if ($t == "COMPANY") {
			$sql = "SELECT distinct(s.id),c.title,c.prod_type,c.url_name,c.logo_url,c.url,c.desc_short FROM (".implode(" INTERSECT ",$sql).") s, ".$_CONFIG['company_table']." c WHERE s.id = c.id GROUP BY s.id, c.title,c.prod_type,c.url_name,c.logo_url,c.desc_short,c.url ORDER BY c.prod_type DESC, c.title ASC";
		} elseif ($t == "PLACEMENT") {
			$sql = "SELECT distinct(p.id),p.title,p.url_name,p.desc_short FROM (".implode(" INTERSECT ",$sql).") s, placement p, ".$_CONFIG['company_table']." c WHERE s.id = p.id AND p.ad_active = 't' AND p.company_id = c.id GROUP BY p.id, p.title,p.url_name,p.desc_short";
		}

		if (DEBUG) {
			Logger::Msg($sql);
			$time_start = microtime(true);
		}

		/* execute the query */
		$db->query($sql);

		/* get a count of matching rows */
		$iResultCount = $db->getNumRows();


		if (DEBUG) {
			$time_end = microtime(true);
			$time = $time_end - $time_start;
			Logger::Msg("ExecTime: ".$time);
			Logger::Msg("ResultCount: ".$iResultCount);
			Logger::Msg("Offset: ".$iOffset);
			Logger::Msg("Limit: ".$iLimit);
				
			$time_start = microtime(true);
		}

		/* fetch only rows corresponding to requested page of results */
		if ($iResultCount >= 1) {
			$k = ($iResultCount < $iLimit) ? $iResultCount : ($iOffset + $iLimit);
			$k = (($iOffset + $iLimit) > $iResultCount) ? ($iOffset + ($iResultCount - $iOffset)) : $k;
			for($i = $iOffset; $i <  $k; $i++) {
				$aResult[] = $db->getRow($fetchmode = PGSQL_BOTH,$i);
			}
		}

		/* return the results */
		if (count($aResult) < 1) return array();
		return $aResult;

	}


	/*
	 *  Email Search (Company Profile Only)
	 */
	public static function doEmailSearch($sEmail) {

		global $db,$_CONFIG;

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		$db->query("SELECT id FROM ".$_CONFIG['company_table']." WHERE email ilike '%".$sEmail."%'");

		if ($db->getNumRows() >= 1) {
			$aTmp = $db->getRows();
			return $aResult[] = $aTmp['id'];
		}

		return array();
	}



	/*
	 * Url Search (Company Profile Only)
	 */
	public static function doUrlSearch($sUrl) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db,$_CONFIG;

		$db->query("SELECT id FROM ".$_CONFIG['company_table']." WHERE url ilike '%".$sUrl."%'");

		if ($db->getNumRows() >= 1) {
			$aTmp = $db->getRows();
			return $aResult[] = $aTmp['id'];
		}
		return array();
	}

	/*
	 * Title Search (Company Profiles Only)
	 * @depreciated for Search::Query() above
	 */
	public static function doTitleSearch($phrase) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db,$_CONFIG;

		$db->query("SELECT distinct(id) from ".$_CONFIG['company_table']." WHERE title ilike '".$phrase."' AND status = 1");
		if ($db->getNumRows() >= 1) {
			return $db->getRows();
		} else {
			return array();
		}
	}

	/*
	 * Clean non-alpha / space chars from search phrase
	 */
	private static function SanitisePhrase($phrase) {
		return trim(preg_replace("/[^ a-zA-Z0-9]/","",$phrase));
	}

}

?>
