<?


class VolunteerTravelProjectProfile extends CompanyProfile {

	const PROFILE_TABLE_NAME = "profile_volunteer_project";
	const PROFILE_LINK_TO_STR = "COMPANY"; // some of the old mapping tables (eg img_map) use string COMPANY as link_to
	const PROFILE_LINK_TO_ID = PROFILE_VOLUNTEER_PROJECT; // newer mapping tables eg refdata use profile_type_id as link to
	
	protected $duration_from_id; // int
	protected $duration_to_id; // int
	protected $price_from_id; // int
	protected $price_to_id; // int
	protected $currency_id; // int
	
	protected $founded; // varchar(32)
	protected $no_placements; // smallint
	protected $org_type; // smallint
	protected $awards; // varchar(512)
	protected $support; // varchar(512)
	protected $safety; // varchar(512)	
	
	protected $species_list;
	protected $habitats_list;
	protected $species_labels;
	protected $habitats_labels;
	
	
	
	public function __Construct() {
		
		parent::__construct();
		
		$this->sTblName = self::PROFILE_TABLE_NAME;
		$this->SetLinkTo(self::PROFILE_LINK_TO_STR);

		$this->species_list = array();
		$this->habitats_list = array();
		
	}
	
	
	
	public function GetProfileById($id, $return = array()) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		if (!is_numeric($id)) return false;

		parent::__Construct();
		
		parent::SetSubTypeTable($this->GetSubTypeTable());
		parent::SetSubTypeFields($this->GetSubTypeFields());
		
		$oResult = parent::GetProfileById($id, $return = "PROFILE");

		if (!$oResult) return FALSE;

		$this->SetSpeciesList();
		$this->SetHabitatsList();
		
		return TRUE;

	}
	

	private function GetSubTypeTable() {
		return $sSubTypeTable = "LEFT OUTER JOIN ".$this->sTblName." c2 ON c.id = c2.company_id";
	}

	private function GetSubTypeFields() {

		return $sSubTypeFields = "
							,c2.duration_from_id
							,c2.duration_to_id
							,c2.price_from_id
							,c2.price_to_id
							,c2.currency_id
							,c2.founded
							,c2.no_placements
							,c2.org_type
							,c2.awards
							,c2.support
							,c2.safety
							";
	}
	
	
	
	public function DoAddUpdate($c,&$aResponse,$bRedirect = false,$bApproved = true, $tx = TRUE) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$db->query("BEGIN");
		
		/* add/update base company profile record */
		$result = parent::DoAddUpdate($c,$aResponse,$bRedirect = false,$bApproved = true,$bTx = FALSE);

		if (!$result) {
			$db->query("ROLLBACK");
			return FALSE;	
		} else {
			$c['id'] = $aResponse['id']; 	
		}
		

		// add slashes to extended profile fields
		Validation::AddSlashes($c);
		
		/* add/update extended profile subtype record */
		$result = $this->UpdateSubTypeRecord($c);
		
		if (!$result) {
			$aResponse = array();
			$aResponse['msg']['general_error'] = ERROR_COMPANY_PROFILE_EXTENDED_ERROR;
			$db->query("ROLLBACK");
			return FALSE;	
		}

		/* update species refdata mappings */
		$aId = Mapping::GetIdByKey($c,REFDATA_SPECIES_PREFIX);
		Mapping::UpdateRefData("refdata_map",PROFILE_COMPANY,$c['id'],REFDATA_SPECIES, $aId);
		
		/* update habitats refdata mappings */
		$aId = Mapping::GetIdByKey($c,REFDATA_HABITATS_PREFIX);
		Mapping::UpdateRefData("refdata_map",PROFILE_COMPANY,$c['id'],REFDATA_HABITATS, $aId);
		

		$db->query("COMMIT");

		
		return TRUE;
		
	}


	public function AddSubTypeRecord($p) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		if (!is_numeric($p['id'])) return false;

		$sql = "INSERT INTO ".self::PROFILE_TABLE_NAME ." (
					company_id,
					duration_from_id,
					duration_to_id,
					price_from_id,
					price_to_id,
					currency_id,
					founded,
					no_placements,
					org_type,
					awards,
					support,
					safety		
				) VALUES (
					".$p['id']."
					,".$p[PROFILE_FIELD_VOLUNTEER_DURATION_FROM]."
					,".$p[PROFILE_FIELD_VOLUNTEER_DURATION_TO]."
					,".$p[PROFILE_FIELD_VOLUNTEER_PRICE_FROM]."
					,".$p[PROFILE_FIELD_VOLUNTEER_PRICE_TO]."
					,".$p[PROFILE_FIELD_VOLUNTEER_CURRENCY]."
					,'".$p[PROFILE_FIELD_VOLUNTEER_FOUNDED]."'
					,".$p[PROFILE_FIELD_VOLUNTEER_NO_PLACEMENTS]."
					,".$p[PROFILE_FIELD_VOLUNTEER_ORG_TYPE]."
					,'".$p[PROFILE_FIELD_VOLUNTEER_AWARDS]."'
					,'".$p[PROFILE_FIELD_VOLUNTEER_SUPPORT]."'
					,'".$p[PROFILE_FIELD_VOLUNTEER_SAFETY]."'
				);";
	
		$db->query($sql);		
		
		if ($db->getAffectedRows() == 1) {
			return TRUE;
		}
		
	}
	
	
	public function UpdateSubTypeRecord($p) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		if (!is_numeric($p['id'])) return false;

		/* is there an existing sub-type record ? */
		if (!$db->getFirstCell("SELECT 1 FROM ".$this->sTblName." WHERE company_id = ".$p['id'])) {
			return $this->AddSubTypeRecord($p);
		}

		$sql = "UPDATE ".self::PROFILE_TABLE_NAME ." SET
					duration_from_id = ".$p[PROFILE_FIELD_VOLUNTEER_DURATION_FROM]."
					,duration_to_id = ".$p[PROFILE_FIELD_VOLUNTEER_DURATION_TO]."
					,price_from_id = ".$p[PROFILE_FIELD_VOLUNTEER_PRICE_FROM]."
					,price_to_id = ".$p[PROFILE_FIELD_VOLUNTEER_PRICE_TO]."
					,currency_id = ".$p[PROFILE_FIELD_VOLUNTEER_CURRENCY]."
					,founded = '".$p[PROFILE_FIELD_VOLUNTEER_FOUNDED]."'
					,no_placements = ".$p[PROFILE_FIELD_VOLUNTEER_NO_PLACEMENTS]."
					,org_type = ".$p[PROFILE_FIELD_VOLUNTEER_ORG_TYPE]."
					,awards = '".$p[PROFILE_FIELD_VOLUNTEER_AWARDS]."'
					,support = '".$p[PROFILE_FIELD_VOLUNTEER_SUPPORT]."'
					,safety = '".$p[PROFILE_FIELD_VOLUNTEER_SAFETY]."'		
				WHERE company_id = ".$p['id']." ;";
	
		$db->query($sql);
		
		if ($db->getAffectedRows() == 1) {
			return TRUE;
		}
		
	}

	
	/* inject type specific form values into _POST to pre-populate edit form */ 
	public function SetTypeSpecificFormValues() {

		$_POST[PROFILE_FIELD_VOLUNTEER_DURATION_FROM] = $this->GetDurationFromId();
		$_POST[PROFILE_FIELD_VOLUNTEER_DURATION_TO] = $this->GetDurationToId();
		$_POST[PROFILE_FIELD_VOLUNTEER_PRICE_FROM] = $this->GetPriceFromId();
		$_POST[PROFILE_FIELD_VOLUNTEER_PRICE_TO] = $this->GetPriceToId();
		$_POST[PROFILE_FIELD_VOLUNTEER_CURRENCY] = $this->GetCurrencyId();
		$_POST[PROFILE_FIELD_VOLUNTEER_FOUNDED] = $this->GetFounded();
		$_POST[PROFILE_FIELD_VOLUNTEER_NO_PLACEMENTS] = $this->GetNoPlacements();
		$_POST[PROFILE_FIELD_VOLUNTEER_ORG_TYPE] = $this->GetOrgType();
		$_POST[PROFILE_FIELD_VOLUNTEER_AWARDS] = $this->GetAwards();
		$_POST[PROFILE_FIELD_VOLUNTEER_FUNDING] = $this->GetFunding();
		$_POST[PROFILE_FIELD_VOLUNTEER_SUPPORT] = $this->GetSupport();
		$_POST[PROFILE_FIELD_VOLUNTEER_SAFETY] = $this->GetSafety();
					
	}
	
	public function GetDurationFromId() {
		return $this->duration_from_id;
	}

	public function GetDurationToId() {
		return $this->duration_to_id;
	}

	public function GetDurationFromLabel() {
		return $this->GetDurationRefdataObject()->GetValueById($this->duration_from_id);
	}

	public function GetDurationToLabel() {
		return $this->GetDurationRefdataObject()->GetValueById($this->duration_to_id);
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
	
	public function GetCurrencyLabel() {
		return $this->GetCurrencyRefdataObject()->GetValueById($this->currency_id);
	}

	public function GetCostsFromLabel() {
		return $this->GetCostsRefdataObject()->GetValueById($this->price_from_id);
	}

	public function GetCostsToLabel() {
		return $this->GetCostsRefdataObject()->GetValueById($this->price_to_id);
	}

	
	public function GetFounded() {
		return $this->founded;
	}

	protected function SetFounded($founded) {
		$this->founded = $founded;
	}

	public function GetNoPlacements() {
		return $this->no_placements;
	}

	protected function SetNoPlacements($no_placements) {
		$this->no_placements = $no_placements;
	}
	
	public function GetNoPlacementsLabel() {
		return $this->GetNumberOfRefdataObject()->GetValueById($this->no_placements);
	}

	public function GetOrgType() {
		return $this->org_type;
	}

	protected function SetOrgType($org_type) {
		$this->org_type = $org_type;
	}

	public function GetOrgTypeLabel() {
		if (!is_object($oOrgType)) {		
			$oOrgType = new Refdata(REFDATA_ORG_PROJECT_TYPE);
			$oOrgType->GetByType();
			$this->SetOrgTypeRefdataObject($oOrgType);
		}
		
		return $this->GetOrgTypeRefdataObject()->GetValueById($this->org_type);
	}
	
	public function GetAwards() {
		return $this->awards;
	}

	protected function SetAwards($awards) {
		$this->awards = $awards;
	}

	public function GetFunding() {
		return $this->funding;
	}

	protected function SetFunding($funding) {
		$this->funding = $funding;
	}

	public function GetSupport() {
		return $this->support;
	}

	protected function SetSupport($support) {
		$this->support = $support;
	}

	public function GetSafety() {
		return $this->safety;
	}

	protected function SetSafety($safety) {
		$this->safety= $safety;
	}
	
	public function SetSpeciesList() {
		
		$result = Refdata::Get(REFDATA_SPECIES, PROFILE_COMPANY, $this->GetId(), $labels = TRUE);
		 
		$this->species_list = array_keys($result);
		$this->species_labels = array_values($result);

	}
	
	public function GetSpeciesList() {
		return $this->species_list; 
	}
	
	public function SetHabitatsList() {
		
		$result = Refdata::Get(REFDATA_HABITATS, PROFILE_COMPANY, $this->GetId(), $labels = TRUE);
		 
		$this->habitats_list = array_keys($result);
		$this->habitats_labels = array_values($result);
		
	}
	
	public function GetHabitatsList() {
		return $this->habitats_list; 
	}

	public function GetSpeciesLabels() {
		return $this->species_labels;
	}

	public function GetHabitatsLabels() {
		return $this->habitats_labels;
	}
	
	
}



?>
