<?

/*
 * @Depreciated - use ProfilePlacement() and/or ProfileGeneral(). ProfileTour(), ProfileJob() instead
 * 18/07/2009
 * 
 */

class Placement {

    public function __construct(&$db)
    {
        $this->Placement($db);
    }

	function Placement(&$db) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		$this->db = $db;
	}

	function GetAllPlacementId() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;
		$this->db->query("SELECT p.id FROM ".$_CONFIG['placement_table']." p,".$_CONFIG['company_table']." c WHERE p.company_id = c.id ORDER BY p.added desc");
		return $aPlacement = $this->db->getRows();
	}

	function GetPlacementIdByCountry($iCountryId) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;
		
		$this->db->query("SELECT 
							p.id 
						 FROM 
						 	".$_CONFIG['placement_table']." p,
						 	".$_CONFIG['company_table']." c,
							prod_country_map m 
						 WHERE 
						 	m.country_id = $iCountryId
						 	AND m.prod_id = p.id 
						 	AND p.company_id = c.id 
						 ORDER BY 
						 	p.title asc");
		return $aPlacement = $this->db->getRows();
	}

	function GetPlacementIdByContinent($iContinentId) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;
		$this->db->query("SELECT 
							p.id,
							p.title,
							cty.name,
							cn.name 
						 FROM 
						 	".$_CONFIG['placement_table']." p,
						 	prod_country_map m, 
						 	country cty, 
						 	continent cn,
						 	".$_CONFIG['company_table']." comp
						 WHERE 
						 	cn.id = $iContinentId
						 	AND cn.id = cty.continent_id 
						 	AND cty.id = m.country_id 
						 	AND m.prod_id = p.id
						 	AND p.company_id = comp.id;
						");
		
		return $aPlacement = $this->db->getRows();
	}

	function GetPlacementIdByActivity($iActivityId) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;
		$sql = "SELECT p.id FROM ".$_CONFIG['placement_table']." p, prod_act_map m, ".$_CONFIG['company_table']." c WHERE m.activity_id = $iActivityId AND m.prod_id = p.id AND p.company_id = c.id ORDER BY p.title asc";
		$this->db->query($sql);

		return $aPlacement = $this->db->getRows();
	}

	function GetPlacementIdByCategory($iCategoryId) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;
		$this->db->query("SELECT p.id FROM ".$_CONFIG['placement_table']." p, prod_cat_map m, ".$_CONFIG['company_table']." c WHERE m.category_id = $iCategoryId AND m.prod_id = p.id AND p.company_id = c.id ORDER BY p.title asc");
		return $aPlacement = $this->db->getRows();
	}


	function GetPlacementById($id,$key = "placement_id",$ret_type = "rows") {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		$select = "p.id
				   ,p.url_name
				   ,p.title        
				   ,p.desc_short   
				   ,p.desc_long  
				   ,p.company_id   
				   ,c.logo_url
				   ,c.title as company_name
				   ,c.tel
				   ,c.url as comp_url
				   ,c.url_name as comp_url_name   
				   ,p.location
				   ,p.ad_active
				   ,p.url
				   ,p.email
				   ,p.img_url1
				   ,p.img_url2
				   ,p.img_url3
				   ,to_char(p.added,'DD/MM/YYYY') as added_date
				   ,to_char(p.last_updated,'DD/MM/YYYY') as updated_date";
		
		// build the where clause
		switch($key) {
			case  "placement_id" :
				$where = "p.id = $id AND p.company_id = c.id AND p.ad_active = 't'";
				$order_by = " ORDER BY p.title asc ";
				break;
			case "company_id" :
				$where = "p.company_id = $id AND p.company_id = c.id AND p.ad_active = 't'";
				$order_by = " ORDER BY p.title asc ";
				break;
			case "all" :
				$where = "p.company_id = c.id ";
				$order_by = " ORDER BY p.title asc ";
				break;
			case "INDEX_LIST_ALL" :
			    $select = "p.id,p.type";
			    $where = "p.company_id = c.id  and c.profile_filter_from_search != 't'";
			    $order_by = " ORDER BY p.title asc ";
			    break;
			case "INDEX_LIST_DELTA" :
			    $select = "p.id,p.type";
			    $where = "p.last_updated > p.last_indexed AND p.company_id = c.id and c.profile_filter_from_search != 't'";
			    $order_by = " ORDER BY p.title asc ";
			    break;
			case "INDEX_LIST_DELTA_SOLR" :
			    $select = "p.id,p.type";
			    $where = "p.last_updated > p.last_indexed_solr AND p.company_id = c.id and c.profile_filter_from_search != 't'";
			    $order_by = " ORDER BY p.title asc ";
			    break;
			case "id_list" :
				$where = "p.id IN (".implode(",",$id).") AND p.company_id = c.id ";
				$order_by = "ORDER BY RANDOM() ";
				break;
			case "recent" :
				$where = " p.company_id = c.id ";
				$order_by = " ORDER BY p.last_updated DESC LIMIT 20";
				break;
				
		}

		global $_CONFIG;

		$sql = "SELECT
				   $select
				FROM 
				   ".$_CONFIG['placement_table']." p,
				   ".$_CONFIG['company_table']." c
					WHERE
					$where
					$order_by
					;";
					
		$this->db->query($sql);

		if ($ret_type == "rows") {
			return $aPlacement = $this->db->getRows();
		} elseif ($ret_type == "objects") {
			return $aPlacement = $this->db->getObjects();
		}
	}


	function GetLatesthPlacements($iLimit) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;
		if (is_numeric($iLimit)) $sLimit = "LIMIT $iLimit";

		$sql = "SELECT p.id,
				p.title,
				p.desc_short,
				p.url_name,
				p.location,
				c.title as company_name,
				c.url_name as comp_url_name
			FROM ".$_CONFIG['placement_table']." p,
				".$_CONFIG['company_table']." c
		WHERE p.company_id = c.id
		ORDER BY p.added DESC
		$sLimit";
		$this->db->query($sql);
		return $this->db->getObjects();
	}

	

	function GetPlacementThumbnail($iPlacementId,$iCount = 1) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		$limit = ($iCount >= 1) ? "LIMIT ".$iCount : "";  
		
		$aResult = array();
		if (!is_numeric($iPlacementId)) return;
		$sql = "SELECT img_id,ext FROM image_map WHERE link_to = 'PLACEMENT' AND link_id = ".$iPlacementId." ".$limit;
		$this->db->query($sql);

		return $aResult = $this->db->getRows();

	}


	function GetPlacementNameDDList() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;
		
		$sql = "select distinct(p.title), count(p.id) from ".$_CONFIG['placement_table']." p group by p.title;";
		$this->db->query($sql);

		$aResult = $this->db->getRows();

		$s = "<select name='placement_list' class='ddlist'>";
		$s .= "<option value=NULL>-- select --</option>";
		$s .= "<option value=ALL>All Placements</option>";
		foreach ($aResult as $aRow) {
			$s .= "<option value='".$aRow['title']."'>";
			$s .= $aRow['title'] . " (".$aRow['count'].")";
			$s .= "</option>";
		}
		$s .= "</select>";

		return $s;

	}


	function GetSponsoredPlacementList($limit = 6) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db,$_CONFIG;

		$sql = "SELECT 
					p.id, 
					p.title,
					p.desc_short, 
					p.url_name, 
					comp.title as company_name,
					comp.id as company_id, 
					comp.url_name as comp_url_name
				FROM 
					".$_CONFIG['profile_hdr_table']." p,
					".$_CONFIG['company_table']." comp 
				WHERE
					p.company_id = comp.id  
				ORDER BY random() LIMIT ".($limit * 4).";";

		$db->query($sql);

		$aProfile = array();
		
		
		/* ensure that only x per comp appear */
		$iMaxPerComp = 1;
		$aCompId = array();
		
		if ($db->getNumRows() >= 1) {
			$aResult = $this->db->getObjects();

			
			foreach($aResult as $oResult) {

				if (in_array($oResult->company_id,$aCompId)) continue;
				$aCompId[] = $oResult->company_id;
				
				$oProfile = new PlacementProfile();

				
				
				$oProfile->SetId($oResult->id);
				$oProfile->SetCompanyId($oResult->company_id);
				$oProfile->SetTitle(stripslashes($oResult->title));
				$oProfile->SetDescShort(stripslashes($oResult->desc_short));
				$oProfile->SetUrlName($oResult->url_name);
				$oProfile->SetCompanyName(stripslashes($oResult->company_name));
				$oProfile->SetCompUrlName($oResult->comp_url_name);
				
				$oProfile->GetImages();
				$oProfile->GetCountryInfo();
				
				$aProfile[] = $oProfile;

			}
		}

		$aProfile = array_slice($aProfile,0,$limit);
		
		return $aProfile;
	}


	function GetSponsoredPlacementHTML($limit = 6) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;
		
		$aProfile = $this->GetSponsoredPlacementList($limit);
		
		
		if (is_array($aProfile)) {
			$s .= "<p class='p_small'>Popular Placements -</p>\n";
			foreach($aProfile as $oProfile) {

				
				
				$s .= "<div id='popular_placement'>";
				$s .= "<b class=\"rtop\"><b class=\"r1\"></b> <b class=\"r2\"></b> <b class=\"r3\"></b><b class=\"r4\"></b></b>";
				$s .= "<div id=\"popular_placement_item\">";

				
				if (is_object($oProfile->GetImage(0))) {					
					$s .= "<div id='popular_placement_img'><a title='".$oProfile->GetTitle() ." : " . $oProfile->GetCompanyName() . " : ".$oProfile->GetCountryTxt() ."' href=\"".$oProfile->GetProfileUrl()."\">".$oProfile->GetImage(0)->GetHtml("_s",$oProfile->GetTitle())."</a></div>";
				} elseif (is_object($oProfile->GetImage(0,LOGO_IMAGE))) {					
					$s .= "<div id='popular_placement_img'><a title='".$oProfile->GetTitle() ." : " . $oProfile->GetCompanyName() . " : ".$oProfile->GetCountryTxt() ."' href=\"".$oProfile->GetProfileUrl()."\">".$oProfile->GetImage(0,LOGO_IMAGE)->GetHtml("_sm",$oProfile->GetTitle())."</a></div>";
				}
				
				
				$s .= "<a class='p_title_sm' title='".$oProfile->GetTitle()." : ".$oProfile->GetCompanyName(). " : " .$oProfile->GetCountryTxt()."' href=\"".$oProfile->GetProfileUrl()."\">". $oProfile->GetTitle()."</a><br/>\n";
				$s .= "<a class='p_comp_sm' title='".$oProfile->GetTitle()." : ".$oProfile->GetCompanyName(). " : " .$oProfile->GetCountryTxt()."' href=\"".$oProfile->GetProfileUrl()."\">".$oProfile->GetCompanyName()."</a><br/>\n";
				
				if (strlen($oProfile->GetCountryTxt()) < 20) {
					$s .= "<span class='p_cty_sm'>".$oProfile->GetCountryTxt()."</span>";
				}

				$s .= "</div>\n";
				$s .= "<b class=\"rbottom\"><b class=\"r4\"></b> <b class=\"r3\"></b> <b class=\"r2\"></b> <b class=\"r1\"></b></b>";
				$s .= "</div>\n";

			}
			$s .= "<a class='std' style='float: left; font-size: 9px;' href='".$_CONFIG['url']."/placement/' title='View All Placements'>View All Placements >></a>";
		} else {
			$s .= "<p>Advertise your jobs here.</p> <p><a href='./contact.php' title='Contact Us'>Contact us</a>.</p>";

		}

		return $s;
	}


	function renderPlacementSearchResult($aPlacement,$link = 'AJAX',$title = 'Matching Placements', $url = "") {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		
		global $_CONFIG;
		
		if (count($aPlacement) >= 1) {
			$s .= "<p class='p_small'>$title</p>\n";
		}

		if ($url == "") {
			$url = $_CONFIG['url'].$_CONFIG['placement_home'];
		}

		if (is_array($aPlacement)) {

			foreach($aPlacement as $p) {
				
				$oProfile = new PlacementProfile();
				$oProfile->SetFromArray($p);
				$aImg = $oProfile->GetImages();
		
				//var_dump($oProfile);

				//print $oProfile->GetImage(0,LOGO_IMAGE)->GetHtml("_sm");
	
				$s .= "<div id='popular_placement'>";
				$s .= "<b class=\"rtop\"><b class=\"r1\"></b> <b class=\"r2\"></b> <b class=\"r3\"></b><b class=\"r4\"></b></b>";
				$s .= "<div id=\"popular_placement_item\">";
				

				
				if (count($aImg) >= 1) {
					$s .= "<a href='".$oProfile->GetProfileUrl()."' title='".$oProfile->GetCompanyName()." : ".$oProfile->GetTitle()."'>".$aImg[0]->GetHtml("_s",$oProfile->GetTitle())."</a>";
				} elseif (is_object($oProfile->GetImage(0,LOGO_IMAGE))) {
					//$s .= $oProfile->GetImage(0,LOGO_IMAGE)->GetHtml("_sm");
					$s .= "<a href='".$oProfile->GetProfileUrl()."' title='".$oProfile->GetCompanyName()." : ".$oProfile->GetTitle()."'><img src='".$oProfile->GetLogoUrl("_sm")."' alt='".$oProfile->GetTitle()."' border='0' /></a>";
				}

				$s .= "<br /><a class='p_title_sm' href='".$oProfile->GetProfileUrl()."' title='".$oProfile->GetCompanyName()." : ".$oProfile->GetTitle()."'>".$oProfile->GetTitle(64) ."</a>";
				$s .= "<br /><a class='p_comp_sm' href='".$oProfile->GetCompanyProfileUrl()."' title='View ".$oProfile->GetCompanyName()." Profile >> '>".$oProfile->GetCompanyName()."</a></p>";
				$s .= "<p class='c_desc_sm'>".$oProfile->GetDescShort(120)."</p>";

				$s .= "</div>\n";
				$s .= "<b class=\"rbottom\"><b class=\"r4\"></b> <b class=\"r3\"></b> <b class=\"r2\"></b> <b class=\"r1\"></b></b>";
				$s .= "</div>\n";
				

			}

			$s .= "<div style='float: left; width: 100%'><a class='p_small' href='".$url."' title='View all Placements'>View All Placements >></a></div>";
		}
		return $s;
	}





	function GetPlacementCount($type,$id = null) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;

		switch($type) {
			case "ALL" :
				return $this->db->getFirstCell("SELECT count(*) from ".$_CONFIG['placement_table'].";");
				break;
			case "BY_COMPANY" :
				return $this->db->getFirstCell("SELECT count(*) as placement_count FROM ".$_CONFIG['placement_table']." p, ".$_CONFIG['company_table']." c WHERE p.company_id = $id AND p.company_id = c.id");
				break;
			case "BY_CATEGORY" :
				$this->db->query("select cat.name,count(p.id) as count from category cat, comp_cat_map cc, ".$_CONFIG['company_table']." c, ".$_CONFIG['placement_table']." p where cat.id = cc.category_id and cc.company_id = c.id and c.id = p.company_id group by cat.name order by cat.name asc;");
				return $this->db->getObjects();
				break;
			case "BY_CATEGORY_COMPANY" :
				$this->db->query("select c.title, count(distinct(p.id)) from ".$_CONFIG['company_table']." c, ".$_CONFIG['placement_table']." p where p.company_id = c.id group by c.title order by c.title asc;");
				return $this->db->getObjects();
				break;
		}
	}



	function GetProfileByCategory($aCatId,$sSort = "RANDOM()", $limit = 3) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;
		
		$sql = "SELECT 
					c.id as category
					,c.name as category_name
					,p.id 
					,p.title
					,p.desc_short
					,p.url_name
					,comp.title as company_name
					,comp.id as company_id
					,comp.prod_type
					,comp.url_name as comp_url_name
				FROM 
					category c, 
					".$_CONFIG['placement_table']." p, 
					prod_cat_map m, 
					".$_CONFIG['company_table']." comp
				WHERE 
					c.id IN (".implode(',',$aCatId).")
					AND c.id = m.category_id 
					AND p.company_id = comp.id  
					AND m.prod_id = p.id
				ORDER BY ".$sSort.";";

		//print $sql;
		
		
		$this->db->query($sql);
		
		$aRes = $this->db->getObjects();		

		//Logger::Msg($aRes);
		
		$oCategory = new Category($this->db);
		$aCat = $oCategory->GetAll();

		$aIdByCategory = array();
		
		foreach($aCat as $k => $v) {
			$aIdByCategory[$k] = array();
		}

		/* an array of id's to ensure there are no duplicate placements 
		 * 
		 * the sql gets a row for each placement in each category duplicated foreach placement image 
		 * 
		 * the filter below removes the duplicates
		 * 
		 * we do it this way for performance (ie no additional select to get placement images)
		 *  
 		*/
		$aId = array();

		/*
		 * similarly we should only display X per company 
		 * to prevent comps with large volume of listings dominating
		 * 
		 * $aCompId = array(comp_id => count);
		 * 
		 */ 
		$aCompId = array();
		$iMaxPerComp = 1;
		$iMaxPerSponsoredComp = 2;
		
		$aTest =array();
		
		if (!is_array($aId)) return false;

		foreach ($aRes as $o) {
			
			
			if ($this->FreqByCompLimitCheck($o,$aCompId,$iMaxPerComp,$iMaxPerSponsoredComp)) continue;
			
			switch($o->category) {
								
				case "6" :
					if (!in_array($o->id,$aId)) {
						$aIdByCategory[6][] = $o;
						//$aTest[$o->company_name]++; 
					}
					break;
				case "3" :
					if (!in_array($o->id,$aId)) {
						$aIdByCategory[3][] = $o;
						//$aTest[$o->company_name]++;
					}
					break;
				case "4" :
					if (!in_array($o->id,$aId)) {
						$aIdByCategory[4][] = $o;
						//$aTest[$o->company_name]++;
					}
					break;
				case "2" :
					if (!in_array($o->id,$aId)) {
						$aIdByCategory[2][] = $o;
						//$aTest[$o->company_name]++;
					}
					break;
				case "0" :
					if (!in_array($o->id,$aId)) {
						$aIdByCategory[0][] = $o;
						//$aTest[$o->company_name]++;
					}
					break;
				case "1" :
					if (!in_array($o->id,$aId)) {
						$aIdByCategory[1][] = $o;
						//$aTest[$o->company_name]++;
					}
					break;
			}
			$aId[] = $o->id; 
		}
		
		//Logger::Msg($aTest);
		
		$aProfile = array();
		
		
		
		
		foreach($aCat as $k => $v) {
			
			//array_reverse($aIdByCategory[$k]);
			
			$aIdByCategory[$k] = array_slice($aIdByCategory[$k],0,$limit);

			if ($sSort != "RANDOM()") shuffle($aIdByCategory[$k]);
			
			for($i=0;$i<count($aIdByCategory[$k]);$i++) {
				$oProfile = new PlacementProfile();
				$oProfile->SetFromObject($aIdByCategory[$k][$i]);
				$oProfile->GetImages();
				$oProfile->GetCountryInfo();
				$aProfile[$k][] = $oProfile;		
			}			
		}
		
		return $aProfile;
	}

	
	function GetProfileByContinent($aContinentId,$sSort = "RANDOM()", $limit = 3) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;
		
		$sql = "SELECT 
					c.id as continent_id
					,c.name as continent_name
					,p.id
					,p.title
					,p.desc_short
					,p.url_name
					,comp.title as company_name
					,comp.id as company_id
					,comp.prod_type
					,comp.url_name as comp_url_name
				FROM 
					continent c,
					country cty,
					".$_CONFIG['placement_table']." p, 
					prod_country_map m, 
					".$_CONFIG['company_table']." comp
				WHERE 
					c.id IN (".implode(',',$aContinentId).")
					AND c.id = cty.continent_id
					AND cty.id = m.country_id 
					AND p.company_id = comp.id  
					AND m.prod_id = p.id
				ORDER BY ".$sSort.";";
		
		$this->db->query($sql);
		
		$aRes = $this->db->getObjects();		

		//Logger::Msg($aRes);
		
		$aIdByContinent = array();
		
		foreach($aContinentId as $k => $v) {
			$aIdByContinent[$v] = array();
		}
		

		/* an array of id's to ensure there are no duplicate placements 
		 *  
 		*/
		$aId = array();

		/*
		 * similarly we should only display X per company 
		 * to prevent comps with large volume of listings dominating
		 * 
		 * $aCompId = array(comp_id => count);
		 * 
		 */ 
		$aCompId = array();
		$iMaxPerComp = 1;
		$iMaxPerSponsoredComp = 3;
		
		$aTest =array();
		
		if (!is_array($aId)) return false;

		foreach ($aRes as $o) {
			
			
			if ($this->FreqByCompLimitCheck($o,$aCompId,$iMaxPerComp,$iMaxPerSponsoredComp)) continue;
			
			switch($o->continent_id) {
 
				case "7" :
					if (!in_array($o->id,$aId)) {
						$aIdByContinent[7][] = $o;
						//$aTest[$o->company_name]++; 
					}
					break;
				case "6" :
					if (!in_array($o->id,$aId)) {
						$aIdByContinent[6][] = $o;
						//$aTest[$o->company_name]++; 
					}
					break;
  				case "5" :
					if (!in_array($o->id,$aId)) {
						$aIdByContinent[5][] = $o;
						//$aTest[$o->company_name]++; 
					}
					break;
				case "4" :
					if (!in_array($o->id,$aId)) {
						$aIdByContinent[4][] = $o;
						//$aTest[$o->company_name]++; 
					}
					break;
  				case "3" :
					if (!in_array($o->id,$aId)) {
						$aIdByContinent[3][] = $o;
						//$aTest[$o->company_name]++; 
					}
					break;  
  				case "2" :
					if (!in_array($o->id,$aId)) {
						$aIdByContinent[2][] = $o;
						//$aTest[$o->company_name]++; 
					}
					break;  
  				case "1" :
					if (!in_array($o->id,$aId)) {
						$aIdByContinent[1][] = $o;
						//$aTest[$o->company_name]++; 
					}
					break;  
				case "8" :
					if (!in_array($o->id,$aId)) {
						$aIdByContinent[8][] = $o;
						//$aTest[$o->company_name]++; 
					}
					break;
					
			}
			$aId[] = $o->id; 
		}
		
		//Logger::Msg($aIdByContinent[7]);
		//Logger::Msg($aContinentId);
		//die();
		
		$aProfile = array();
		
		foreach($aContinentId as $k => $v) {
			
			//array_reverse($aIdByCategory[$k]);
			
			$aIdByContinent[$v] = array_slice($aIdByContinent[$v],0,$limit);

			if ($sSort != "RANDOM()") shuffle($aIdByContinent[$v]);
			
			for($i=0;$i<count($aIdByContinent[$v]);$i++) {
				$oProfile = new PlacementProfile();
				$oProfile->SetFromObject($aIdByContinent[$v][$i]);
				$oProfile->GetImages();
				$oProfile->GetCountryInfo();
				$aProfile[$v][] = $oProfile;
			}			
		}
		
		return $aProfile;
	}
	
	
	
	/*
	 * Get a list of placement id's for featured projects page
	 * 
	 * List is order by added date
	 * 
	 * Only placements with images can appear
	 * 
	 * A sponsored comp profile can appear max 2 times 
	 * A basic / enhanced profile can appear max 1 time 
	 * 
	 */
	public function GetFeaturedIdList() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG,$db;

		
		/*
		 * Just get the placements with images
		 * this SQL returns duplicate row (per image)
		 * but these are filtered in the duplicate check below
		 * 
		 */
		$sql = "SELECT 
					p.id 
					,comp.id as company_id
					,comp.prod_type
				FROM  
					".$_CONFIG['placement_table']." p, 
					".$_CONFIG['company_table']." comp,
					".$_CONFIG['image_map']." im,
					".$_CONFIG['image']." i
				WHERE 
					p.company_id = comp.id
					AND im.link_id = p.id  
					AND im.link_to = 'PLACEMENT'
					AND im.img_id = i.id
				ORDER BY  
					RANDOM();";
				
		$db->query($sql);		
		$aRes = $db->getObjects();		
		
		$aId = array();/* filtered id list result container */		

		$aDupChk = array();
		
		$aCompId = array();
		$iMaxPerComp = 1;
		$iMaxPerSponsoredComp = 4; /* greater weighting to sponsored comps */
		
		if (!is_array($aId)) return false;

		foreach ($aRes as $o) {
			
			if ($this->FreqByCompLimitCheck($o,$aCompId,$iMaxPerComp,$iMaxPerSponsoredComp)) continue;
			
			//$aId[$o->company_id][] = array("id" => $o->id);

			if (!in_array($o->id,$aDupChk)) { /* ensure there are no duplicates */
				$aId[] = $o->id;
			}
			$aDupChk[] = $o->id;
		}		
		return $aId;
	}
	
	
	private function FreqByCompLimitCheck($oResult,&$aCompId,$iMaxPerComp,$iMaxPerSponsoredComp) {

		//Logger::Msg(":::");
		//Logger::Msg($oResult->company_id);
		//Logger::Msg($oResult->prod_type);
		//Logger::Msg($aCompId);
		
		if (array_key_exists($oResult->company_id,$aCompId)) {
			$aCompId[$oResult->company_id]++;
		} else {
			$aCompId[$oResult->company_id] = 0;
		}
		
		//Logger::Msg($aCompId[$oResult->company_id]);
		
		
		if ($oResult->prod_type == SPONSORED_LISTING) {
			if ($aCompId[$oResult->company_id] >= $iMaxPerSponsoredComp) return true;
		} else {
			if ($aCompId[$oResult->company_id] >= $iMaxPerComp) return true;
		}
	
	}

	function GetPlacementCountByCategory() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		return $this->renderTable($this->GetPlacementCount("BY_CATEGORY"));
	}

	function GetPlacementCountByCompany() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		return $this->renderTable($this->GetPlacementCount("BY_CATEGORY_COMPANY"));
	}

	function hit($id,$hits) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;
		
		$hits++;
		return $this->db->query("update ".$_CONFIG['placement_table']." set hits = $hits where id = $id;");
	}


	function renderTable($arr) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		$s = "<table cellpadding='2' cellspacing='2' border='0'>";
		foreach ($arr as $c) {
			$s .= "<tr>";
			foreach($c as $p) {
				$s .= "<td>".$p."</td>";
			}
			$s .= "</tr>";
		}
		$s .= "</table>";
		return $s;
	}

	
	
	// param: array of placement objects
	Function renderPlacementEditList($aPlacement,$credits) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");


		$s = "<table cellpadding='2' cellspacing='0' border='0'>";
		$i = 1;
		if (is_array($aPlacement)) {
			$s .= "<tr><th>&nbsp;</th><th>&nbsp;</th><th><b>Title</th><th>Description</th><th>View</th><th>Edit</th><th>Delete</th></tr>\n";
			$idx =0;
			foreach ($aPlacement as $p) {
				
				$oProfile = new PlacementProfile();
				$oProfile->SetFromArray($p);
				
				
				$class = ($class == "hi") ? "" : "hi";

				$s .= "<tr class='$class'>\n";
				$s .= "<td>".$i."</td>\n";

				//$aImg = $this->GetPlacementThumbnail($p->id,1);
				if ((count($aImg) == 1)) {
					$s .= "<td><a class='std' href='".$_CONFIG['url']."/company/".$p->comp_url_name."/".$p->url_name."' title='".$p->company_name."'><img src='".$sImgUrl."' alt='' border='' /></a></td>";
				}

				$s .= "<td>".stripslashes($p->title)."</td>\n";
				$s .= "<td width='340px'>".stripslashes($p->desc_short)."</td>\n";
				$s .= "<td><a href=\"".$_CONFIG['url']."/company/".$p->comp_url_name."/".$p->url_name."/\" target=\"_new\">view</a></td>\n";
				$s .= "<td><a href=\"".$_CONFIG['url']."/company/".$p->comp_url_name."/".$p->url_name."/edit/\" target=\"_new\">edit</a></td>\n";
				$s .= "<td><a href=\"".$_CONFIG['url']."account.php?&d&p=".$p->id."&c=".$p->company_id."\" onclick=\"javascript: return confirm('This will permanently delete the placement.  Are you sure?')\">delete</a></td>\n";
				$s .= "</tr>\n";
				$i++;
			}
		}
		$s .= "</table>";
		return $s;
	}

	
	function GetRelatedPlacements($sql) {

		global $db,$_CONFIG;
		
		// get matching PLACEMENTS
		$db->query($sql);
		$arr = $db->getRows();
		
		if (is_array($arr)) {
			foreach($arr as $r) {
				$aId[] = $r['id'];
			}
			
			if (count($aId) > $_CONFIG['results_per_page']) {
				$oPager2 = new PagedResultSet();
				$oPager2->GetFromArray($aId,"p2_");
				$sPagerHtml = $oPager2->getPageNav($_REQUEST['clean_url']."/");
				$aId = array_slice($aId,$oPager2->iResultOffset,$_CONFIG['results_per_page']);
			}
	
			return $this->GetPlacementById($aId,$key = "id_list",$ret_type = "rows");	
		}
	}

	function getPlacementCredits($id) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;
		
		return $this->db->getFirstCell("SELECT job_credits FROM ".$_CONFIG['company_table']." WHERE id = $id");
	}

	function GetExtraInfo($aPlacementIn) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		$aPlacementOut = array();
		foreach($aPlacementIn as $c) {
			$this->GetActivityInfo($c);			
			$this->GetCategoryInfo($c);
			$aPlacementOut[] = $c;
		}
		return $aPlacementOut;
	}


	function GetActivityInfo(&$aPlacement) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$oActivity = new Activity($this->db);
		$arr = $oActivity->GetActivitiesById($aPlacement['id'],"placement_id");

		
		unset($sText);
		for ($i=0; $i<count($arr);$i++) {
			unset($comma);
			$comma = ($i < (count($arr) -1)) ? " / " : "";
			$sText .= $arr[$i]['name'] . $comma;
			$aId[] =  $arr[$i]['id'];
		}		
				
		$aPlacement['activity_txt'] = $sText;		
		$aPlacement['activity_array'] = $aId;
	}



	function GetCategoryInfo(&$aPlacement) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		$oCategory = new Category($this->db);
		$arr = $oCategory->GetCategoriesById($aPlacement['id'],"placement_id");
				
		unset($sText);
		for ($i=0; $i<count($arr);$i++) {
			unset($comma);
			$comma = ($i < (count($arr) -1)) ? " / " : "";
			$sText .= $arr[$i]['name'] . $comma;
			$aId[] =  $arr[$i]['id'];
		}
		$aPlacement['category_txt'] = $sText;
		$aPlacement['category_array'] = $aId;
	}


	function RandomNumber($limit) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		srand((double)microtime()*1000000);
		return rand(0,$limit);

	}

	function deletePlacement($id) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;
		
		$this->db->query("SELECT id FROM ".$_CONFIG['placement_table']." WHERE id = $id");
		if ($this->db->getNumRows() == 1) {
			$this->db->query("DELETE FROM prod_cat_map WHERE prod_id = $id");
			$this->db->query("DELETE FROM prod_act_map WHERE prod_id = $id");
			$this->db->query("DELETE FROM keyword_idx_2 WHERE type = 2 AND id = $id");
			$this->db->query("DELETE FROM ".$_CONFIG['profile_hdr_table']." WHERE id = $id");
		}
	}

	function getCompanyIdByPlacement($id) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		if (!is_numeric($id)) return;
		
		global $_CONFIG;
		
		return $this->db->getFirstCell("SELECT company_id FROM ".$_CONFIG['placement']." WHERE id = $id");
	}


	public function GetRecentPlacementHTML($iLimit = 3) {


		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db,$_CONFIG,$oPlacement;

		
		$oCategory = new Category($db);
		$aCategory = $oCategory->GetCategoriesByWebsite($_CONFIG['site_id'],$return = "OBJECTS");

		$sSort = "RANDOM()";
		
		if ($_CONFIG['single_cat_site']) {
			$singleCategorySite = true;
			$iLimit = 12;
			$sSort = "p.added DESC";
		}
		
		
		foreach($aCategory as $o) {
			$aCatId[] = $o->id;
		}
		
		$aProfile = $oPlacement->GetProfileByCategory($aCatId,$sSort,$iLimit);

	
		
		if ($_CONFIG['single_cat_site']) {
			foreach($aCategory as $oCategory) {

				if (!is_array($aProfile[$oCategory->id])) continue;
				
				foreach($aProfile[$oCategory->id] as $oProfile) {
					if (is_object($oProfile)) {
						$a[] = $oProfile;
					}
				}
			}
			shuffle($a);
			$aProfile = $a;
		}
		
		
		/*
		 * Two rendering layouts are available 
		 * 	- single column ($singleCategorySite == true)
		 *  - two column
		 * 
		 */
		if (!$singleCategorySite) {
			
			$s .= "<div id='main_placement'>";

			$aStyles = array(
							0 => "rp_01",
							1 => "rp_02",
							2 => "rp_03",
							3 => "rp_04",
							4 => "rp_05",
							5 => "rp_06");
				
			$i = 0;
				
			foreach($aCategory as $oCategory) {

				
				
				$s .= "<div id='".$aStyles[$i++]."'> <!-- begin category -->";
				
				$s .= "<h1 class='p_cat_title'><a class='p_cat_title_link' href='".$_CONFIG['url'].$_CONFIG['category_home']."/".$oCategory->url_name."' title='".$oCategory->name."'>Gap Year ".$oCategory->name."</a> <i><a class='recent_placement_more' href='".$_CONFIG['url'].$_CONFIG['category_home']."/".$oCategory->url_name."' title='View all ".$oCategory->name."'>view all >></a></i></h1>";

				if (!is_array($aProfile[$oCategory->id])) continue;
				
				foreach($aProfile[$oCategory->id] as $oProfile) {

					$s .= "<div id='recent_placement_container'> <!-- begin recent placement container -->";
					$s .= "<b class=\"rtop\"><b class=\"r1\"></b> <b class=\"r2\"></b> <b class=\"r3\"></b> <b class=\"r4\"></b></b>";
					$s .= "<div id='recent_placement'> <!-- begin recent placement item -->";

					if (is_object($oProfile->GetImage(0))) {
						$s .= "<span id='recent_placement_img'><a title='".$oProfile->GetTitle() ." : " . $oProfile->GetCompanyName() . " : ".$oProfile->GetCountryTxt() ."' href=\"".$oProfile->GetProfileUrl()."\">".$oProfile->GetImage(0)->GetHtml("_s","")."</a></span>";
					}

					$s .= "<a class='p_title_sm' title='".$oProfile->GetTitle() ." : " . $oProfile->GetCompanyName() . " : ".$oProfile->GetCountryTxt() ."' href=\"".$oProfile->GetProfileUrl()."\" />".$oProfile->GetTitle(45)."</a>";
					$s .= "<br><a class='p_comp_sm' title='View " . $oProfile->GetCompanyName() . " Profile >>' href=\"".$oProfile->GetProfileUrl()."/\">".$oProfile->GetCompanyName()."</a>";
					if (strlen($oProfile->GetCountryTxt()) < 20) {
						$s .= "<p class='p_cty_sm'>".$oProfile->GetCountryTxt()."</p>";
					}
					$s .= "<p class='recent_placement_desc'>".$oProfile->GetDescShort(120)."</p>";
					
					$s .= "</div> <!-- end recent placement item -->";
					$s .= "<b class=\"rbottom\"><b class=\"r4\"></b> <b class=\"r3\"></b> <b class=\"r2\"></b> <b class=\"r1\"></b></b>";	
					$s .= "</div> <!-- end recent placement container -->";

				}
				$s .= "</div> <!-- end category -->";
			}
			$s .= "</div>";
		}

		/* display recent placements in a full width panel */
		if ($singleCategorySite) {

			$s .= "<div id='rplacement_wide'>";

			$title = "Recently Added <i><a class='viewall' href='".$_CONFIG['url']."/placement/' title='View All'>View All >></a></i>";
			$s .= "<h1 id='rplacement_title' class='title1'>".$title."</h1>";
						
				
			foreach($aProfile as $oProfile) {
				
				if ($i++ >= $iLimit) continue;
				
				$s .= "\n<div id='rplacement_item_container'>";
				$s .= "<b class=\"rtop\"><b class=\"r1\"></b> <b class=\"r2\"></b> <b class=\"r3\"></b> <b class=\"r4\"></b></b>";
				$s .= "<div id=\"rplacement_item\">";					

				$s .= "<a class='p_title_sm' title='".$oProfile->GetTitle() ." : " . $oProfile->GetCompanyName() . " : ".$oProfile->GetCountryTxt() ."' href=\"".$oProfile->GetProfileUrl()."\" />".$oProfile->GetTitle(45)."</a>";					
				$s .= "<br><a class='p_comp_sm' title='View " . $oProfile->GetCompanyName() . " Profile >>' href=\"".$oProfile->GetProfileUrl()."/\">".$oProfile->GetCompanyName()."</a>";
				
				
				if (is_object($oProfile->GetImage(0))) {
					$s .= "<span id='recent_placement_img'><a title='".$oProfile->GetTitle() ." : " . $oProfile->GetCompanyName() . " : ".$oProfile->GetCountryTxt() ."' class=\"std\" href=\"".$oProfile->GetProfileUrl()."\">".$oProfile->GetImage(0)->GetHtml("_s","")."</a></span>";
				}					
				
				if (strlen($oProfile->GetCountryTxt()) < 20) {
					$s .= "<p class='p_cty_sm'>".$oProfile->GetCountryTxt()."</p>";
				}
				
				$s .= "<p class='p_small'>".$oProfile->GetDescShort(120)."</p>";

				$s .= "</div> <!-- end rplacement item -->";
				$s .= "<b class=\"rbottom\"><b class=\"r4\"></b> <b class=\"r3\"></b> <b class=\"r2\"></b> <b class=\"r1\"></b></b>";					
				$s .= "</div> <!-- end rplacement item container -->\n\n";
			}

			$s .= "</div><!-- end rplacement wide -->";
		}
		
		return $s;

	} // end GetRecentPlacementHTML()

	
	public function SearchByActivityCountry($iActivityId,$iCountryId) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db,$_CONFIG;

		$db->query("SELECT p.id,p.title,p.");
		
	}

	public function GetPlacementSearchResultHTML($aPlacement) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
				
		if (!is_array($aPlacement)) return false;
		
		$s .= "<div id='rplacement_wide'>";

		foreach($aPlacement as $p) {

			$s .= "<div id='rplacement_item_container'>";
			$s .= "<b class=\"rtop\"><b class=\"r1\"></b> <b class=\"r2\"></b> <b class=\"r3\"></b> <b class=\"r4\"></b></b>";
			$s .= "<div id=\"rplacement_item\">";
			$s .= "<span id='rplacement_img'><a title='".$p->title ." : " . $p->company_name . " : ".$p->country ."' href=\"".$_CONFIG['url']."/company/".$p->comp_url_name."/".$p->url_name."\"><img src='http://www.oneworld365.org/images/client_image/img_".$p->img_thumb_id."_s".$p->img_thumb_ext."' alt=' ' border=''></a></span>";
			
			if (strlen($p->title) > 36) {
				$p->title = $p->title." ";
				$p->title = substr($p->title,0,36);
				$p->title = substr($p->title,0,strrpos($p->title,' '));
				$p->title = $p->title."...";
			}


			$s .= "<a class='p_title_sm' title='".$p->title ." : " . $p->company_name ."' href=\"".$_CONFIG['url']."/company/".$p->comp_url_name."/".$p->url_name."\">".stripslashes($p->title)."</a>";
			$s .= "<br><a class='p_comp_sm' title='View " . $p->company_name . " Profile >>' href=\"".$_CONFIG['url']."/company/".$p->comp_url_name."/\">".$p->company_name."</a>";
			$s .= "<p class='p_cty_sm'>".$p->country."</p>";

			if (strlen($p->desc_short) > 65) {
				$p->desc_short = $p->desc_short." ";
				$p->desc_short = substr($p->desc_short,0,125);
				$p->desc_short = substr($p->desc_short,0,strrpos($p->desc_short,' '));
				$p->desc_short = $p->desc_short."...";
			}

			$s .= "<p class='p_desc_sm'>".stripslashes($p->desc_short)."</p>";

			$s .= "</div> <!-- end rplacement item -->";
			
			$s .= "<b class=\"rbottom\"><b class=\"r4\"></b> <b class=\"r3\"></b> <b class=\"r2\"></b> <b class=\"r1\"></b></b>";
			$s .= "</div> <!-- end rplacement item container -->";
		}

		$s .= "</div>";
		
	}
	
}// end placement class

?>
