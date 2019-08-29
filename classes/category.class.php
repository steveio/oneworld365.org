<?

class Category {

    public function __construct(&$db)
    {
        return $this->Category($db);
    }
 
	function Category(&$db) {
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$this->db = $db;
	}
	

	function GetAll() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$sql = "SELECT c.id,c.name,c.url_name FROM category c ORDER BY sort_order DESC;";
		$this->db->query($sql);
		return $this->db->getRows();
	}
	
	function GetCategoriesByWebsite($iSiteId,$sReturn = "ROWS") {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$sql = "SELECT 
					c.id
					,c.name
					,c.url_name
				FROM 
					category c
					,website_category_map m 
				WHERE 
					m.website_id = ".$iSiteId." 
					AND m.category_id = c.id 
				ORDER BY 
					c.sort_order ASC;";
		
		$db->query($sql);
		
		if ($sReturn == "ROWS") {
			$aResult = $db->getRows();
			foreach ($aResult as $aRow) {
				$aNew[$aRow['id']]['id'] =  $aRow['id'];
				$aNew[$aRow['id']]['name'] =  $aRow['name'];
				$aNew[$aRow['id']]['url_name'] =  $aRow['url_name'];
			}
			return $this->aCategory = $aNew;
		}
		if ($sReturn == "OBJECTS") {
			return $db->getObjects(); 
		}
	}

	function GetCategoriesById($id,$type) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		if ($type == "company_id") {
			$sql = "SELECT a.id,a.name FROM comp_cat_map c, category a WHERE c.company_id = ".$id." AND c.category_id=a.id ";
		} elseif ($type == "placement_id") {
			$sql = "SELECT a.id,a.name FROM prod_cat_map c, category a WHERE c.prod_id = ".$id." AND c.category_id=a.id ";
		}
		$this->db->query($sql);
		return $this->db->getRows();
	}


	function GetCategoryLinkList($mode = "post",$aSelected = array(),$slash = true,$all =false) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $_CONFIG;
		
		if ($all) {
			$aCategories = $this->GetAll();
		} else {
			$aCategories = $this->GetCategoriesByWebsite($_CONFIG['site_id']);
		}
		
		$idx = 0;
		unset($ct_text);
		// get category text links
		$ct_text = '';
		if (is_array($aCategories)) {
		foreach($aCategories as $c) {
			
			if ($slash == true) {
				$delimeter = ($idx < count($aCategories) -1) ? " / " : " " ;
			}
			if ($mode == "input") {
				$checked = (in_array($c['id'],$aSelected)) ? "checked" : "";
				$ct_text .= $c['name'] . " <input class='inputCheckBox' type='checkbox' name='cat_".$c['id']."' $checked />  $delimeter  ";
			} else {
				$checked = ($_POST['cat_'.$c['id']] == "on") ? "checked" : "";
				$ct_text .= $c['name'];
			}
			
			$idx++;
		}
		}
		return $ct_text;
	}

	function GetSelected($link_to,$link_id) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $_CONFIG;
		
		switch ($link_to) {
			case "website" : 
				$tbl = "website_category_map";	
				$key = "website_id";
		}
		
		$sql = "SELECT c.id FROM category c,".$tbl." m WHERE m.".$key." = ".$link_id." AND m.category_id = c.id ORDER BY c.name ASC;";
		$this->db->query($sql);
		$aResult = $this->db->getRows();
		$aRes = array();
		for($i=0;$i<count($aResult);$i++) {
			$aRes[] = (int) $aResult[$i]['id'];
		}
		return $aRes;
	}

	
	public function GetById($iId) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		if (!is_numeric($iId)) return false;
		
		$db->query("SELECT id,name,url_name,desc_short as description,img_url FROM category WHERE id = ".$iId.";");
		
		$oRes = $db->getObject();
		$oRes->name = stripslashes($oRes->name);
		$oRes->description = stripslashes($oRes->description);
		
		return $oRes;
				
	}
	
	public function Update($iId,$sName,$sUrlName,$sDesc,$sImgUrl) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;		
		
		if (!is_numeric($iId)) return false;
		
		/* activity exists */
		$iExistingId = $db->getFirstCell("SELECT 1 FROM category WHERE id = ".$iId.";");		
		if (!is_numeric($iExistingId)) return false;
		
		/* update url_name */ 
		$sExistingName = $db->getFirstCell("SELECT name FROM category WHERE id = ".$iId.";");
		if ($sName != stripslashes($sExistingName)) { /* generate a new unique url namespace identifier */
			$oNs = new NameService();
			$sUrlName = $oNs->GetUrlName($sName,'category','name');			
		}
		
		$sSql = "UPDATE category SET name = '".addslashes($sName)."',url_name='".$sUrlName."',desc_short='".addslashes($sDesc)."' WHERE id = '".$iId."'";
		
		return $db->query($sSql);


	}
	
	public function Add($sName,$sDescription) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$iId = $db->getFirstCell("SELECT max(id)+1 FROM activity;");
				
		// generate unique url namespace identifier
		$oNs = new NameService();
		$sUrlName = $oNs->GetUrlName($sName,'category','name');
				
		return $db->query("INSERT INTO category (id,name,url_name,desc_short) VALUES (".$iId.",'".addslashes(ucfirst(strtolower($sName)))."','".$sUrlName."','".addslashes($sDescription)."');");
			
	}

	public function Delete($iId) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		if (!is_numeric($iId)) return false;
		
		global $db;
		
		$db->query("DELETE FROM website_category_map WHERE activity_id = ".$iId);
		$db->query("DELETE FROM comp_cat_map WHERE activity_id = ".$iId);
		$db->query("DELETE FROM prod_cat_map WHERE activity_id = ".$iId);
		$db->query("DELETE FROM category WHERE id = ".$iId);
		
		return true;
		
	}
	
	public function GetDDList($sName = "category_id") {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$aCategories = $this->GetAll();
		
		$sStr = "<select name='".$sName."'  class='ddlist'>";
		
		$sStr .= "<option value='null'>select</option>";
		
		foreach ($aCategories as $aCategory) {	
			$sStr .= "<option value='".$aCategory['id']."'>".$aCategory['name']."</option>";
		}

		$sStr .= "</select>";
		
		return $sStr;
		
	}

	public function GetCategoryPanelHTML() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db,$_CONFIG;
		
		// display the category select list
		$db->query("SELECT c.id,c.name,c.url_name,c.desc_short,c.img_url FROM category c, website_category_map m WHERE m.website_id = ".$_CONFIG['site_id']." AND m.category_id = c.id ORDER BY sort_order, name asc");
		$aCategory = $db->getObjects();
		
		if (count($aCategory) == 1) $singleCategorySite = true;
		if (!$_CONFIG['no_category_panel']) {

			$width = ($singleCategorySite) ? "200px" : "600px";
			$s .= "<div id='main_cat_panel' style='width: $width;'>";
				foreach ($aCategory as $c) {
					$s .= "<div id='category_panel_item' style='margin-top: 12px;'>";
					$s .= "<div id='category_panel_img'><a href='".$_CONFIG['url']."/placement/category/".$c->url_name."' title='Category : ".$c->name."'><img src='".$c->img_url."' alt='".$c->name."' width='140' height='100' border=0></a></div>";
					$s .= "<a class='category_panel_title' href='".$_CONFIG['url']."/placement/category/".$c->url_name."' title='Category : ".$c->name."'>".stripslashes($c->name)."</a><br />";
					$s .= "<p class='category_panel_desc'>".stripslashes($c->desc_short)."</p>";
					$s .= "</div>";
				}
			$s .= "</div>";
		} // end category panel
		
		return $s;
	}	

	public function GetByName($sName) {
	
		global $db;
	
		$sql = "SELECT id,name,url_name FROM category WHERE name = '".addslashes($sName)."';";
	
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
}

?>
