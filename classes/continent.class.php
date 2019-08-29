<?


class Continent {

	private $id;
	private $name;
	private $url_name;
	private $description;		

	public function __construct()
	{
	    $this->Continent();
	}

	/* @param depreciated $db */
	function Continent($db = NULL) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
	}

	public function GetId() { 
		return $this->id;
	}

	public function SetId($id) { 
		$this->id = $id;
	}

	public function GetName() { 
		return stripslashes($this->name);
	}

	public function SetName($name) { 
		$this->name = $name;
	}

	public function GetUrlName() { 
		return $this->url_name;
	}

	public function GetUrl() {
		global $_CONFIG; 
		return $_CONFIG['url'] ."/continent/". $this->url_name;
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
	
	/* older code expects std object */
	public function GetAll($ret = "STD") {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$db->query("SELECT id,name,url_name FROM continent ORDER BY name asc");
		if ($ret == "STD") {
			return $aContinent = $db->getObjects();
		} else {
			$a = $db->getObjects();
			$out = array();
			if (!is_array($a)) return array();
			foreach($a as $o) {
				$oCtn = new Continent();
	    		foreach ($o as $key => $val) {
	        		$oCtn->$key = $val;
	    		}
				$out[] = $oCtn;
			}
			return $out;
		}
		
	}
	
	public function GetById($iId) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		if (!is_numeric($iId)) return false;
		
		$db->query("SELECT id,name,url_name FROM continent WHERE id = ".$iId.";");
		
		$oRes = $db->getObject();
		$oRes->name = stripslashes($oRes->name);
		
		return $oRes;
				
	}
	
	public function GetByName($sName) {
	
		global $db;
	
		$db->query("SELECT id,name,url_name FROM continent WHERE name = '".addslashes($sName)."';");
	
		if ($db->getNumRows() == 1)
		{
			$oRes = $db->getObject();
			$oRes->id = $oRes->id;
			$oRes->name = stripslashes($oRes->name);
			$oRes->url_name = $oRes->url_name;
			return $oRes;
		}
		return false;
	}
	
	
	function GetContinents() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$sql = "SELECT id,name FROM continent order by name asc;";
		$db->query($sql);
		$aResult = $db->getRows();

		foreach ($aResult as $aRow) {
			$aNew[$aRow['id']]['id'] =  $aRow['id'];
			$aNew[$aRow['id']]['name'] =  $aRow['name'];
		}
		return $aNew;
	}


	function GetContinentLinkList($mode = "post",$aSelected = array(),$slash = false,$limit = false) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$aContinent= $this->GetContinents();

		$idx = 0;
		unset($ct_text);
		foreach($aContinent as $c) {
			
			$link = $_SERVER['PHP_SELF']  ."?s=country&id=".$c['id']."";
			if ($slash == true) {
				$delimeter = ($idx < count($aContinent)) ? " / " : " " ;
			}
			if ($mode == "input") {
				$checked = (in_array($c['id'],$aSelected)) ? "checked" : "";
			}
			if ($limit == true) $sOnlick = "onclick=\"javascript: checkOne('ctn_','ctn_".$c['id']."');\"";
			$ct_text .= $c['name'] . " <input type='checkbox' name='ctn_".$c['id']."' $sOnlick $checked /> $delimeter ";			
			$idx++;
		}		
		
		return  $ct_text;
	}

	
	function GetDDList($selected,$name = 'p_continent_id',$onchange = '',$iActivityId = null, $bCount = false) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db, $_CONFIG;
		
		if($bCount) {

			if (is_numeric($iActivityId)) {

				/* return continent w/ activity w/ project (count) */
				
				$db->query("SELECT 
										c.id,
										c.name,
										f.freq as count
									FROM 
										continent c,
										proj_freq_matrix f
									WHERE
										f.sid = ".$_CONFIG['site_id']."
										AND f.freq >= 1
										AND f.c1_type = 0
										AND f.c1_id = ".$iActivityId."
										AND c2_type = 2 
										AND c.id = f.c2_id
									ORDER BY 
										c.name ASC");
				
				
			} else {

				/* return continent w/ project (count) */
				
				$db->query("SELECT 
										c.id,
										c.name,
										f.freq as count
									FROM 
										continent c,
										proj_freq_matrix f
									WHERE
										f.sid = ".$_CONFIG['site_id']."
										AND f.c1_type = 2
										AND f.freq >= 1
										AND c.id = f.c1_id
										AND c2_id is null
									ORDER BY 
										c.name ASC");
				
			}
				
		} else {
			
			$db->query("SELECT 
									c.id,
									c.name
								FROM 
									continent c
								ORDER BY 
									c.name ASC");
			
		
		}
		$result = $db->getRows();
		$s = "<select name='".$name."' class='ddlist' onchange=\"".$onchange."\">";
		$s .= "<option value='NULL'>select</option>";
		if (is_array($result)) {
			foreach($result as $row) {
				
				$chk = ($row['id'] == $selected) ? "selected" : "";
				$sLabel =  ($bCount) ? $row['name'] ." (".$row['count'].")"  : $row['name']; 
				$s .= "<option value='".$row['id']."' $chk>".$sLabel."</option>";
			}
		}
		$s .= "</select>";
		return $s;
	}
	
}



?>
