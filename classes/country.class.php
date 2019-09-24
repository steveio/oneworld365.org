<?

class Country {
	
	private $id;
	private $continent_id;
	private $name;
	private $count;
	private $url_name;
	private $description;
	private $hostelbooker_id;		

	public function __construct()
	{
	    $this->Country();
	}

	/* @param depreciated $db */
	function Country($db = NULL) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
	}
	
	public function GetId() { 
		return $this->id;
	}

	public function SetId($id) { 
		$this->id = $id;
	}

	public function GetContinentId() { 
		return $this->continent_id;
	}

	public function SetContinentId($id) { 
		$this->continent_id = $id;
	}	
	
	public function GetName() { 
		return stripslashes($this->name);
	}

	public function SetName($name) { 
		$this->name = $name;
	}

	public function GetCount() { 
		return stripslashes($this->count);
	}

	public function SetCount($count) { 
		$this->count = $count;
	}	

	public function GetUrl() {
		global $_CONFIG; 
		return $_CONFIG['url'] ."/country/". $this->url_name;
	}
	
	public function GetUrlName() { 
		return $this->url_name;
	}

	public function SetUrlName($url_name) { 
		$this->url_name = $url_name;
	}

	public function GetDesc() { 
		return stripslashes($this->description);
	}

	public function SetDesc($description) { 
		$this->description = $description;
	}	
	
		
	public function GetAll() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db,$_CONFIG;

		$db->query("SELECT id,name,continent_id,url_name FROM country ORDER BY name ASC");

		return $db->getObjects();		
	}



	public function GetAllWithPlacements() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db,$_CONFIG;
		
		$db->query("SELECT 
						c.id, 
						c.name,
						c.url_name,
						c.continent_id,
						f.freq as count 
					FROM 
						country c,
						prod_country_map m ,
						".$_CONFIG['placement_table']." p,
						proj_freq_matrix f 
					WHERE 
						c.id = m.country_id
						AND m.prod_id = p.id
						AND f.sid = ".$_CONFIG['site_id']."
						AND f.c1_type = 1
						AND f.c1_id = c.id
						AND f.freq >= 1
						AND c2_id is null 
					GROUP BY 
						c.name,c.id,
						c.continent_id, 
						c.url_name,
						f.freq
					ORDER BY 
						name ASC;");
		
		return $aCountry= $db->getObjects();
		
		
		
		
	}
	
	function GetCountries() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$sql = "SELECT id,name FROM country order by name asc;";
		$db->query($sql);
		$aResult = $db->getRows();

		foreach ($aResult as $aRow) {
			$aNew[$aRow['id']]['id'] =  $aRow['id'];
			$aNew[$aRow['id']]['name'] =  $aRow['name'];
		}

		return $this->aCountry = $aNew;
	}


	public function GetById($iId) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		if (!is_numeric($iId)) return false;
		
		$db->query("SELECT id,name,url_name FROM country WHERE id = ".$iId.";");
		
		$oRes = $db->getObject();
		$oRes->name = stripslashes($oRes->name);
		
		return $oRes;
				
	}
	
	public function GetByName($sName) {
	
		global $db;
	
		// some of the names have a trailing whitespace so has to be LIKE!
		$sql = "SELECT id,name,url_name FROM country WHERE name LIKE '".addslashes($sName)."%';";
		$db->query($sql);
	
		if ($db->getNumRows() == 1)
		{
			$oRes = $db->getObject();
			$oRes->id = $oRes->id;
			$oRes->name = stripslashes($oRes->name);
			$oRes->url_name = $oRes->url_name;
			return $oRes;
		}
	}
	
	
	/* lookup hostelbookers country id from a oneworld365 id */
	public function SetHostelBookerId($country_id) {

		global $db;

		if (!is_numeric($country_id)) return FALSE;
		
		$sql = "SELECT hostelbooker_id FROM hostelbooker_map WHERE country_id = ".$country_id;

		$db->query($sql);
		
		if ($db->getNumRows() == 1) {
			$row = $db->getRow();
			$this->hostelbooker_id = $row['hostelbooker_id'];
			return TRUE;
		}
	
	}
	
	public function GetHostelBookerId() {
		return $this->hostelbooker_id;
	}
	
	
	public function GetByUrlName($url_name) {

		global $db;

		$sql = "SELECT id,name,url_name,continent_id FROM country WHERE url_name = '".$url_name."'";
		
		$db->query($sql);
		
		if ($db->getNumRows() == 1) {
			$row = $db->getRow();
			$this->id = $row['id'];
			$this->continent_id = $row['continent_id'];
			$this->name = $row['name'];
			$this->url_name = $row['url_name'];	
			return TRUE;
		}
	
	}

	
	function GetCountriesById($id,$type = 'company_id') {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;
		
		if ($type == "company_id") {
			$sTable = "comp_country_map m";	
			$sKey = "m.company_id";
		} elseif ($type == "placement_id") {
			$sTable = "prod_country_map m";
			$sKey = "m.prod_id";
		}
		
		$sql = "SELECT a.id,a.name,ct.id as continent_id,ct.name as continent FROM ".$sTable.", country a, continent ct WHERE ".$sKey." = $id AND m.country_id=a.id and a.continent_id = ct.id ORDER BY a.name ASC;";
		
		$db->query($sql);
		return $db->getRows();
	}

	public function GetByContinentId($continent_id) {
		
		global $db;

		$sql = "SELECT c.id,c.name FROM country c, continent ct WHERE c.continent_id = ct.id and ct.id = ".$continent_id." ORDER BY c.name ASC;";
		
		$db->query($sql);
		return $db->getRows();
		
	}
	
	public function GetByContinentName($continent_name) {
	
		global $db;
	
		$sql = "SELECT c.id,c.name FROM country c, continent ct WHERE c.continent_id = ct.id and ct.name = '".$continent_name."' ORDER BY c.name ASC;";
	
		$db->query($sql);

		return $db->getRows();
	
	}
	
	
	function GetCountryLinkList($mode = "post",$aSelected = array(),$slash = true) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$aCountries = $this->GetCountries();

		$idx = 0;
		unset($ct_text);
		// get country text links
		foreach($aCountries as $c) {
			$link = $_SERVER['PHP_SELF']  ."?s=country&id=".$c['id']."";
			if ($slash == true) {
				$delimeter = ($idx < count($aCountries) -1) ? " / " : " " ;
			}
			if ($mode == "input") {
				$checked = (in_array($c['id'],$aSelected)) ? "checked" : "";
			} else {
				$checked = ($_POST['cty_'.$c['id']] == "on") ? "checked" : "";
			}
			$ct_text .= "<label class='select_list'>". $c['name'] . " </label><input class='select_list' type='checkbox' name='cty_".$c['id']."' $checked /> $delimeter ";
			$idx++;
		}
		return  $ct_text;
	}

	function GetCountryDropDown($selected,$name = 'p_country_id') {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$db->query("SELECT id,name FROM country ORDER BY name asc");
		$result = $db->getRows();
		$s = "<select name='".$name."' class='ddlist'>";
		$s .= "<option value='NULL'>select</option>";
		if (is_array($result)) {
			foreach($result as $row) {
				$chk = (trim($row['id']) == $selected) ? "selected" : "";
				$s .= "<option value='".$row['id']."' $chk>".$row['name']."</option>";
			}
		}
		$s .= "</select>";
		return $s;
	}


	function GetDDList($selected,$name = 'p_country_id',$continent_id = null,$iActivityId = null) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db,$_CONFIG;

		
		
		if ((is_numeric($iActivityId)) && (!is_numeric($continent_id))) {
			
			/* return all countries w/ activity */
			$sql = "SELECT 
						c.id, 
						c.name,
						f.freq as count			
					  FROM 
						country c,
						prod_country_map m, 
						".$_CONFIG['profile_hdr_table']." p,
						".$_CONFIG['company_table']." comp,
						proj_freq_matrix f
					  WHERE 
					  	c.id = m.country_id
					  	AND m.prod_id = p.id
					  	AND p.company_id = comp.id
						AND f.sid = ".$_CONFIG['site_id']."
						AND f.c1_type = 0
						AND f.c1_id = ".$iActivityId."
						AND c2_type = 1
						AND c.id = f.c2_id
						AND f.freq >= 1
					  GROUP BY
					    c.id,
					    c.name,
					    f.freq
					   ORDER BY name ASC;";

		} elseif ((is_numeric($iActivityId)) && (is_numeric($continent_id))) {
			
			/* return all countries in continent w/ activity */
			$sql = "SELECT 
						c.id, 
						c.name,
						f.freq as count			
					  FROM 
						country c,
						prod_country_map m, 
						".$_CONFIG['profile_hdr_table']." p,
						".$_CONFIG['company_table']." comp,
						proj_freq_matrix f
					  WHERE 
					  	c.id = m.country_id
					  	AND c.continent_id = ".$continent_id."
					  	AND m.prod_id = p.id
					  	AND p.company_id = comp.id
						AND f.sid = ".$_CONFIG['site_id']."
						AND f.c1_type = 0
						AND f.c1_id = ".$iActivityId."
						AND c2_type = 1
						AND c.id = f.c2_id
						AND f.freq >= 1
					  GROUP BY
					    c.id,
					    c.name,
					    f.freq
					   ORDER BY name ASC;";
			
		
		} elseif ((is_numeric($continent_id)) && (!is_numeric($activity_id))) {
			$sql = "SELECT 
						c.id, 
						c.name,
						f.freq as count			
					  FROM 
						country c,
						prod_country_map m, 
						".$_CONFIG['profile_hdr_table']." p,
						".$_CONFIG['company_table']." comp,
						proj_freq_matrix f
					  WHERE 
					  	c.continent_id = ".$continent_id."
					  	AND c.id = m.country_id
					  	AND m.prod_id = p.id
					  	AND p.company_id = comp.id
						AND f.sid = ".$_CONFIG['site_id']."
						AND f.c1_type = 1
						AND f.freq >= 1
						AND c.id = f.c1_id
						AND c2_id is null
					  GROUP BY
					    c.id,
					    c.name,
					    f.freq
					   ORDER BY name ASC;";
		} else { /* just the country count */
			$sql = "SELECT 
						c.id, 
						c.name,
						f.freq as count
					FROM 
						country c,
						prod_country_map m, 
						".$_CONFIG['placement_table']." p,
						".$_CONFIG['company_table']." comp,
						proj_freq_matrix f
					WHERE 
						c.id = m.country_id
						AND m.prod_id = p.id 
						AND p.company_id = comp.id
						AND f.sid = ".$_CONFIG['site_id']."
						AND f.c1_type = 1
						AND f.freq >= 1
						AND c.id = f.c1_id
						AND c2_id is null						
					GROUP BY
						c.id, 
						c.name,
						f.freq
					ORDER by name ASC;";
		}

		$db->query($sql);
		
		$result = $db->getRows();
		
		$s = "<select name='".$name."' class='ddlist'>";
		$s .= "<option value='NULL'>select</option>";
		if(is_array($result)) {
			foreach($result as $row) {
				$chk = (trim($row['id']) == $selected) ? "selected" : "";
				$s .= "<option value='".$row['id']."' $chk>".$row['name']." (".$row['count'].")</option>";
			}
		}
		$s .= "</select>";
		return $s;
	}
	
	
	/* single column */
	public function GetSelectListHTML() {

		global $db,$_CONFIG;
		
		// display the country select list
		$db->query("SELECT c.id, c.name,c.url_name,count(*) FROM country c, comp_country_map m, ".$_CONFIG['company_table']." comp WHERE m.country_id = c.id AND m.company_id = comp.id GROUP BY c.name,c.id,c.url_name ORDER by name ASC;");
		$arr = $db->getObjects();
		$s .= "<div style='position: relative; float: left; width: 300px; margin: 0px 0px 0px 0px; '>";
		$s .= "<h1><a class='title_blue' href='./country/' title='Find opportunities by country.'>By Country : </a></h1>";
		if (is_array($arr)) {
			foreach ($arr as $c) {
				$txt = '';
				if (is_array($_CONFIG['txt_pattern_country'])) {
					$i = 0;
					foreach($_CONFIG['txt_pattern_country'] as $pattern) {
						$delimeter = ($i++ < (count($_CONFIG['txt_pattern_country']) -1)) ? ", " : "";
						$txt .= sprintf($pattern,$c->name).$delimeter;
					}
				} else {
					$txt = sprintf($_CONFIG['txt_pattern_country'],$c->name);
				}
				$s .= "<div style='position: relative; float: left; width: 100px;'>";
				$s .= "<a class='std' href='".$_CONFIG['url']."/country/".$c->url_name."' title='$txt'>".stripslashes($c->name)."</a><span class='p_small'> (".$c->count.")</span><br>";
				$s .= "</div>";
			}
		}
		$s .= "</div>";
			
		return $s;
	}


	
	public static function GetSelectList() {
		
		global $db,$_CONFIG;
		
		$db->query("SELECT 
						c.id, 
						c.name,
						c.url_name,
						c.continent_id, 
						count(*) 
					FROM 
						country c, 
						".$_CONFIG['comp_country_map']." m 
					WHERE 
						m.country_id = c.id 
					GROUP BY 
						c.name,c.id,
						c.continent_id, 
						c.url_name 
					ORDER BY 
						name ASC;");
		$a = $db->getObjects();
		$aCountry = array();
		if (!is_array($a)) return array();
		foreach($a as $o) {
			$oCty = new Country();
    		foreach ($o as $key => $val) {
        		$oCty->$key = $val;
    		}
			$aCountry[] = $oCty;
		}		
		return $aCountry;		
	}
}

?>
