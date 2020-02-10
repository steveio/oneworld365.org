<?

class SeasonalJobEmployerProfile extends CompanyProfile {

	
	const PROFILE_TABLE_NAME = "profile_seasonaljobs";
	const PROFILE_LINK_TO_STR = "COMPANY"; // some of the old mapping tables (eg img_map) use string COMPANY as link_to
	const PROFILE_LINK_TO_ID = PROFILE_SEASONALJOBS; // newer mapping tables eg refdata use profile_type_id as link to
	

	// extended profile specific fields
	protected $duration_from_id; // int
	protected $duration_to_id; // int
	
	protected $pay; // varchar(512)
	protected $benefits; // varchar(512),
	protected $no_staff; // smallint refdata.int_range 
	protected $job_types; // varchar(512), 
	protected $how_to_apply; // varchar(512)
	protected $requirements; // varchar(512)

	
	public function __Construct() {
		
		parent::__construct();
		
		$this->sTblName = self::PROFILE_TABLE_NAME;
		$this->SetLinkTo(self::PROFILE_LINK_TO_STR);
	
	}
	

	
	public function GetProfileById($id, $return = array()) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		if (!is_numeric($id)) return false;

		parent::__Construct();
		
		
		parent::SetSubTypeTable($this->GetSubTypeTable());
		parent::SetSubTypeFields($this->GetSubTypeFields());

		
		$oResult = parent::GetProfileById($id, $return = "PROFILE");

		if (!$oResult) return FALSE;
		
		
		return TRUE;

	}
	

	private function GetSubTypeTable() {
		return $sSubTypeTable = "LEFT OUTER JOIN ".$this->sTblName." c2 ON c.id = c2.company_id";
	}

	private function GetSubTypeFields() {

		return $sSubTypeFields = "
							,c2.job_types
							,c2.duration_from_id
							,c2.duration_to_id
							,c2.pay
							,c2.benefits
							,c2.no_staff
							,c2.how_to_apply
							,c2.requirements		
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
		
				
		$db->query("COMMIT");

		
		return TRUE;
		
	}

	public function AddSubTypeRecord($p) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		if (!is_numeric($p['id'])) return false;

		$sql = "INSERT INTO ".self::PROFILE_TABLE_NAME ." (
					company_id,
					job_types,
					duration_from_id,
					duration_to_id,
					pay,
					benefits,
					no_staff,
					how_to_apply,
					requirements					
				) VALUES (
					".$p['id']."
					,'".$p[PROFILE_FIELD_SEASONALJOBS_JOB_TYPES]."'
					,".$p[PROFILE_FIELD_SEASONALJOBS_DURATION_FROM]."
					,".$p[PROFILE_FIELD_SEASONALJOBS_DURATION_TO]."
					,'".$p[PROFILE_FIELD_SEASONALJOBS_PAY]."'
					,'".$p[PROFILE_FIELD_SEASONALJOBS_BENEFITS]."'
					,".$p[PROFILE_FIELD_SEASONALJOBS_NO_STAFF]."
					,'".$p[PROFILE_FIELD_SEASONALJOBS_HOW_TO_APPLY]."'
					,'".$p[PROFILE_FIELD_SEASONALJOBS_REQUIREMENTS]."'
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
					job_types = '".$p[PROFILE_FIELD_SEASONALJOBS_JOB_TYPES]."',
					duration_from_id = ".$p[PROFILE_FIELD_SEASONALJOBS_DURATION_FROM].",
					duration_to_id = ".$p[PROFILE_FIELD_SEASONALJOBS_DURATION_TO].",
					pay = '".$p[PROFILE_FIELD_SEASONALJOBS_PAY]."',
					benefits = '".$p[PROFILE_FIELD_SEASONALJOBS_BENEFITS]."',
					no_staff = ".$p[PROFILE_FIELD_SEASONALJOBS_NO_STAFF].",
					how_to_apply = '".$p[PROFILE_FIELD_SEASONALJOBS_HOW_TO_APPLY]."',
					requirements = '".$p[PROFILE_FIELD_SEASONALJOBS_REQUIREMENTS]."'		
				WHERE company_id = ".$p['id']." ;";
	
		$db->query($sql);
	
		if ($db->getAffectedRows() == 1) {
			return TRUE;
		}
		
	}
	
	
	/* inject type specific form values into _POST to pre-populate edit form */ 
	public function SetTypeSpecificFormValues() {

		$_POST[PROFILE_FIELD_SEASONALJOBS_DURATION_FROM] = $this->GetDurationFromId();
		$_POST[PROFILE_FIELD_SEASONALJOBS_DURATION_TO] = $this->GetDurationToId();
		$_POST[PROFILE_FIELD_SEASONALJOBS_PAY] = $this->GetPay();
		$_POST[PROFILE_FIELD_SEASONALJOBS_BENEFITS] = $this->GetBenefits();
		$_POST[PROFILE_FIELD_SEASONALJOBS_NO_STAFF] = $this->GetNoStaff();
		$_POST[PROFILE_FIELD_SEASONALJOBS_JOB_TYPES] = $this->GetJobTypes();
		$_POST[PROFILE_FIELD_SEASONALJOBS_HOW_TO_APPLY] = $this->GetHowToApply();
		$_POST[PROFILE_FIELD_SEASONALJOBS_REQUIREMENTS] = $this->GetRequirements();
			
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
	
	
	
	public function GetPay() {
		return $this->pay;
	}

	protected function SetPay($pay) {
		$this->pay = $pay;
	}

	public function GetBenefits() {
		return $this->benefits;
	}

	protected function SetBenefits($benefits) {
		$this->benefits = $benefits;
	}

	public function GetNoStaff() {
		return $this->no_staff;
	}
	
	public function GetNoStaffLabel() {

		$oNoStaff = Refdata::GetInstance(REFDATA_INT_RANGE);
		$oNoStaff->SetOrderBySql(' id ASC');
		$oNoStaff->SetElementName(PROFILE_FIELD_SEASONALJOBS_NO_STAFF);
		$oNoStaff->GetByType();
		
		return $oNoStaff->GetValueById($this->no_staff);		
				
	}

	protected function SetNoStaff($no_staff) {
		$this->no_staff = $no_staff;
	}

	public function GetJobTypes() {
		return $this->job_types;
	} 

	protected function SetJobTypes($job_types) {
		$this->job_types = $job_types;		
	}

	public function GetHowToApply() {
		return $this->how_to_apply;
	}

	protected function SetHowToApply($how_to_apply) {
		$this->how_to_apply = $how_to_apply;
	}

	public function GetRequirements() {
		return $this->requirements;
	}

	protected function SetRequirements($requirements) {
		$this->requirements = $requirements;
	}

	
}


?>
	

