<?

class Event {

	public function __construct(&$db) {
		$this->db = $db;
	}
	
	
	/* get all events associated with a website */
	public function GetEvent($iSiteId, $iId, $iLimit = null) {
		
		$sIdConstraint = (is_numeric($iId)) ? " e.id = ".$iId. " AND " : ""; 
		$sLimit = (is_numeric($iLimit)) ? "LIMIT ".$iLimit : "";

		$sSql = "SELECT e.id,
						e.title,
						e.description, 
						e.url_name, 
						e.location, 
						e.country_id, 
						to_char(e.from_date,'dd/mm/yyyy') as from_date, 
						to_char(e.to_date,'dd/mm/yyyy') as to_date, 
						to_char(e.added,'dd/mm/yyyy') as added, 
						c.name as country_name 
					FROM event e, 
						website_event_map m, 
						country c 
					WHERE $sIdConstraint 
						m.website_id = ".$iSiteId." 
					AND m.event_id = e.id 
					AND e.country_id = c.id 
					ORDER BY e.from_date 
					DESC ".$sLimit.";";
		
		$this->db->query($sSql);
		$aResult = $this->db->getObjects();
		
		$aEventItem = array();
		
		for($i=0;$i<count($aResult);$i++) {
			
			$oEventItem = new EventItem($aResult[$i]->id,stripslashes($aResult[$i]->title),stripslashes($aResult[$i]->description),stripslashes($aResult[$i]->location), $aResult[$i]->country_id, $aResult[$i]->from_date, $aResult[$i]->to_date, $aSiteId = array(), $aResult[$i]->added,$aResult[$i]->country_name,$aResult[$i]->url_name);

			$oEventItem->GetImages();
						
			$aEventItem[] = $oEventItem; 

		}
		if (is_numeric($iId)) {
			if (is_object($aEventItem[0])) {
				return $aEventItem[0];
			}
		} else {
			return $aEventItem;
		}
	}
	
	/* update an event */
	public function UpdateEvent($oEventItem) {

		if (!is_numeric($oEventItem->GetId())) return -1;

		/* @todo - update url_name if title has changed */

		$sToDateSql = (Validation::IsValidDate($oEventItem->GetToDate())) ? ",to_date='".$oEventItem->GetToDate()."'::date" : "";
		
		$sSql = "UPDATE event SET 
								 title = '".addslashes($oEventItem->GetTitle())."'
								 ,description = '".addslashes($oEventItem->GetDescription())."'
								 ,location = '".addslashes($oEventItem->GetLocation())."'
								 ,country_id = ".$oEventItem->GetCountryId()."
								 ,from_date = '".$oEventItem->GetFromDate()."'::date
								 $sToDateSql
								 WHERE 
								 id = ".$oEventItem->GetId()."; 
									";

		$this->db->query($sSql);
		
		if ($this->db->getAffectedRows() != 1) {
			return -1;
		}
		
	
	}
	
	/* add a new event */
	public function AddEvent(&$oEventItem) {
		
		$sToDateSql = (Validation::IsValidDate($oEventItem->GetToDate())) ? "'".$oEventItem->GetToDate()."'::date," : 'null,';

		$sSql = "INSERT INTO event (
									id,
									title,
									description,
									url_name,
									location,
									country_id,
									from_date,
									to_date,
									added
								) VALUES (
									nextval('Event_seq'),
									'".addslashes($oEventItem->GetTitle())."',
									'".addslashes($oEventItem->GetDescription())."',
									'".$oEventItem->GetUrlName()."',
									'".addslashes($oEventItem->GetLocation())."',
									".$oEventItem->GetCountryId().",
									'".$oEventItem->GetFromDate()."'::date,
									".$sToDateSql."
									now()::timestamp
								);";
									
		$this->db->query($sSql);
		
		if ($this->db->getAffectedRows() == 1) {
			$oid = $this->db->getLastOid();
			$oEventItem->SetId($this->db->getFirstCell("SELECT id FROM event WHERE oid = $oid"));
			$_POST['id'] = $oEventItem->GetId();
			return 1; 
		} else {
			return -1;
		}
	}
	
	/* map this event to one or more websites */
	public function AddEventSiteMapping($iEventId,$aSiteId) {
		
		if ((!is_array($aSiteId)) || (count($aSiteId) < 1)) 	return false;	
		if (!is_numeric($iEventId)) return false;
		
		/* delete any existing site mappings for this event */
		$this->db->query("DELETE FROM website_event_map WHERE event_id = ".$iEventId."");
		
		/* add a new entry into website_event_map for each site this event will appear on */
		foreach($aSiteId as $iId) {
			$this->db->query("INSERT INTO website_event_map (website_id, event_id) VALUES (".$iId.",".$iEventId.")");
		}
	}

	/* add/update images associated with this event */
	public function AddEventImage($iEventId,$aImgUrl) {
		
		if ((!is_array($aImgUrl)) || (count($ImgUrl) < 1)) 	return false;	
		if (!is_numeric($iEventId)) return false;
		
		/* delete any existing images mapped onto this event */
		$this->db->query("DELETE FROM website_event_map WHERE event_id = ".$iEventId."");
		 //id  | ext  |  link_to  | link_id 
		
		
		/* add a new entry into website_event_map for each site this event will appear on */
		foreach($aSiteId as $iId) {
			$this->db->query("INSERT INTO website_event_map (website_id, event_id) VALUES (".$iId.",".$iEventId.")");
		}
		
	}
	
}


class EventItem {

	private $iId;
	private $sTitle;
	private $sDescription;
	private $sUrlName;
	private $sLocation;
	private $iCountryId;
	private $sCountry;	
	private $sFromDt;
	private $sToDt;	
	private $sAdded;
	private $aSiteId;
	private $aError;
	private $aImageUrl;
	private $aImage;
	
	public function __construct($iId = null,$sTitle,$sDescription,$sLocation,$iCountryId,$sFromDt,$sToDt,$aSiteId,$sAddedDt = '',$sCountry = '',$sUrlName = '') {
		$this->iId = $iId;
		$this->sTitle = $sTitle;
		$this->sDescription = $sDescription;
		$this->sLocation = $sLocation;
		$this->iCountryId = $iCountryId;		
		$this->sCountry = $sCountry;
		$this->sFromDt = $sFromDt;
		$this->sToDt = $sToDt;
		$this->sUrlName = $sUrlName;
		$this->sAddedDt = $sAddedDt;
		$this->aImageUrl = array(); /* an array of remote image url's associated with this event */
		$this->aImage = array(); /* an array of images (local) associated with this event */ 

		/* get array of site id's this event can appear on */
		$this->aSiteId = $aSiteId;

	}

	public function CreateUrlName() {
		/* generate unique url friendly namespace identifier */
		if (strlen($this->sTitle) > 1) {
			$oNs = new NameService();
			$this->sUrlName = $oNs->GetUrlName($this->sTitle,$sTbl = 'event','url_name');
		}	
	}
		
	public function GetId() {
		return $this->iId;
	}

	public function SetId($iId) {
		$this->iId = $iId;
	}
	
	public function GetTitle() {
		return $this->sTitle;	
	}

	public function SetTitle($sTitle) {
		$this->sTitle = $sTitle;		
	}
	
	public function GetDescription() {
		return $this->sDescription;
	}

	public function GetShortDescription() {
		if (strlen($this->sDescription) > 100) {
			$sOut = $this->sDescription." ";
			$sOut = substr($sOut,0,100);
			$sOut = substr($sOut,0,strrpos($sOut,' '));
			$sOut = $sOut."...";
			return $sOut;
		} else {
			return $this->sDescription;
		}
	}
	
	
	public function SetDescription($sDescription) {
		$this->sDescription = $sDescription;
	}

	public function GetUrlName() {
		return $this->sUrlName;	
	}

	public function GetLocation() {
		return $this->sLocation;	
	}

	public function GetCountryId() {
		return $this->iCountryId;	
	}

	public function GetCountry() {
		return $this->sCountry;	
	}
	
	
	public function GetFromDate() {
		return $this->sFromDt;	
	}
	
	public function GetToDate() {
		return $this->sToDt;	
	}
	
	public function GetAddedDt() {
		return $this->sAddedDt;	
	}
	
	public function GetSiteId() {
		return $this->aSiteId;	
	}
	
	public function SetImageUrl($sUrl) {
		$this->aImageUrl[] = $sUrl; 
	}
	
	public function GetImageUrls() {
		return $this->aImageUrl;	
	}

	public function GetImages() {
		global $_CONFIG,$db;
		$oImage = new Image($db,$_CONFIG['root_path'],"http://www.oneworld365.org");
		$aImg = $oImage->GetImg('EVENT',$this->GetId());
		for($i=0; $i<count($aImg); $i++) {
			$aImg[$i]->sImgUrl = $oImage->sImgBaseUrl . $oImage->sImgPrefix . $aImg[$i]->id . $aImg[$i]->ext;
			$aImg[$i]->sImgThumbnailUrl = $oImage->sImgBaseUrl . $oImage->sImgPrefix . $aImg[$i]->id . $oImage->sImgThumbnailSuffix . $aImg[$i]->ext;			
		}
		$this->aImage = $aImg;
	}

	public function SetImage($oImg) {
		$this->aImage[] = $oImg;
	}

	public function GetImage() {
		return $this->aImage;
	}
	
	public function Validate() {
		
		$aError = array();
				
		if (strlen($this->sTitle) < 1) {
			$aError['title'] = "Please supply a valid title.";
		}
		if (strlen($this->sTitle) >= 200) {
			$aError['titleLength'] = "Title should be less than 200 characters.";
		}
		if (strlen($this->sDescription) < 1) {
			$aError['description'] = "Please supply a description or some details about the event.";
		}
		if (strlen($this->sLocation) < 1) {
			$aError['location'] = "Please supply an event location.";
		}
		if (strlen($this->iCountryId) < 1) {
			$aError['country'] = "Please select a country.";
		}
		if (!Validation::IsValidDate($this->sFromDt)) {
			$aError['fromDt'] = "Please specify a valid start date.";
		}
		
		if (count($this->aSiteId) < 1) {
			$aError['site'] = "Please select one or more sites where this event will appear.";
		}
		
		return $aError;
				
		
	}	
	
}













?>
