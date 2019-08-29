<?php



class TeachingProjectProfile extends CompanyProfile {
	
	const PROFILE_TABLE_NAME = "profile_teaching";
	const PROFILE_LINK_TO_STR = "COMPANY"; // some of the old mapping tables (eg img_map) use string COMPANY as link_to
	const PROFILE_LINK_TO_ID = PROFILE_TEACHING; // newer mapping tables eg refdata use profile_type_id as link to
	
	/* profile specific fields */
	protected $duration_from_id; // int
	protected $duration_to_id; // int
	
	protected $no_teachers; // smallint,
	protected $class_size; // smallint,	
	protected $duration; // smallint,
	protected $salary; // varchar(512),
	protected $benefits; // varchar(512),
	protected $qualifications; // varchar(512),
	protected $requirements; // varchar(512),
	protected $how_to_apply; // varchar(512)
	
	
	public function __Construct() {
		
		parent::__construct();
		
		$this->sTblName = self::PROFILE_TABLE_NAME;
		$this->SetLinkTo(self::PROFILE_LINK_TO_STR);
		
	}
	

	public function GetProfileById($id,$return = array()) {

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
								,c2.duration_from_id
								,c2.duration_to_id
								,c2.no_teachers
								,c2.class_size
								,c2.salary
								,c2.benefits
								,c2.qualifications
								,c2.requirements
								,c2.how_to_apply		
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
					company_id
					,duration_from_id
					,duration_to_id
					,no_teachers
					,class_size
					,salary
					,benefits
					,qualifications
					,requirements
					,how_to_apply
				) VALUES (
					".$p['id']."
					,".$p[PROFILE_FIELD_TEACHING_DURATION_FROM]."
					,".$p[PROFILE_FIELD_TEACHING_DURATION_TO]."
					,".$p[PROFILE_FIELD_TEACHING_NO_TEACHERS]."
					,".$p[PROFILE_FIELD_TEACHING_CLASS_SIZE]."
					,'".$p[PROFILE_FIELD_TEACHING_SALARY]."'
					,'".$p[PROFILE_FIELD_TEACHING_BENEFITS]."'
					,'".$p[PROFILE_FIELD_TEACHING_QUALIFICATIONS]."'
					,'".$p[PROFILE_FIELD_TEACHING_REQUIREMENTS]."'
					,'".$p[PROFILE_FIELD_TEACHING_HOW_TO_APPLY]."'
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
					no_teachers = ".$p[PROFILE_FIELD_TEACHING_NO_TEACHERS]."
					,duration_from_id = ".$p[PROFILE_FIELD_TEACHING_DURATION_FROM]."
					,duration_to_id = ".$p[PROFILE_FIELD_TEACHING_DURATION_TO]."
					,class_size = '".$p[PROFILE_FIELD_TEACHING_CLASS_SIZE]."'
					,salary = '".$p[PROFILE_FIELD_TEACHING_SALARY]."'
					,benefits = '".$p[PROFILE_FIELD_TEACHING_BENEFITS]."'
					,qualifications = '".$p[PROFILE_FIELD_TEACHING_QUALIFICATIONS]."'
					,requirements = '".$p[PROFILE_FIELD_TEACHING_REQUIREMENTS]."'
					,how_to_apply = '".$p[PROFILE_FIELD_TEACHING_HOW_TO_APPLY]."'		
				WHERE company_id = ".$p['id']." ;";
		
		$db->query($sql);
	
		if ($db->getAffectedRows() == 1) {
			return TRUE;
		}
		
	}
	
	/* inject type specific form values into _POST to pre-populate edit form */ 
	public function SetTypeSpecificFormValues() {

		$_POST[PROFILE_FIELD_TEACHING_NO_TEACHERS] = $this->GetNoTeachers();
		$_POST[PROFILE_FIELD_TEACHING_CLASS_SIZE] = $this->GetClassSize();
		$_POST[PROFILE_FIELD_TEACHING_DURATION_FROM] = $this->GetDurationFromId();
		$_POST[PROFILE_FIELD_TEACHING_DURATION_TO] = $this->GetDurationToId();
		$_POST[PROFILE_FIELD_TEACHING_SALARY] = $this->GetSalary();
		$_POST[PROFILE_FIELD_TEACHING_BENEFITS] = $this->GetBenefits();
		$_POST[PROFILE_FIELD_TEACHING_QUALIFICATIONS] = $this->GetQualifications();
		$_POST[PROFILE_FIELD_TEACHING_REQUIREMENTS] = $this->GetRequirements();
		$_POST[PROFILE_FIELD_TEACHING_HOW_TO_APPLY] = $this->GetHowToApply();
			
	}
	
	public function GetNoTeachers() {
		return $this->no_teachers;
	}

	public function GetClassSize() {
		return $this->class_size;
	}
	
	public function GetDurationFromId() {
		return $this->duration_from_id;
	}

	public function GetDurationToId() {
		return $this->duration_to_id;
	}
	
	public function GetSalary() {
		return $this->salary;	
	}
	
	public function GetBenefits() {
		return $this->benefits;
	}
	
	public function GetQualifications() {
		return $this->qualifications;
	}
	
	public function GetRequirements() {
		return $this->requirements;
	}
	
	public function GetHowToApply() {
		return $this->how_to_apply;
	}
	
}

?>