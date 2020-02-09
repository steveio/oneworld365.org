<?

/*
 * General (placement) Profile Class
 * 
 * 
 */



class GeneralProfile extends PlacementProfile implements ProfileInterface {
	
	protected $duration_from_id;
	protected $duration_to_id;
	protected $price_from_id;
	protected $price_to_id;
	protected $currency_id;
	protected $duration_txt; // @depreciated
	protected $start_dates;
	protected $benefits;
	protected $requirements;
	
	private $sTblName; /* general (placement) profile table */

	
	public function __Construct() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		
		$this->sTblName = "profile_general";
		$this->SetLinkTo("PLACEMENT");
		
	}

	public function GetTypeLabel() {
		return "Profile";
	}
	
	public function GetById($id) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");		
		
		if (!is_numeric($id)) return false;
		
		parent::__Construct();
		
		parent::SetSubTypeTable($this->GetSubTypeTable());
		parent::SetSubTypeFields($this->GetSubTypeFields());
		
		$oResult = parent::GetProfileById($id);

		if (!$oResult) return FALSE;
		
		foreach($oResult as $k => $v) {
			$this->$k = is_string($v) ? stripslashes($v) : $v;
		}

		parent::GetCategoryInfo();
		parent::GetActivityInfo();
		parent::GetCountryInfo();
		parent::GetImages();
		parent::SetCompanyLogo();
		parent::GetReviewRating();

		return TRUE;	
	}
	
	
	private function GetSubTypeTable() {	
		
		return $sSubTypeTable = "LEFT OUTER JOIN ".$this->sTblName." p2 ON p.id = p2.p_hdr_id";		
				
	}

	private function GetSubTypeFields() {
		
		return $sSubTypeFields = "
							,p2.duration_txt
							,p2.start_dates
							,p2.benefits
							,p2.requirements
							,p2.duration_from_id
							,p2.duration_to_id
							,p2.price_from_id
							,p2.price_to_id
							,p2.currency_id
							";		
	}

	
	/*
	 * Add volunteer profile specific record
	 * 
	 * @param array key value hash of fields to update
	 */
	public function AddSubTypeRecord($p) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;
		
		/* check that profile_hdr id and subtype record exist */
		if (!is_numeric($p['id'])) return false;
		
			
		$sql = "INSERT INTO ".$this->sTblName." (	p_hdr_id
												,duration_txt
												,start_dates
												,benefits
												,requirements
												,duration_from_id
												,duration_to_id
												,price_from_id
												,price_to_id
												,currency_id
												) VALUES (
													".$p['id']."
													,'".$p['duration_txt']."'
													,'".$p['start_dates']."'
													,'".$p['benefits']."'
													,'".$p['requirements']."'
													,".$p['duration_from_id']."
													,".$p['duration_to_id']."
													,".$p['price_from_id']."
													,".$p['price_to_id']."
													,".$p['currency_id']."
												);";

		$db->query($sql);

		if ($db->getAffectedRows() == 1) {
			$this->UpdateRefData($p);
			
			return true;
		} else {
			Logger::DB(1,get_class($this)."::".__FUNCTION__."()",$sql);
		}
		
	}
		
	
	
	/*
	 * Update volunteer profile specific fields
	 * 
	 * @param array key value hash of fields to update
	 */
	public function UpdateSubTypeRecord($p) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;
		
		/* check that profile_hdr id and subtype record exist */
		if (!is_numeric($p['id'])) return false;
		

		/* is there an existing sub-type record ? */
		if (!$db->getFirstCell("SELECT 1 FROM ".$this->sTblName." WHERE p_hdr_id = ".$p['id'])) {
			return $this->AddSubTypeRecord($p);
		}
			
		$sql = "UPDATE ".$this->sTblName." 
					SET 
						duration_txt = '".$p['duration_txt']."'
						,start_dates = '".$p['start_dates']."'
						,benefits = '".$p['benefits']."'
						,requirements = '".$p['requirements']."'
						,duration_from_id = ".$p['duration_from_id']."
						,duration_to_id = ".$p['duration_to_id']."
						,price_from_id = ".$p['price_from_id']."
						,price_to_id = ".$p['price_to_id']."
						,currency_id = ".$p['currency_id']."
					WHERE p_hdr_id = ".$p['id']."
				";
			
		$db->query($sql);
		
		if ($db->getAffectedRows() != 1) {
			Logger::DB(1,get_class($this)."::".__FUNCTION__."()",$sql); 
			return false;
		}
		
		$this->UpdateRefdata($p);
				
		return true;
	}

	private function UpdateRefdata($p) {
				
	}
	
	public function GetType() {
		return $this->profile_type;
	}
	
	public function GetDurationText() {
		return $this->duration_txt;
	}
		
	public function GetStartDates() {
		return $this->start_dates;
	}
	
	public function GetBenefits() {
		return $this->benefits;
	}

	public function GetRequirements() {
		return $this->requirements;
	}

	public function GetDurationFromId() {
		return $this->duration_from_id;
	}

	public function GetDurationToId() {
		return $this->duration_to_id;
	}

	public function GetPriceFromId() {
		return $this->price_from_id;
	}

	public function GetPriceToId() {
		return $this->price_to_id;
	}
	
	public function GetCurrencyId() {
		return $this->currency_id;
	}
	
}


?>
