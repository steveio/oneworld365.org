<?

class Activity {

	private $id;
	private $name;
	private $count;
	public $url_name;
	private $description;

	public function __construct()
	{
        $this->Activity();	    
	}

	/* @param depreciated $db */ 
	function Activity($db = NULL) {

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

	public function GetCount() { 
		return stripslashes($this->count);
	}

	public function SetCount($count) { 
		$this->count = $count;
	}		
	
	public function GetUrl() {
		global $_CONFIG; 
		return $_CONFIG['url'] ."/". $this->url_name;
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
	
	
	
	/*
	 * Get all Activities
	 * 
	 * By default, returns just activities mapped to current site
	 * 
	 * @param string return type {ROWS || OBJECTS}
	 * @param bool only activities mapped to current site? 
	 * 
	 */
	function GetAll($r = "ROWS",$all = false) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db,$_CONFIG;
		
		if ($all) {

			$sql = "SELECT a.id
							,a.name
							,a.url_name
							,a.description
							,a.keywords 
						FROM 
							activity a
						ORDER BY 
							a.name ASC;";
			
		} else {
		
			$sql = "SELECT a.id
							,a.name
							,a.url_name
							,a.description
							,a.keywords 
						FROM 
							activity a
							,website_activity_map m
						WHERE
							m.website_id = ".$_CONFIG['site_id']." 
							AND m.activity_id = a.id 
						ORDER BY 
							a.name ASC;";
		}
			
		$db->query($sql);

		if ($r == "ROWS") {
			return $db->getRows();
		} 
		if($r == "OBJECTS") {
			return $db->getObjects();
		}
		
	}
	
	function GetByCategoryId($iCategoryId)
	{
		global $db;
		
		$sql = "SELECT  a.id
						,a.name
						,a.url_name
				FROM
					activity a
					,cat_act_map m
				WHERE
					m.category_id = ".$iCategoryId."
				AND m.activity_id = a.id
				ORDER BY a.name asc
				";
		
		$db->query($sql);
		
		return $db->getObjects();
		
	}
	
	
	function GetAllActivities() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db, $_CONFIG;
		
		/* get id's of activities mapped to the current website */
		$sql = "SELECT 
					a.id 
				FROM 
					activity a, 
					website_activity_map m 
				WHERE 
					m.website_id = ".$_CONFIG['site_id']." 
					AND m.activity_id = a.id 
				ORDER BY 
					a.name ASC;";
		$db->query($sql);
		$aResult = $db->getRows();
		foreach ($aResult as $aRow) {
			$aMappedActivityId[] =  $aRow['id'];
		}
		
		/* get all other activites (those not mapped to the current website) */ 
		$sql = "SELECT id,name FROM activity ORDER BY name ASC;";
		$db->query($sql);
		$aResult = array();
		$aResult = $db->getRows();
		foreach ($aResult as $aRow) {
			$aAllActivity[$aRow['id']]['id'] =  $aRow['id'];
			$aAllActivity[$aRow['id']]['name'] =  $aRow['name'];
			$aAllActivity[$aRow['id']]['visible'] = false;
		}
		
		/* set activities mapped to the current website as visible */
		foreach($aAllActivity as $aActivity) {
			if (in_array($aActivity['id'],$aMappedActivityId)) {
				$aAllActivity[$aActivity['id']]['visible'] = true;
			}
		}
				
		return $this->aActivity = $aAllActivity;
	}


	function GetActivitiesById($id,$type) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		if ($type == "company_id") {
			$sql = "SELECT a.id,a.name FROM comp_act_map c, activity a WHERE c.company_id = ".$id." AND c.activity_id=a.id ";
		} elseif ($type == "placement_id") {
			$sql = "SELECT a.id,a.name FROM prod_act_map c, activity a WHERE c.prod_id = ".$id." AND c.activity_id=a.id ";
		}
		$db->query($sql);
		return $db->getRows();
	}


	function GetActivityLinkList($mode = "post",$aSelected = array(),$slash = true,$all = false) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		/* @todo - confusing! refactor method names */
		if ($all) {
			$aActivities = $this->GetAll($return = "ROWS",$all = true);
		} else {
			$aActivities = $this->GetAllActivities();
		}
		$idx = 0;
		$sHtml = '';
		$sHtml2 = '';
		
		foreach($aActivities as $aActivity) {
			$link = $_SERVER['PHP_SELF']  ."?s=activity&id=".$aActivity['id']."";
			if ($slash == true) {
				$delimeter = ($idx < count($aActivities) -1) ? " / " : " " ;
			}
			if ($mode == "input") {
				$checked = (in_array($aActivity['id'],$aSelected)) ? "checked" : "";
				$value = (in_array($aActivity['id'],$aSelected)) ? "on" : "off";
			} else {
				$checked = ($_POST['act_'.$aActivity['id']] == "on") ? "checked" : "";
			}
			
			if (($aActivity['visible'] == true) || ($all)) {
				$sHtml .= "<label class='select_list'>".$aActivity['name'] ."</label><input class='select_list' type='checkbox' name='act_".$aActivity['id']."' $checked /> ". $delimeter ."";
			} else {
				$sHtml2 .= "<input type='hidden' name='act_".$aActivity['id']."' value='".$value."' /> ";
			}
			
			$idx++;
		}
		return $sHtml.$sHtml2;
	}

	function GetActivityTopX($x = 10) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$sql = "select title from (select distinct(p.title), count(p.id) from placement p group by p.title order by count desc limit 20) as title order by title asc;";
		$db->query($sql);
		$aResult = $db->getRows();

		foreach ($aResult as $aRow) {
			$s .= $aRow['title'] . " <br />";
		}

		return $s;
	}


	function GetSelected($link_to,$link_id) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db, $_CONFIG;
		
		switch ($link_to) {
			case "website" : 
				$tbl = "website_activity_map";	
				$key = "website_id";
		}
		
		$sql = "SELECT a.id FROM activity a,".$tbl." m WHERE m.".$key." = ".$link_id." AND m.activity_id = a.id ORDER BY a.name ASC;";
		$db->query($sql);
		$aResult = $db->getRows();
		$aRes = array();
		for($i=0;$i<count($aResult);$i++) {
			$aRes[] = (int) $aResult[$i]['id'];
		}
		return $aRes;
	}
	
	public function ActivityExists($sName) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		/* does an activity with "name" already exist? */
		if (is_numeric($db->getFirstCell("SELECT id FROM activity WHERE name = '".addslashes(ucfirst(strtolower($sName)))."';"))) {
			return true;
		}
		
	
	}

	
	public function GetById($iId) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		if (!is_numeric($iId)) return false;
		
		$db->query("SELECT id,name,url_name,description,img_url FROM activity WHERE id = ".$iId.";");
		
		$oRes = $db->getObject();
		$oRes->name = stripslashes($oRes->name);
		$oRes->description = stripslashes($oRes->description);
		
		return $oRes;
				
	}

	public function GetByName($sName) {
	
		global $db;
	
		$sql = "SELECT id,name,url_name FROM activity WHERE name = '".addslashes($sName)."';";
	
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

	
	public function Update($iId,$sName,$sUrlName,$sDesc,$sImgUrl) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;		
		
		if (!is_numeric($iId)) return false;
		
		/* activity exists */
		$iExistingId = $db->getFirstCell("SELECT 1 FROM activity WHERE id = ".$iId.";");		
		if (!is_numeric($iExistingId)) return false;
		
		/* update url_name */ 
		$db->query("SELECT name,url_name FROM activity WHERE id = ".$iId.";");
		$row = $db->getRow();
		$sExistingName = $row['name'];
		$sExistingUrlName = $row['url_name'];
		if ($sName != stripslashes($sExistingName)) { /* generate a new unique url namespace identifier */
			$oNs = new NameService();
			$sUrlName = $oNs->GetUrlName($sName,'activity','name');			
		}
		
		$sSql = "UPDATE activity SET name = '".addslashes($sName)."',url_name='".$sUrlName."',description='".addslashes($sDesc)."' WHERE id = '".$iId."'";

		$db->query($sSql);

		/* add a url mapping to redirect requests for old url to new url */
		$url_from = "/".$sExistingUrlName;
		$url_to = "/".$sUrlName;
		$oNs->AddUrlMapping($url_from, $url_to);
	
		/* re-map any articles published to old url */
		require_once("./classes/article.class.php");
		ContentMapping::UpdateUrl($url_from,$url_to);
	
		return TRUE;


	}
	
	public function Add($sName,$sDescription) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$iId = $db->getFirstCell("SELECT max(id)+1 FROM activity;");
				
		// generate unique url namespace identifier
		$oNs = new NameService();
		$sUrlName = $oNs->GetUrlName($sName,'activity','name');
				
		return $db->query("INSERT INTO activity (id,name,url_name,description) VALUES (".$iId.",'".addslashes(ucfirst(strtolower($sName)))."','".$sUrlName."','".addslashes($sDescription)."');");
			
	}

	public function Delete($iId) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		if (!is_numeric($iId)) return false;
		
		global $db;
		
		$db->query("DELETE FROM website_activity_map WHERE activity_id = ".$iId);
		$db->query("DELETE FROM comp_act_map WHERE activity_id = ".$iId);
		$db->query("DELETE FROM prod_act_map WHERE activity_id = ".$iId);
		$db->query("DELETE FROM activity WHERE id = ".$iId);
		
		return true;
		
	}
	
	public function GetDDList($sName = "activity_id",$selected = 'null',$sOnChangeJs = '',$bCount = false) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG,$db;

		
		if($bCount) {

			$sql = "SELECT a.id
							,a.name
							,f.freq as count 
						FROM 
							activity a
							,website_activity_map m
							,proj_freq_matrix f
						WHERE
							m.website_id = ".$_CONFIG['site_id']." 
							AND m.activity_id = a.id
					 		AND f.sid = ".$_CONFIG['site_id']."
							AND f.c1_type = 0
							AND f.freq >= 1
							AND a.id = f.c1_id
							AND c2_id is null							
						ORDER BY 
							a.name ASC;";
			
			
		} else {

			$sql = "SELECT a.id
							,a.name 
						FROM 
							activity a
							,website_activity_map m
						WHERE
							m.website_id = ".$_CONFIG['site_id']." 
							AND m.activity_id = a.id 
						ORDER BY 
							a.name ASC;";
		}
			
		$db->query($sql);
		

		$aActivities = $db->getRows();
		
		$sStr = "<select name='".$sName."'  class='ddlist' onchange=\"".$sOnChangeJs."\">";
		
		$sStr .= "<option value='null'>select</option>";
		
		foreach ($aActivities as $aActivity) {	
			$s = ($selected == $aActivity['id']) ? "selected" : "";
			
			$sLabel =  ($bCount) ? $aActivity['name'] ." (".$aActivity['count'].")"  : $aActivity['name'];
			
			$sStr .= "<option value='".$aActivity['id']."' ".$s.">".$sLabel."</option>";
		}

		$sStr .= "</select>";
		
		return $sStr;
		
	}

	/* currently only used on seasonal jobs */
	public function GetActivityPanelHTML() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db,$_CONFIG;
		

        // display the activity select list
        $db->query("SELECT a.id,a.name,a.url_name, count(c.*) FROM activity a, website_activity_map m, comp_act_map m2, ".$_CONFIG['company_table']." c WHERE m.activity_id = a.id AND m.website_id = ".$_CONFIG['site_id']." AND m2.activity_id = m.activity_id and m2.company_id = c.id GROUP BY a.name,a.id,a.url_name ORDER BY a.name asc;");
        $arr = $db->getObjects();
		$s .= "<ul class='activity_list'>";
        foreach ($arr as $c) {
                $s .= "<li>";
                $s .= "<a class='std' href='".$_CONFIG['url']."/".$c->url_name."' title='".$c->name." Job'>".stripslashes($c->name)." (".$c->count.")</a><br>";
                $s .= "</li>";
        }
        $s .= "</ul>";

		return $s;
	}

	public static function GetSelectList() {

		global $db, $_CONFIG;
		
		// display the activity select list
		$db->query("SELECT a.id,a.name,a.url_name,count(*) FROM activity a, comp_act_map m, website_activity_map m2, ".$_CONFIG['company_table']." c WHERE m.activity_id = a.id AND m.activity_id = m2.activity_id AND m2.website_id = ".$_CONFIG['site_id']." AND m.company_id = c.id GROUP BY a.name,a.id,a.url_name ORDER BY a.name asc");
		$a = $db->getObjects();
		$out = array();
		if (!is_array($a)) return array();
		foreach($a as $o) {
			$oAct = new Activity();
    		foreach ($o as $key => $val) {
        		$oAct->$key = $val;
    		}
			$out[] = $oAct;
		}
		return $out;

	}

	public function SetFromObject($o) {
		
		foreach($o as $k => $v) {
			$this->$k = $v;
		}		
	}	

	
	public function GetActivityByWebsiteId($iSiteId,$aIdFilter = array()) {
		
		global $db;
	
		$id_sql = (count($aIdFilter) > 1) ? " AND a.id IN (".implode(",",$aIdFilter).") " : "";
		
		/* get id's of activities mapped to the current website */
		$sql = "SELECT
				a.id,
				a.name
			FROM
				activity a,
				website_activity_map m
			WHERE
				m.website_id = ".$iSiteId."
				AND m.activity_id = a.id
				$id_sql
			ORDER BY
				a.name ASC;";
		
		$db->query($sql);
		
		$aResult = $db->getRows();
		
		$aActivity = array();
		
		if (is_array($aResult) && count($aResult) >= 1) {
			foreach($aResult as $a) {
				$aActivity[$a['id']] = $a['name'];
			}
		}
		
		return $aActivity;

	}
	
	
	public function GetActivityListByWebsite($iSiteId) {
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db,$_CONFIG;
		
		$db->query("SELECT 
				a.id,
				a.name,
				a.url_name,
				f.freq as count
			FROM 
				activity a,
				proj_freq_matrix f, 
				prod_act_map m, 
				".$_CONFIG['placement_table']." p, 
				".$_CONFIG['company_table']." c, 
				website_activity_map m2 
			WHERE 
				m.activity_id = a.id
				AND f.sid = ".$iSiteId."
				AND f.c1_type = 0
				AND f.freq >= 1
				AND a.id = f.c1_id
				AND c2_id is null 
				AND m.activity_id = m2.activity_id 
				AND m2.website_id = ".$iSiteId." 
				AND m.prod_id = p.id 
				AND p.company_id = c.id 
			GROUP BY 
				a.name,a.id,
				a.url_name,
				f.freq 
			ORDER BY 
				a.name ASC;");

			return $aActivityList = $db->getObjects();
			
	}
	
	public static function getActivitySelectList()
	{
		global $db;
	
		$sql = "select
			c.id as cid,
			c.name as cname,
			a.id as aid,
			a.name as aname
			from
			category c
			left join cat_act_map m on m.category_id = c.id
			left outer join activity a on m.activity_id = a.id
			order by c.id, a.name";

		$db->query($sql);

		$aResult = $db->getRows();

		$aActivity = array();

		foreach ($aResult as $aRow) {
			$aActivity[$aRow['cname']][] = $aRow['aname']; 
		}
		
		return $aActivity;
	}
}

?>
