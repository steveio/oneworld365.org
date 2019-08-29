<?

class Website {

	public function __construct(&$db) {
		$this->db = $db;
	}
	
	public function GetWebsites($orderBy = "id DESC") {
		$this->db->query("SELECT id,name FROM website ORDER BY $orderBy;");
		return $aResult = $this->db->getObjects();
	}
	
	public function GetSiteSelectList($aSelected = array()) {
		$aWebsites = $this->GetWebsites();

		$sStr = "<div>";
		foreach ($aWebsites as $oSite) {	
			$sStr .= "<span style='display: block; margin: 3px 0px 3px 0px;'><label style='width:150px'>".$oSite->name."</label>";
			$sChecked = (in_array($oSite->id,$aSelected)) ? "checked" : "";
			$sStr .= "<input type='checkbox' name='web_".$oSite->id."' $sChecked/><br/>";
			$sStr .= "</span>";
		}
		$sStr .= "</div>";		

		return $sStr;	
	}
	
	public function GetCompanyWebsiteList($iCompanyId) {
		if (!is_numeric($iCompanyId)) return;
		$this->db->query("SELECT website_id FROM comp_website_hp_map WHERE company_id = ".$iCompanyId.";");
		for($i=0; $i< count($this->db->getNumRows()); $i++) {
			$aResult = $this->db->getRows();
		}
		$aId = array();
		for($i=0; $i< count($aResult); $i++) {
			$aId[] = (int) $aResult[$i]['website_id'];
		}
		return $aId;
		
	}

	public function GetSiteDropDownList($iSelectedSiteId) {

		global $_CONFIG;
		
		$aWebsites = $this->GetWebsites("name ASC");
		
		$sStr = "<select name='website_id' onchange='document.forms[0].submit();'>";
		
		foreach ($aWebsites as $oSite) {
			$s = ($iSelectedSiteId == $oSite->id) ? "selected" : "";	
			$sStr .= "<option value='".$oSite->id."' $s>".$oSite->name."</option>";
		}

		$sStr .= "</select>";
		
		return $sStr;
		
	}

}

?>