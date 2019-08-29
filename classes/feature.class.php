<?



class Feature {


	private $id;
	private $title;
	private $description;
	private $img_url1;
	private $img_url2;
	private $img_url3;
	private $current;

	
	private $aSidebar;
	private $aError;
	
	private $aFeature; /* an array of feature objects, populated when calling GetAll */
	

	public function __construct() {

		$this->aSidebar = array();
		$this->aError = array();
		
	}

	public function GetId() {
		return $this->id;			
	}
	
	public function GetTitle() {
		return $this->title;			
	}

	public function GetDescription() {
		return $this->description;			
	}

	public function GetPublished() {
		$sDate = $this->published;
		$aMonths = array("January","February","March","April","May","June","July","August","September","October","November","December");
		$aBits = explode("-",$sDate);
		return $aMonths[((int)$aBits[1] -1)]." ".$aBits[0];			
	}
	
	public function GetSideBars() {
		return $this->aSidebar;
	}

	public function GetImage($idx) {
		
		if ($idx == 1) return $this->img_url1;
		if ($idx == 2) return $this->img_url2;
		if ($idx == 3) return $this->img_url3;
			
	}
	
	public function GetCurrent() {
		
		global $db;
		
		$db->query("SELECT * FROM feature WHERE current = 'T'");
		
		if ($db->GetNumRows() == 1) {
			$aRes = $db->getRow(PGSQL_ASSOC);
			foreach($aRes as $k => $v) { 
				$this->$k = is_string($v) ? stripslashes($v) : $v;
			}
			$this->SetSidebars();
			
		}
	}
	

	public function GetAll() {
		
		global $db;
		
		$db->query("SELECT * FROM feature ORDER BY published DESC");
		
		if ($db->GetNumRows() >= 1) {
			$aRes = $db->getRows(PGSQL_ASSOC);
			foreach($aRes as $aRow) {
				$oFeature = new Feature();
				foreach($aRow as $k => $v) {
					$oFeature->$k = is_string($v) ? stripslashes($v) : $v;
				}
				
				$oFeature->SetSidebars();
				$this->aFeature[$oFeature->GetId()] = $oFeature; 
			}
		}
		return $this->aFeature;
	}

	
	private function SetSidebars() {
		
		global $db;
		
		$db->query("SELECT * FROM feature_sidebar WHERE feature_id = ".$this->id);
				
		if ($db->GetNumRows() >= 1) {
			$aRes = $db->getRows();
			
			foreach($aRes as $aRow) {
				$oFeatureSidebar = new FeatureSidebar();
				$oFeatureSidebar->GetFromArray($aRow);
				$this->aSidebar[] = $oFeatureSidebar; 
			}
		}
		
	}
	
	public function Create($aFormVars) {
		
	}

	
	public function GetError() {
		return $this->aError;
	}
	
};


class FeatureSidebar {

	private $id;
	private $feature_id;
	private $title;
	private $sort_order;
	private $aItems;

	public function __construct() {

		$this->aItems = array();

	}

	
	public function GetFromArray($aRow) {
		
		$this->id = $aRow['id'];
		$this->feature_id = $aRow['feature_id'];
		$this->title = stripslashes($aRow['title']);
		$this->sort_order = $aRow['sort_order'];
		$this->aItems = array();
		
		$this->SetItems();
		
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
	
	private function SetItems() {

		global $db;
		
		$db->query("SELECT * FROM feature_sidebar_item WHERE sidebar_id = ".$this->id);
		
		if ($db->GetNumRows() >= 1) {
			$aRes = $db->getRows(PGSQL_ASSOC);
			foreach($aRes as $aRow) {
				$this->aItems[] = new FeatureSidebarItem($aRow); 
			}
		}
		
	}

};


class FeatureSidebarItem {

	private $id;
	private $title;
	private $url;
	private $sort_order;
			

	public function __construct($aRow) {

		$this->id = $aRow['id'];
		$this->title = stripslashes($aRow['title']);
		$this->sort_order = $aRow['sort_order'];
		$this->url = $aRow['url'];		
		
	}

	public function GetTitle() {
		return $this->title;			
	}

	public function GetUrl() {
		return $this->url;			
	}
	
	public function GetSortOrder() {
		return $this->sort_order;
	}

};

?>