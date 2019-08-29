<?php


/* user submitted links */
define("DB__RECIPROCAL_LINK_TBL","reciprocal_link");
define("DB__RECIPROCAL_LINK_CAT_TBL","reciprocal_link_cat");						

/* article links */
define("DB__LINK_TBL","link");
define("DB__LINK_GROUP_TBL","link_group");
define("DB__LINK_MAP_TBL","link_map");	


/*
 * 
 * Represents a hyperlink
 * 
 */
class Link {

	private $id;
	private $title;
	private $url;
	
	public function __construct($id,$title,$url) {

		$this->id = $id;
		$this->title = stripslashes($title);
		$this->url = $url;		
		
	}

	public function GetId() {
		return $this->id;
	}
	
	public function SetId($id) {
		$this->id = $id;
	}
	
	public function GetTitle() {
		return $this->title;			
	}

	public function GetUrl() {
		
		if ((preg_match("/www/",$this->url)) && (!preg_match("/http/",$this->url))) {
			return "http://".$this->url;
		} else {	
			return $this->url;
		}
					
	}
	
	public function GetSortOrder() {
		return $this->sort_order;
	}
	
	public function Save(&$aResponse) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		if (!$this->Validate($aResponse)) return false;
						
		$id = $db->getFirstCell("SELECT nextval('link2_seq');");

		$sql = "INSERT INTO ".DB__LINK_TBL." (id,title,url) VALUES (".$id.",'".$this->GetTitle()."','".$this->GetUrl()."');";
		
		$db->query($sql);
		
		if ($db->getAffectedRows() == 1) {
			$this->SetId($id);
			return true;
		}
		
	}


	public function Map($link_to,$link_id) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;

		if (!is_numeric($link_id)) return false;
		
		$db->query("SELECT 1 FROM ".DB__LINK_MAP_TBL." WHERE link_id=".$this->GetId()." AND link_to='".$link_to."' AND link_id=".$link_id.";");

		if ($db->getNumRows() != 1) {
		
			$db->query("INSERT INTO ".DB__LINK_MAP_TBL." (link_id,link_to,link_to_id) VALUES (".$this->GetId().",'".$link_to."','".$link_id."');");

			if ($db->getAffectedRows() == 1) return true;
		}
		
	}
	
	public function Delete() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$db->query("SELECT 1 FROM ".DB__LINK_TBL." WHERE id=".$this->GetId());

		if ($db->getNumRows() == 1) {
			$db->query("DELETE FROM ".DB__LINK_TBL." WHERE id=".$this->GetId());			
			$db->query("DELETE FROM ".DB__LINK_MAP_TBL." WHERE link_id=".$this->GetId());
			return true;
		}
		
	}
	
	
	
	public function Validate(&$aResponse) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		if (strlen($this->GetUrl()) < 1) {
			$aResponse['msg'][] = "Please supply a valid link url.";
		}
		
		if (strlen($this->GetUrl()) > 255) {
			$aResponse['msg'][] = "Link Url must be less than and 255 characters.";
		}

		if (preg_match("/[^a-zA-Z0-9\. :\/_\-]/",$this->GetUrl())) {
			$aResponse['msg'][] = "Link Url contains invalid characters.";
		}

		if (strlen($this->GetTitle()) < 1) {
			$aResponse['msg'][] = "Please supply a valid link title.";
		}
		
		if (strlen($this->GetTitle()) > 127) {
			$aResponse['msg'][] = "Link title must be less than and 127 characters.";
		}
		
		if (count($aResponse['msg']) >= 1) return false;
		
		return true;
	}
	
	public function SetFromArray($a,$m="GET") {
		foreach($a as $k => $v) {
			if ($m == "GET") {
				$this->$k = (is_string($v)) ? stripslashes($v) : $v;
			} elseif ($m == "SET") {
				$this->$k = (is_string($v)) ? addslashes($v) : $v;
			}
		}
	}

};



class LinkGroup implements TemplateInterface {

	private $id;
	private $title;

	private $aItems;
	public $oTemplate;

	public function __construct() {

		$this->oTemplate = new Template(); 
		
		$this->aItems = array();

	}


	public function GetId() {
		return $this->id;			
	}
	
	public function GetTitle() {
		return $this->title;			
	}

	public function GetSortOrder() {
		return $this->sort_order;			
	}
		
	public function GetItems() {
		return $this->aItems;			
	}
	

	public function GetByAssociation($link_to,$link_to_id) {
		
		global $db;
		
		$db->query("SELECT l.* FROM ".DB__LINK_TBL." l,".DB__LINK_MAP_TBL." m WHERE m.link_to_id = ".$link_to_id." AND m.link_to = '".$link_to."' AND m.link_id = l.id ORDER BY l.title DESC;");
		
		if ($db->GetNumRows() >= 1) {
			$result = $db->getRows(); 
			foreach($result as $row) {
				$oLink = new Link($row['id'],$row['title'],$row['url']);
				$this->aItems[] = $oLink; 
			}
		}
	}
	
	public function LoadTemplate($sFilename,$aOptions = array()) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$this->oTemplate->Set("LINK_ARRAY",$this->GetItems());
				
		$this->oTemplate->LoadTemplate($sFilename);
		
	}
	
	public function Render() {

		return $this->oTemplate->Render();
		
	}

	
	public function SetFromArray($aRow) {
		
	}
	
};






class ReciprocalLinkCategory {

	private $id;
	private $name;
	private $description;
	
	private $aLink;

	public function __construct($id,$name,$description) {
		
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
		
	}

	public function GetId() {
		return $this->id;
	}
	
	public function GetName() {
		return $this->name;
	}
	
	public function GetDescription() {
		return $this->description;
	}

	
	
	public static function GetAll() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$db->query("SELECT id,name,description FROM ".DB__RECIPROCAL_LINK_CAT_TBL." ORDER BY name DESC;");
		
		$aRes = $db->getObjects();
		
		$aLinkCat = array();
		
		foreach($aRes as $o) {
			$aLinkCat[] = new ReciprocalLinkCategory($o->id,$o->name,$o->description);
		}
		
		return $aLinkCat;
	}
	
	public function SetLink($oLink) {
		$this->aLink[] = $oLink;
	}
	
	public function GetLinks() {
		return $this->aLink;
	}
	
	
	public static function GetDropDownList($selected,$name = 'link_cat_id') {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$aLinkCat  = ReciprocalLinkCategory::GetAll();
		
		$s = "<select name='".$name."' class='ddlist'>";
		$s .= "<option value='NULL'>select</option>";
		if (is_array($aLinkCat)) {
			foreach($aLinkCat as $o) {
				$chk = (trim($o->GetId()) == $selected) ? "selected" : "";
				$s .= "<option value='".$o->GetId()."' $chk>".$o->GetName()."</option>";
			}
		}
		$s .= "</select>";
		return $s;
	}
	
	
}



class ReciprocalLink {

	private $id;
	private $name;
	private $description;
	private $backlink_url;
	private $email;
	private $ip_addr;
	private $added;
	
	
	public function __construct() {		
	}
	
	
	public function GetId() {
		return $this->id;
	}
	
	public function GetName($prefix = false) {
		if ($prefix) {
  			if (!preg_match("/http/",$this->name)) {
				return "http://".$this->name;
			}
		}
		return $this->name;
	}
	
	public function GetDescription() {
		return $this->description;
	}

	public function GetEmail() {
		return $this->email;
	}
	
	public function GetBackLink() {
		return $this->backlink_url;
	}
	
	
	public function GetIPAddr() {
		return $this->ip_addr;
	}
	
	public function GetAdded() {
		return $this->added;
	}
	
	
	public function Set($id,$name,$description,$email,$backlink_url,$ip,$added) {
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
		$this->email = $email;
		$this->backlink_url = $backlink_url;
		$this->ip_addr = $ip;
		$this->added = $added;
	}
	
	
	public static function GetAll() {
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;
		
		$db->query("SELECT  l.id
							,l.name
							,l.description
							,l.email
							,l.backlink_url
							,c.id as cat_id
							,c.name as cat_name
							,c.description as cat_description 
						FROM 						
							".DB__RECIPROCAL_LINK_CAT_TBL." c
							,".DB__RECIPROCAL_LINK_TBL." l
						WHERE 
							l.cat_id = c.id
							AND l.status = 1
						ORDER BY 
							c.id ASC	
							,l.name ASC
						;");

		$aRes = $db->getObjects();
		
		$aLinkCat = array(); 
		
		foreach($aRes as $o) {
			if (!array_key_exists($o->cat_id,$aLinkCat)) {
				$aLinkCat[$o->cat_id] = new ReciprocalLinkCategory($o->cat_id,$o->cat_name, $o->cat_description);
			}
			$oLink = new ReciprocalLink();
			$oLink->Set($o->id,$o->name,$o->description,$o->email,$o->backlink_url,$o->ip_addr,$o->added);
			$aLinkCat[$o->cat_id]->SetLink($oLink);
		}
		
		return $aLinkCat;
			
	}

	
	public static function GetPending() {
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;
		
		$db->query("SELECT  l.id
							,l.cat_id
							,l.name
							,l.description
							,l.email
							,l.backlink_url
							,l.status
							,l.ip_addr
							,to_char(l.added,'DD/MM/YYYY HH24:MI:SS') as added
						FROM 
							".DB__RECIPROCAL_LINK_TBL." l
						WHERE 
							l.status = 0
						;");

		$aRes = $db->getObjects();
		
		$aLink = array();
		
		
		if (count($aRes) < 1) return array();

		foreach($aRes as $o) {
			$oLink = new ReciprocalLink();
			$oLink->Set($o->id,$o->name,$o->description,$o->email,$o->backlink_url,$o->ip_addr,$o->added);
			$aLink[$o->id] = $oLink; 
		}
		
		return $aLink;
		
			
	}
	
	public function Suggest($sUrl,$sDesc,$sEmail,$sBackLinkUrl,&$aResponse) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$sUrl = htmlspecialchars($sUrl,ENT_NOQUOTES,"UTF-8");
		$sDesc = htmlspecialchars($sDesc,ENT_NOQUOTES,"UTF-8");
		$sEmail = htmlspecialchars($sEmail,ENT_NOQUOTES,"UTF-8");
		$sBackLinkUrl = htmlspecialchars($sBackLinkUrl,ENT_NOQUOTES,"UTF-8");
		
		if (!$this->Valid($sUrl,$sDesc,$sEmail,$sBackLinkUrl,$aResponse)) return false;

		$sHost = preg_replace("/http:\/\/www./","",$sUrl);
		$db->query("SELECT id FROM ".DB__RECIPROCAL_LINK_TBL." WHERE name like '%".$sHost."'");
		if ($db->getNumRows() >= 1) {
			$aResponse['msg'][] = "Your link has already been added.";
			return false;
		}

		$ip = IPAddress::GetVisitorIP();
		
		$sql = "INSERT INTO ".DB__RECIPROCAL_LINK_TBL." (id,name,description,email,backlink_url,status,ip_addr,added) VALUES (nextval('link_seq'),'".addslashes($sUrl)."','".addslashes($sDesc)."','".addslashes($sEmail)."','".addslashes($sBackLinkUrl)."',0,'".$ip."',now()::timestamp);";

		$db->query($sql);
		
		if ($db->getAffectedRows() != 1) {
			$aResponse['msg'][] = "There was a problem adding your link.";
			return false;
		}
		
		return true;
	}
	
	public function Valid($sUrl,$sDesc,$sEmail,$sBackLinkUrl,&$aResponse) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		if (strlen($sUrl) < 13) {
			$aResponse['msg']['url'] = "Please supply a valid url.";
		}
		
		if (strlen($sUrl) > 119) {
			$aResponse['msg']['url'] = "Url must be less than and 120 characters.";
		}

		if (strlen($sEmail) > 119) {
			$aResponse['msg']['email'] = "Email must be less than and 120 characters.";
		}
		
		if (strlen($sBackLinkUrl) > 119) {
			$aResponse['msg']['backlink_url'] = "BackLink Url must be less than and 120 characters.";
		}
		
		if (strlen($sDesc) < 1) {
			$aResponse['msg']['desc'] = "Please supply a valid description.";
		}
		
		if (strlen($sDesc) > 299) {
			$aResponse['msg']['desc'] = "Desc must be less than and 120 characters.";
		}
		
		if (count($aResponse['msg']) >= 1) return false;
		
		
		return true;
		
	}
	
	
	public static function Approve($id,$cat_id,&$aResponse) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		if (!is_numeric($id)) {
			$aResponse['msg']['id'] = "Invalid request, unable to approve link.";
		}
		if (!is_numeric($cat_id)) {
			$aResponse['msg']['cat_id'] = "You must select a valid category.";
		}

		$db->query("SELECT id FROM ".DB__RECIPROCAL_LINK_TBL." WHERE id = ".$id);
		
		if ($db->getNumRows() != 1) {
			 $aResponse['msg']['update'] = "Duplicate link error.";
		}
		
		if (count($aResponse['msg']) >= 1) return false;
		
		$db->query("UPDATE ".DB__RECIPROCAL_LINK_TBL." SET cat_id = ".$cat_id .", status = 1 WHERE id = ".$id);
		
		return true;
	}

	public static function Reject($id,&$aResponse) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		if (!is_numeric($id)) {
			$aResponse['msg']['id'] = "Invalid request, unable to approve link.";
		}

		$db->query("SELECT id FROM ".DB__RECIPROCAL_LINK_TBL." WHERE id = ".$id);

		if ($db->getNumRows() != 1) {
			 $aResponse['msg']['update'] = "Link missing / duplicate error.";
		}

		if (count($aResponse['msg']) >= 1) return false;

		$db->query("DELETE FROM ".DB__RECIPROCAL_LINK_TBL." WHERE id = ".$id);

		return true;

	}
	
}

?>
