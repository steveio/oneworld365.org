<?

class Company {

    public function __construct(&$db)
    {
        $this->Company($db);        
    }

	function Company(&$db) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$this->db = $db;
		$this->link = "url"; /* @depreciated - ajax vs rest link style */
	}

	

	function Delete($id) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		if (!is_numeric($id)) return false; 
		
		$this->db->query("SELECT id FROM company WHERE id = $id");

		if ($this->db->getNumRows() == 1) {
			$this->db->query("DELETE FROM comp_cat_map WHERE company_id = $id");
			$this->db->query("DELETE FROM comp_act_map WHERE company_id = $id");
			$this->db->query("DELETE FROM keyword_idx_2 WHERE type = 1 AND id = $id");
			$this->db->query("DELETE FROM company WHERE id = $id");
			return true;
		}
	}
	
	
	
	function GetById($id,$sFeilds = "*") {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $_CONFIG;

		
		$sql = "SELECT ".$sFeilds." 
				FROM ".$_CONFIG['company_table']." 
				WHERE id = $id
				ORDER BY 
					title asc;";
		
		$this->db->query($sql);
		
		return $this->db->getObject();
		
	}
		
		
	
	// get a full company profile
	function GetCompany($id,$ret_type = "rows") {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		if (!is_numeric($id)) return false;
		
		global $_CONFIG;

		$sql = "SELECT 
					c.*,
					c.job_credits as pquota,
					CASE
						WHEN (select 1 from euser u where u.company_id = c.id limit 1)=1 THEN 1
						ELSE 0
					END as user_act_exists
				FROM ".$_CONFIG['company_table']." c 
				WHERE c.id = ".$id." 
				ORDER BY c.title asc;";
		
		$this->db->query($sql);
		if ($ret_type == "rows") {
			return $aCompany = $this->db->getRows();
		} elseif ($ret_type == "objects") {
			return $aCompany = $this->db->getObjects();
		}
	}


	// get an array of short company profile results for search page
	function GetCompanyList($type,$id = null,$return = "ARRAY",$limit = 4) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $_CONFIG;

		$select = "SELECT * ";
		switch(strtoupper($type)) {
			case "ALL" :
				$sql = "$select FROM ".$_CONFIG['company_table']." WHERE status = 1 order by title asc;";
				break;
			case "INDEX_LIST_DELTA" :
			    $sql = "SELECT id FROM ".$_CONFIG['company_table']." WHERE status = 1 AND last_updated > last_indexed";
			    break;
			case "INDEX_LIST_DELTA_SOLR" :
			    $sql = "SELECT id FROM ".$_CONFIG['company_table']." WHERE status = 1 AND last_updated > last_indexed_solr AND c.profile_filter_from_search != 't'";
			    break;
			case "INDEX_LIST_ALL" :
			    $sql = "SELECT id FROM ".$_CONFIG['company_table']." WHERE profile_filter_from_search != 't'";
			    break;
			case "COUNTRY" :
				$sql = "$select FROM ".$_CONFIG['company_table']." c, comp_country_map m WHERE c.status = 1 AND m.country_id = $id AND m.company_id = c.id order by title asc;";
				break;
			case "ACTIVITY" :
				$sql = "$select FROM ".$_CONFIG['company_table']." c, comp_act_map m WHERE c.status = 1 AND m.activity_id = $id AND m.company_id = c.id order by title asc;";
				break;
			case "CATEGORY" :
				$sql = "$select FROM ".$_CONFIG['company_table']." c, comp_cat_map m WHERE c.status = 1 AND m.category_id = $id AND m.company_id = c.id order by prod_type desc, title asc;";
				break;
			case "NAME" :
				$sql = "$select FROM ".$_CONFIG['company_table']." c WHERE c.status = 1 AND c.id = '".$id."' order by title asc;";
				break;
			case "KEYWORD" :
				$sql = "$select FROM ".$_CONFIG['company_table']." c WHERE c.status = 1 AND c.id in (".implode(",",$id).") order by title asc;";
				break;
			case "ID" :
				$sql = "SELECT id,title,desc_short,url,logo_url,prod_type,location,url_name FROM ".$_CONFIG['company_table']." c WHERE c.status = 1 AND c.id in (".implode(",",$id).");";
				break;
			case "ID_SORTED" :
				$sql = "SELECT id,title,desc_short,url,logo_url,prod_type,location,url_name FROM ".$_CONFIG['company_table']." c WHERE c.status = 1 AND c.id in (".implode(",",$id).") ORDER BY prod_type desc";
				break;
			case "RECENT" :
				$sql = "SELECT id,title,desc_short,url_name,logo_url,status, to_char(added,'DD/MM/YYYY') as added_date, to_char(last_updated,'DD/MM/YYYY') as updated_date FROM ".$_CONFIG['company_table']." ORDER BY last_updated desc LIMIT 20";
				break;
			case "RECENT_PAID_LISTING" :
				$sql = "SELECT id,title,desc_short,url_name,logo_url,status, to_char(added,'DD/MM/YYYY') as added_date, to_char(last_updated,'DD/MM/YYYY') as updated_date FROM ".$_CONFIG['company_table']." WHERE prod_type >= 1 AND prod_type <= 2 ORDER BY last_updated DESC LIMIT ".$limit;
				break;				
		}

		$this->db->query($sql);
		if ($return == "OBJECTS") {
			return $aCompany = $this->db->getObjects();
		} else {
			return $aCompany = $this->db->getRows();
		}
	}


	function GetExtraInfo($aCompanyIn) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$aCompanyOut = array();
		foreach($aCompanyIn as $c) {
			$this->GetActivityInfo($c);
			$this->GetCategoryInfo($c);
			$this->GetCountryInfo($c);
			$this->GetPlacementCount($c);
			$aCompanyOut[] = $c;
		}
		return $aCompanyOut;
	}

	function  GetPlacementCount(&$aCompany) {
		$oPlacement = new Placement($this->db);
		$aCompany['profile_count'] = $oPlacement->GetPlacementCount("BY_COMPANY",$aCompany['id']);
	}


	/*
	*
	* @todo - migrate to category, activity, country classes *************
	*
	*/
	function GetActivityInfo(&$aCompany) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
				
		$oActivity = new Activity($this->db);
		$arr = $oActivity->GetActivitiesById($aCompany['id'],"company_id");

		for ($i=0; $i<count($arr);$i++) {
			unset($comma);
			$comma = ($i < (count($arr) -1)) ? " / " : "";
			$sText .= $arr[$i]['name'] . $comma;
			$aId[] =  $arr[$i]['id'];
		}
		$aCompany['activity_txt'] = $sText;
		$aCompany['activity_array'] = $aId;
	}



	function GetCategoryInfo(&$aCompany) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$oCategory = new Category($this->db);
		$arr = $oCategory->GetCategoriesById($aCompany['id'],"company_id");
		unset($sText);
		for ($i=0; $i<count($arr);$i++) {
			unset($comma);
			$comma = ($i < (count($arr) -1)) ? " / " : "";
			$sText .= $arr[$i]['name'] . $comma;
			$aId[] =  $arr[$i]['id'];
		}
		$aCompany['category_txt'] = $sText;
		$aCompany['category_array'] = $aId;
	}



	function GetCountryInfo(&$aCompany) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$oCountry = new Country($this->db);
		$arr = $oCountry->getCountriesById($aCompany['id'],"company_id");


		unset($sText);
		unset($sText2);
		
		$a = array();

		for ($i=0; $i<count($arr);$i++) {
			unset($comma);
			$comma = ($i < (count($arr) -1)) ? " / " : "";
			$sText .= $arr[$i]['name'] . $comma;
			$aId[] =  $arr[$i]['id'];
			
			/* build continent text string */
			if (!in_array($arr[$i]['continent'],$a)) {
				$sText2 .= $arr[$i]['continent'] ." / ";
				$a[$arr[$i]['continent']] = $arr[$i]['continent']; 
			}

		}
		
		$aCompany['country_txt'] = $sText;
		$aCompany['continent_txt'] = substr_replace($sText2,"",-2); /* strip extra slash */
		$aCompany['country_array'] = $aId;

	}


	/*
	* Return a HTML drop down list of companies
	* 
	* @param int selected company id
	* @param int constrain to specified company id
	* @param string HTML select element name
	* @param bool display a <select> option
	* @param int constrain to display comps where listing type > x (optional)
	* 
	*/
	function getCompanyNameDropDown($selected_id = null,$id = null,$name= "p_company", $bSelect = false,$iListingType = null,$sOnChangeJS = "") {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		if (is_numeric($id)) {
			$w = " id = ".$id." AND ";
			$bSelect = true;
		}

		if (is_numeric($iListingType)) $w2 = " AND prod_type >= ".$iListingType; 
		
		$this->db->query("SELECT id,title FROM company WHERE $w status = 1 $w2 ORDER BY title asc");
		
		$result = $this->db->getRows();
		$s = "<select id='".$name."' name='".$name."' class='ddlist' onchange=\"".$sOnChangeJS."\">";
		if ($bSelect) {
			$s .= "<option value='NULL'>select</option>";
		}
		foreach($result as $row) {
			$selected = (trim($row['id']) == $selected_id) ? "selected" : "";
			$s .= "<option value='".$row['id']."' $selected>".$row['title']."</option>";
		}
		$s .= "</select>";
		return $s;
	}

	
	public function GetHomepageCompHTML($limit = 5,$trunc = 120) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $_CONFIG,$db;
		
		$db->query("SELECT c.id,c.title,c.desc_short,c.url_name,c.logo_url,c.url, c.last_updated FROM ".$_CONFIG['company_table']." c, comp_website_hp_map m WHERE m.website_id = ".$_CONFIG['site_id']." AND m.company_id = c.id ORDER BY random() LIMIT $limit");
		$aCompany = $db->getObjects();
		
		$aProfile = array();
		
		foreach ($aCompany as $c) {
			
			$oProfile = new CompanyProfile();
			$oProfile->SetFromArray($c);
			$oProfile->GetImages();
			
			$aProfile[] = $oProfile;
			
			/*
			$s .= "<div id='comp_profile_rpanel'>";
			
			//$s .= "<div id='comp_profile_rpanel_img'><a href='".$oProfile->GetProfileUrl()."' title='".$oProfile->GetTitle()." : ".$oProfile->GetDescShort()."'><img src='".$oProfile->GetLogoUrl()."' alt='".$oProfile->GetTitle().":".$oProfile->GetDescShort()."' border='0'></a></div>";
			if (is_object($oProfile->GetImage(0,LOGO_IMAGE))) {
			$s .= "<div id='comp_profile_rpanel_img'><a href='".$oProfile->GetProfileUrl()."' title='".$oProfile->GetTitle()." : ".$oProfile->GetDescShort()."'>".$oProfile->GetImage(0,LOGO_IMAGE)->GetHtml("_sm",$oProfile->GetTitle(),'',FALSE)."</a></div>";
			}
			$s .= "<a class='c_title_sm' href='".$oProfile->GetProfileUrl()."' title='".$oProfile->GetTitle()."'>".$oProfile->GetTitle()."</a><br/>";
			$s .= "<p class='c_desc_sm'>".$oProfile->GetDescShort($trunc)."</p>";
			//$s .= "<a class='c_link1_sm' href='".$oProfile->GetProfileUrl()."' title='View ".$oProfile->GetTitle()." Profile'>view profile >></a><br />";
			//$s .= "<a class='c_link2_sm' href='".$oProfile->GetUrl()."' target='_new' title='Visit ".$oProfile->GetTitle()." Website' onclick=\"javascript: pageTracker._trackPageview('/outgoing/". $oProfile->GetUrlName()."/www');\">visit website >></a>";			
			$s .= "</div>";
			*/
		}
		return $aProfile;
	}
	

	function GetFeaturedCompanyList($type) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $_CONFIG;
		
		if ($type == "ENHANCED") {
			$this->db->query("SELECT id,title,location,desc_short,url,logo_url, url_name FROM ".$_CONFIG['company_table']." WHERE status = 1 AND prod_type = ".ENHANCED_LISTING." order by random() limit 9;");
			$arr = $this->db->getObjects();
			
			
			$s = "<div id=\"enhanced_company\">\n";
			$s .= "<div style='float: left; width: 640px;'><p style='font-size: 10px;'>Featured Summer Camps - </p></div>\n";
			foreach ($arr as $c) {
				
				$oProfile = new CompanyProfile();
				$oProfile->SetFromArray($c);

				$s .= "<div style='position: relative; float: left; width: 190px; height: 260px; margin: 0px 4px 0px 0px; padding: 6px; margin: 3px; border: 1px dotted #CCCCCC;'>\n";
				//$s .= "<div style='height: 85px; width: 160px;'><a href=\"".$oProfile->GetProfileUrl()."\" title='". $c->title ."' target='_new' ><img src=\"". $oProfile->GetLogoUrl()."\" border=\"0\" alt=\"". $oProfile->GetTitle()."\"  /></a></div><!-- end comp img -->\n";
				$s .= "<div style='height: 85px; width: 160px;'><a href=\"".$oProfile->GetProfileUrl()."\" title='". $c->title ."' target='_new' >".$oProfile->GetImage(0,LOGO_IMAGE)->GetHtml("_s",$oProfile->GetTitle(),'',FALSE)."</a></div>";
				$s .= "<p class='p_small'>".$oProfile->GetTitle(100)."</p>\n";
				$s .= "<p class='p_small'>".$oProfile->GetDescShort(200)."</p>\n";
				$s .= "<a class='p_small' href='".$oProfile->GetProfileUrl()."' title='View ".$oProfile->GetTitle." Profile'>view profile >></a><br />\n";
				$s .= "<a class='p_small' href='".$c->url."' target='_new' title='Visit ".$oProfile->GetTitle()." Website' style=\"text-decoration: none\">visit website >></a>\n";
				$s .= "</div><!-- end company -->\n";

			}
			$s .= "</div><!-- end enhanced company -->\n\n";
			return $s;

		}
		if ($type == "SPONSORED") {
			
			$this->db->query("SELECT c.id,c.title,c.desc_short, c.logo_url, c.url, c.url_name FROM company c, comp_website_hp_map m WHERE c.status = 1 AND c.id = m.company_id AND m.website_id = ".$_CONFIG['site_id']." order by random();");
			$arr = $this->db->getObjects();
						
			$s = "<div id=\"sponsored_company\">";
			if (is_array($arr)) {
				foreach ($arr as $c) {

					$oProfile = new CompanyProfile();
					$oProfile->SetFromArray($c);
					$oProfile->GetImages();
										
					$s .= "<span class='sponsored_comp_img'><a href=\"".$oProfile->GetProfileUrl()."\" title='". $oProfile->GetTitle() ." : ".$oProfile->GetDescShort()."' target='_new' >";
					if (is_object($oProfile->GetImage(0,LOGO_IMAGE))) {
						$s .= $oProfile->GetImage(0,LOGO_IMAGE)->GetHtml("_sm",$oProfile->GetTitle(),'',FALSE);
					}
					//$s .=  "<img style='margin: 0px 0px 10px 0px;' src=\"". $oProfile->GetLogoUrl()."\" border=\"0\" alt='". $oProfile->GetTitle() ." : ".$oProfile->GetDescShort()."' title='". $oProfile->GetTitle() ."' />";
					$s .="</a></span>";
				}
			}
			$s .= "</div>";
			return $s;
		}
	}

	
	
	function renderCompanySearchResult($aCompany,$iTotalSearchResult,$aOffset,$hdrTxt = "") {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$plural = ($iTotalSearchResult == 1) ? "Result" : "Results"; 
		
		$r .= "<div>";
		if ($hdrTxt != "") {
			$r .= "<h1 class='search_result_title' style='margin: 3px 0px 10px 0px;'>$hdrTxt</h1>";
		} else {
			$r .= "<h1 class='search_result_title'>Found ".$iTotalSearchResult." $plural</h1>";
		}
		$r .= "</div>";
		$i = 0;
		$r .= "<div id='search_result_inner'>";
		foreach($aCompany as $row) { 
			
			$style = ($i == 1) ? "sr_rcol" : "sr_lcol";
			$i = ($i == 1) ? 0 : 1;

			$r .= "<div id='$style'>";	
			if ($row['logo_url'] != "") {
				$r .= "<div style='height: 74px;'><a href='".$_CONFIG['url']."/company/".$row['url_name']."' title='".$row['title']." : ".$row['desc_short']."' class='option'><img src='".$row['logo_url']."' alt='".$row['title']."' border='0'/></a></div>";
			}

			$r .= "<a class='title' href='".$_CONFIG['url']."/company/".$row['url_name']."' title='".$row['title']." : ".$row['desc_short']."' class='option'>".stripslashes($row['title'])."</a>";

			$r .= "<p class='p_small'>".stripslashes($row['desc_short'])."</p>";
			
			$r .= "<a class='p_small' href='".$_CONFIG['url']."/company/".$row['url_name']."'>View Profile >></a>";
			
			if ($row['prod_type'] >= BASIC_LISTING) {
				$r .= "<br /><a class='p_small' onclick=\"javascript: pageTracker._trackPageview('/outgoing/". $row['url_name'] ."/www');\" href='".$row['url']."' target='_new'>Visit Website >></a>";
			}
			$r .= "</div>";
		}
		$r .= "</div>";
		return $r;
	}

	function renderCompanySearchResultList($aCompany,$iTotalSearchResult,$aOffset,$hdrTxt = "") {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		
		global $_CONFIG;

		$plural = ($iTotalSearchResult == 1) ? "Result" : "Results"; 

		$r .= "<p style='font-size: 10px; margin: 3px 0px 10px 0px;'>Matching Organisations -</h1>";

		foreach($aCompany as $row) { 

			if ($row['logo_url'] != "") {
				$r .= "<div style='height: 74px;'><a href='".$_CONFIG['url']."/company/".$row['url_name']."' title='".$row['title']." : ".$row['desc_short']."' class='option'><img src='".$row['logo_url']."' alt='".$row['title']."' border='0'/></a></div>";
			}

			$r .= "<h1 class='title'><a href='".$_CONFIG['url']."/company/".$row['url_name']."' title='".$row['title']." : ".$row['desc_short']."' class='option'>".stripslashes($row['title'])."</a></h1>";
			$r .= "<p style='font-size: 10px; line-height: 16px;'>";
			$r .= "</p><p style='font-size: 9px; margin 0px 0px 0px 0px;'>".stripslashes($row['desc_short'])."</p>";
			$r .= "<a href='".$_CONFIG['url']."/company/".$row['url_name']."' class='option'>View ".$row['title'] ."'s Profile</a>";
			
			if ($row['prod_type'] >= ENHANCED_LISTING) {
				$r .= "<p style='font-size: 10px; margin: 3px 0px 12px 0px; letter-spacing: 2px; '><a href='".$c->url."' target='_new' title='Visit ".$c->title." Website'>visit website >></a></p>";
			}
		}

		$r .= "<a style='margin: 6px 0px 6px 3px;' class='std' href='".$_CONFIG['url']."/company/' title='View all Companies / Organisations'>View All Companies >></a>";

		return $r;
	}



	function GetCompanyCount($type) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		switch($type) {
			case "ALL" : 
				return $this->db->getFirstCell("SELECT count(*) FROM company;");
				break;
			case "BASIC" : 
				return $this->db->getFirstCell("SELECT count(*) FROM company WHERE prod_type = ".BASIC_LISTING.";");
				break;
			case "ENHANCED" : 
				return $this->db->getFirstCell("SELECT count(*) FROM company WHERE prod_type = ".ENHANCED_LISTING.";");
				break;
			case "SPONSORED" : 
				return $this->db->getFirstCell("SELECT count(*) FROM company WHERE prod_type = ".SPONSORED_LISTING.";");
				break;
			case "BY_CATEGORY" :
				$this->db->query("select count(c.id) as comp_count,cat.name from company c, comp_cat_map cc, category cat where c.id = cc.company_id and cc.category_id = cat.id group by cat.id,cat.name order by cat.name asc;");
				return $this->db->getObjects();
				break;
		}
	}

	public function GetFeaturedComp($type = "SPONSORED",$limit = -1) {

		global $db,$_CONFIG;
		
		if ($type == "SPONSORED") {
			$where = "prod_type = ".SPONSORED_LISTING;
		}
		if ($type == "ENHANCED") {
			$where = "prod_type = ".ENHANCED_LISTING;
		}
		if ($type == "BOTH") {
			$where = "prod_type >= ".BASIC_LISTING;
		}
		
		$limit = ($limit >= 1) ? " LIMIT ".$limit : "";

		$sql = "SELECT id,title,desc_short,url_name,logo_url,prod_type FROM ".$_CONFIG['company_table']." WHERE ".$where." ORDER BY random() ".$limit;
		
		$db->query($sql);
		
		$aCompany = $db->getObjects();
				
		foreach($aCompany as $oComp) {
			if ($oComp->prod_type == BASIC_LISTING) {
				$aComp['BASIC'][] = $oComp;
			}
			if ($oComp->prod_type == ENHANCED_LISTING) {
				$aComp['ENHANCED'][] = $oComp;
			}
			if ($oComp->prod_type == SPONSORED_LISTING) {
				$aComp['SPONSORED'][] = $oComp;
			}
		}

		return $aComp;
	}
	
	function GetCompanyCountByCategory() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$arr = $this->GetCompanyCount("BY_CATEGORY");
		$s = "<table cellpadding='2' cellspacing='2' border='0'>";
		foreach ($arr as $c) {
			$s .= "<tr><td>".$c->name."</td><td>".$c->comp_count."</td></tr>";
		}
		$s .= "</table>";
		return $s;
	}


	function hit($id,$hits) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$hits++;
		return $this->db->query("update company set hits = $hits where id = $id;");
	}

	
	public function doSearch(&$sCompResultHTML,&$sPagerHTML,$sSubCatSQL,$sPagerKey = 'P1') {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $_CONFIG;
		
		$aCompany = array();
		$response = array();
		
		/* result counters */
		$iCompResCount = 0;
		
		/* comp result paging params */
		$iLimit = $_CONFIG['results_per_page'];
		$iOffset = is_numeric($_REQUEST[$sPagerKey.'_ro']) ? (($_REQUEST[$sPagerKey.'_ro'] -1) * $iLimit) : 0;  

		$sQuery = $_REQUEST['cat_name'];		
		$sQuery = preg_replace("/\-/"," ",$sQuery);
		$sQuery = preg_replace("/\_/","-",$sQuery);
		
		$aId = array();
		$aId = Search::KeywordSearch($sQuery,$iLimit,$iOffset,$iCompResCount,$mode = "INTERSECT",$sSubCatSQL);
		
		if (is_array($aId) && count($aId) >= 1) {

			$aCompany = $this->GetCompanyList("ID_SORTED",$aId);
		}
	
		if ($iCompResCount > $_CONFIG['results_per_page']) {
			$oPager = new PagedResultSet();
			$oPager->GetByCount($iCompResCount,$sPagerKey."_");
			$url = $_CONFIG['url'].$_REQUEST['uri']; 
			$sPagerHTML = $oPager->getPageNav($url);
		}
		
		if ($iCompResCount >= 1) {	
			/* render company result list html */
			$sCompResultHTML = $this->renderCompanySearchResult($aCompany,$iCompResCount,$aOffset = array());
		} else {
			$sCompanyResultListHTML = "0 Matching Results.";
		}

	}
	

}

?>
