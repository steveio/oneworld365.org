<?

class News {

	public function __construct(&$db) {
		$this->db = $db;
	}
	
	public function GetNews($iSiteId, $iLimit) {
		$this->db->query("SELECT n.* FROM news n, website_news_map m WHERE m.website_id = ".$iSiteId." AND m.news_id = n.id ORDER BY n.added DESC LIMIT ".$iLimit.";");
		$aResult = $this->db->getObjects();
		$aNewsItem = array();
		for($i=0;$i<count($aResult);$i++) {
			$oNewsItem = new NewsItem($aResult->id,$aResult->title,$aResult->description,$aResult->added);
			$aNewsItem[] = $oNewsItem; 
		}
		return $aNewsItem;
	}
	
	public function AddNewsItem($oNewsItem) {
		$this->db->query("INSERT INTO news (id,title,description,added) VALUES (nextval('news_seq'),$oNewsItem->title,$oNewsItem->description,now()::timestamp);");
		if ($this->db->getAffectedRows() == 1) {
			return true;
		}
	}

}

class NewsItem {

	private $iId;
	private $sTitle;
	private $sDescription;
	private $sAddedDt;
	private $aSiteId;
	
	public function __construct($iId,$sTitle,$sDescription,$sAddedDt,$aSiteId) {
		$this->iId = $iId;
		$this->sTitle = $sTitle;
		$this->sDescription = $sDescription;
		$this->sAddedDt = $sAddedDt;
		$this->aSiteId= $aSiteId;
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

	public function SetDescription($sDescription) {
		$this->sDescription = $sDescription;
	}
	
	public function GetAdded() {
		return $this->sAddedDt;	
	}
	
	public function SetAdded($sAddedDt) {
		$this->$sAddedDt = $sAddedDt;
	}

	public function GetSiteId() {
		return $this->aSiteId;	
	}
	
	public function SetSiteId($aSiteId) {
		$this->aSiteId = $aSiteId;
	}
	
}


?>