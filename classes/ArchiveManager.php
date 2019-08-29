<?php



define("EXCEPTION_PLACEMENT_NOT_FOUND", 101);
define("EXCEPTION_PROFILE_HDR_ARCHIVE_FAIL", 102);
define("EXCEPTION_PROFILE_BODY_ARCHIVE_FAIL", 103);
define("EXCEPTION_PROFILE_HDR_DELETE_FAIL", 104);
define("EXCEPTION_PROFILE_BODY_DELETE_FAIL", 105);
define("EXCEPTION_PROFILE_MAPPING_ARCHIVE_FAIL",106);
define("EXCEPTION_PROFILE_TYPE_ERROR", 107);
define("EXCEPTION_COMPANY_PROFILE_NOT_FOUND", 201);
define("EXCEPTION_COMPANY_DELETE_FAIL", 202);
define("EXCEPTION_COMPANY_ARCHIVE_FAIL", 203);
define("EXCEPTION_COMPANY_BODY_ARCHIVE_FAIL", 204);
define("EXCEPTION_COMPANY_BODY_DELETE_FAIL", 205);



class ArchiveManager {
	
	const ARCHIVE_TBL_SUFFIX = '_archive'; // suffix added to DB table names to distinguish them as archive tables
	
	private $object_id; // int id of object (placement or company) profile being archived
	private $profile_type; // int type of profile eg PROFILE_TOUR, PROFILE_JOB etc 
	
	public function __Construct() {
		
	}
	
	public function SetObjectId($id) {
		$this->object_id = $id;
	}
	
	public function GetObjectId() {
		return $this->object_id;
	}

	
	public function ArchiveCompany($id) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		if (!is_numeric($id)) return FALSE;
		
		$this->SetObjectId($id);
		
		try {
			
			$db->query("BEGIN");

			$this->Verify__CompanyProfile();

			$this->Archive__Company();
			$this->Delete__Company();
			
			$this->Archive__CompanyExtendedProfile();
			$this->Delete__CompanyExtendedProfile();
			
			$this->Archive__CompanyMappings();
			$this->Delete__SearchIndexKeywords(1);
			
			$db->query("COMMIT");

			Logger::DB(2,get_class($this)."::".__FUNCTION__."()","ARCHIVE_COMPANY OK : ".$id);
			
			return TRUE;
			
		} catch (Exception $e ) {

			$db->query("ROLLBACK");

			Logger::DB(1,get_class($this)."::".__FUNCTION__."()","ARCHIVE_COMPANY FAIL : ".serialize($e));
			
			return FALSE;
			
		}
	}
			
	
		
	public function ArchivePlacement($id) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		if (!is_numeric($id)) return FALSE;
		
		$this->SetObjectId($id);
		
		try {
			
			$db->query("BEGIN");

			$this->Verify__Placement();
			$this->SetProfileType();
			
			$this->Archive__ProfileHeader();
			$this->Archive__ProfileBody();
			$this->Archive__ProfileMappings();

			$this->Delete__ProfileHeader();
			$this->Delete__ProfileBody();
			$this->Delete__ProfileMappings();

			$this->Delete__SearchIndexKeywords(2);
			
			$db->query("COMMIT");

			Logger::DB(2,get_class($this)."::".__FUNCTION__."()","ARCHIVE_PROFILE OK : ".$id);
			
			return TRUE;
			
		} catch (Exception $e ) {

			$db->query("ROLLBACK");

			Logger::DB(1,get_class($this)."::".__FUNCTION__."()","ARCHIVE_PROFILE FAIL : ".serialize($e));
			
			return FALSE;
			
		}
				

	}

	private function Verify__Placement() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$sql = "SELECT 1 FROM profile_hdr WHERE id = ".$this->GetObjectId().";";
		
		$db->query($sql);
		
		if ($db->getNumRows() != 1) throw new Exception("Placement not found, id: ".$this->GetObjectId(),EXCEPTION_PLACEMENT_NOT_FOUND);
				
	}

	private function Verify__CompanyProfile() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$sql = "SELECT 1 FROM company WHERE id = ".$this->GetObjectId().";";
		
		$db->query($sql);
		
		if ($db->getNumRows() != 1) throw new Exception("Company profile not found, id: ".$this->GetObjectId(),EXCEPTION_COMPANY_PROFILE_NOT_FOUND);
				
	}
	
	
	private function SetProfileType() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$sql = "SELECT type FROM profile_hdr WHERE id = ".$this->GetObjectId().";";
		
		$this->profile_type = $db->getFirstCell($sql);
		
		if (!is_numeric($this->profile_type))  throw new Exception("Invalid profile_type id for placement, id: ".$this->GetObjectId(),EXCEPTION_PROFILE_TYPE_ERROR);
		
	}
	
	private function GetProfileType() {
		return $this->profile_type;
	}
	
	private function Archive__ProfileHeader() {
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$sql = "INSERT INTO profile_hdr_archive (SELECT * FROM profile_hdr WHERE id = ".$this->GetObjectId().");";
		
		$db->query($sql);
		
		if ($db->getAffectedRows() != 1) throw new Exception("Failed to copy profile, id: ".$this->GetObjectId(),EXCEPTION_PROFILE_HDR_ARCHIVE_FAIL);
	
	}
	
	
	private function Archive__ProfileBody() {
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		if ($this->GetProfileType() == PROFILE_VOLUNTEER) {
						
			$sql = "INSERT INTO profile_general_archive ( SELECT * FROM profile_general WHERE p_hdr_id = ".$this->GetObjectId().");";
			
		} elseif ($this->GetProfileType() == PROFILE_TOUR) {

			$sql = "INSERT INTO profile_tour_archive ( SELECT * FROM profile_tour WHERE p_hdr_id = ".$this->GetObjectId()." );";
			
		} elseif ($this->GetProfileType() == PROFILE_JOB) {

			$sql = "INSERT INTO profile_job_archive ( SELECT * FROM profile_job WHERE p_hdr_id = ".$this->GetObjectId()." );";
		}
		
		$db->query($sql);
				
		if ($db->getAffectedRows() != 1) throw new Exception("Failed to copy profile, id: ".$this->GetObjectId(),EXCEPTION_PROFILE_BODY_ARCHIVE_FAIL);
	
	}

	
	private function Archive__ProfileMappings() {
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;

		$pre_count = 0;
		$post_count = 0;
		
		$sql = "SELECT count(*) FROM prod_cat_map WHERE prod_id = ".$this->GetObjectId().";";
		$pre_count = $db->getFirstCell($sql);
		
		$sql = "INSERT INTO prod_cat_map_archive ( SELECT * FROM prod_cat_map WHERE prod_id = ".$this->GetObjectId()." );";
		$db->query($sql);

		$sql = "SELECT count(*) FROM prod_cat_map_archive WHERE prod_id = ".$this->GetObjectId().";";
		$post_count = $db->getFirstCell($sql);
		
		if ($pre_count != $post_count) throw new Exception("Failed to archive prod_cat_map, id: ".$this->GetObjectId(),EXCEPTION_PROFILE_MAPPING_ARCHIVE_FAIL);

		
		$pre_count = 0;
		$post_count = 0;
		
		$sql = "SELECT count(*) FROM prod_act_map WHERE prod_id = ".$this->GetObjectId().";";
		$pre_count = $db->getFirstCell($sql);
		
		$sql = "INSERT INTO prod_act_map_archive ( SELECT * FROM prod_act_map WHERE prod_id = ".$this->GetObjectId().");";
		$db->query($sql);

		$sql = "SELECT count(*) FROM prod_act_map_archive WHERE prod_id = ".$this->GetObjectId().";";
		$post_count = $db->getFirstCell($sql);
		
		if ($pre_count != $post_count) throw new Exception("Failed to archive prod_act_map, id: ".$this->GetObjectId(),EXCEPTION_PROFILE_MAPPING_ARCHIVE_FAIL);
		

		$pre_count = 0;
		$post_count = 0;
		
		$sql = "SELECT count(*) FROM prod_country_map WHERE prod_id = ".$this->GetObjectId().";";
		$pre_count = $db->getFirstCell($sql);
		
		$sql = "INSERT INTO prod_country_map_archive ( SELECT * FROM prod_country_map WHERE prod_id = ".$this->GetObjectId().");";
		$db->query($sql);

		$sql = "SELECT count(*) FROM prod_country_map_archive WHERE prod_id = ".$this->GetObjectId().";";
		$post_count = $db->getFirstCell($sql);
		
		if ($pre_count != $post_count) throw new Exception("Failed to archive prod_country_map, id: ".$this->GetObjectId(),EXCEPTION_PROFILE_MAPPING_ARCHIVE_FAIL);
		

		$pre_count = 0;
		$post_count = 0;
		
		$sql = "SELECT count(*) FROM image_map WHERE link_to = 'PLACEMENT' AND link_id = ".$this->GetObjectId().";";
		$pre_count = $db->getFirstCell($sql);
		
		$sql = "INSERT INTO image_map_archive ( SELECT * FROM image_map WHERE link_to = 'PLACEMENT' AND link_id = ".$this->GetObjectId()." );";
		$db->query($sql);

		$sql = "SELECT count(*) FROM image_map_archive WHERE link_to = 'PLACEMENT' AND link_id = ".$this->GetObjectId().";";
		$post_count = $db->getFirstCell($sql);
		
		if ($pre_count != $post_count) throw new Exception("Failed to archive image_map, id: ".$this->GetObjectId(),EXCEPTION_PROFILE_MAPPING_ARCHIVE_FAIL);
		
	}

	
	private function Delete__ProfileHeader() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;

		$sql = "DELETE FROM profile_hdr WHERE id = ".$this->GetObjectId().";";
		
		$db->query($sql);

		if ($db->getAffectedRows() != 1) {
			throw new Exception("Failed to copy profile, id: ".$this->GetObjectId(),EXCEPTION_PROFILE_HDR_DELETE_FAIL);
			die("here");
		}
		
	} 

	
	private function Delete__ProfileBody() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$sql = "";
		
		if ($this->GetProfileType() == PROFILE_VOLUNTEER) {
						
			$sql = "DELETE FROM profile_general WHERE p_hdr_id = ".$this->GetObjectId().";";
			
		} elseif ($this->GetProfileType() == PROFILE_TOUR) {

			$sql = "DELETE FROM profile_tour WHERE p_hdr_id = ".$this->GetObjectId().";";
			
		} elseif ($this->GetProfileType() == PROFILE_JOB) {

			$sql = "DELETE FROM profile_job WHERE p_hdr_id = ".$this->GetObjectId().";";
		}
		
		$db->query($sql);
		
		if ($db->getAffectedRows() != 1) throw new Exception("Failed to copy profile, id: ".$this->GetObjectId(),EXCEPTION_PROFILE_BODY_DELETE_FAIL);
	} 

	
	private function Delete__ProfileMappings() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$sql = "DELETE FROM prod_cat_map WHERE prod_id = ".$this->GetObjectId().";";

		$db->query($sql);
		
		$sql = "DELETE FROM prod_act_map WHERE prod_id = ".$this->GetObjectId().";";

		$db->query($sql);
		
		$sql = "DELETE FROM prod_country_map WHERE prod_id = ".$this->GetObjectId().";";

		$db->query($sql);
		
		$sql = "DELETE FROM image_map WHERE link_to = 'PLACEMENT' AND link_id = ".$this->GetObjectId().";";
		
		$db->query($sql);
		
		
	} 
	
	
	
	
	private function Delete__SearchIndexKeywords($type) {
		
		if (!is_numeric($type)) return FALSE;
		
		global $db;
		
		$sql = "DELETE FROM keyword_idx_2 WHERE type = ".$type." AND id = ".$this->GetObjectId().";";
		
		$db->query($sql);
	
	}

	private function Archive__CompanyMappings() {
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;

		$pre_count = 0;
		$post_count = 0;
		
		$sql = "SELECT count(*) FROM comp_cat_map WHERE company_id = ".$this->GetObjectId().";";
		$pre_count = $db->getFirstCell($sql);
		
		$sql = "INSERT INTO comp_cat_map_archive ( SELECT * FROM comp_cat_map WHERE company_id = ".$this->GetObjectId()." );";
		$db->query($sql);

		$sql = "SELECT count(*) FROM comp_cat_map_archive WHERE company_id = ".$this->GetObjectId().";";
		$post_count = $db->getFirstCell($sql);
		
		if ($pre_count != $post_count) throw new Exception("Failed to archive comp_cat_map, id: ".$this->GetObjectId(),EXCEPTION_PROFILE_MAPPING_ARCHIVE_FAIL);

		
		$pre_count = 0;
		$post_count = 0;
		
		$sql = "SELECT count(*) FROM comp_act_map WHERE company_id = ".$this->GetObjectId().";";
		$pre_count = $db->getFirstCell($sql);
		
		$sql = "INSERT INTO comp_act_map_archive ( SELECT * FROM comp_act_map WHERE company_id = ".$this->GetObjectId().");";
		$db->query($sql);

		$sql = "SELECT count(*) FROM comp_act_map_archive WHERE company_id = ".$this->GetObjectId().";";
		$post_count = $db->getFirstCell($sql);
		
		if ($pre_count != $post_count) throw new Exception("Failed to archive comp_act_map, id: ".$this->GetObjectId(),EXCEPTION_PROFILE_MAPPING_ARCHIVE_FAIL);
		

		$pre_count = 0;
		$post_count = 0;
		
		$sql = "SELECT count(*) FROM comp_country_map WHERE company_id = ".$this->GetObjectId().";";
		$pre_count = $db->getFirstCell($sql);
		
		$sql = "INSERT INTO comp_country_map_archive ( SELECT * FROM comp_country_map WHERE company_id = ".$this->GetObjectId().");";
		$db->query($sql);

		$sql = "SELECT count(*) FROM comp_country_map_archive WHERE company_id = ".$this->GetObjectId().";";
		$post_count = $db->getFirstCell($sql);
		
		if ($pre_count != $post_count) throw new Exception("Failed to archive comp_country_map, id: ".$this->GetObjectId(),EXCEPTION_PROFILE_MAPPING_ARCHIVE_FAIL);
		

		$pre_count = 0;
		$post_count = 0;
		
		$sql = "SELECT count(*) FROM image_map WHERE link_to = 'COMPANY' AND link_id = ".$this->GetObjectId().";";
		$pre_count = $db->getFirstCell($sql);
		
		$sql = "INSERT INTO image_map_archive ( SELECT * FROM image_map WHERE link_to = 'COMPANY' AND link_id = ".$this->GetObjectId()." );";
		$db->query($sql);

		$sql = "SELECT count(*) FROM image_map_archive WHERE link_to = 'COMPANY' AND link_id = ".$this->GetObjectId().";";
		$post_count = $db->getFirstCell($sql);
		
		if ($pre_count != $post_count) throw new Exception("Failed to archive company image_map, id: ".$this->GetObjectId(),EXCEPTION_PROFILE_MAPPING_ARCHIVE_FAIL);
		
	}

	private function Archive__Company() {
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$sql = "INSERT INTO company_archive (SELECT * FROM company WHERE id = ".$this->GetObjectId().");";
		
		$db->query($sql);
		
		if ($db->getAffectedRows() != 1) throw new Exception("Failed to copy company , id: ".$this->GetObjectId(),EXCEPTION_COMPANY_ARCHIVE_FAIL);
	
	}
	
	
	private function Delete__Company() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;

		$sql = "DELETE FROM company WHERE id = ".$this->GetObjectId().";";
		
		$db->query($sql);

		if ($db->getAffectedRows() != 1) {
			throw new Exception("Failed to delete company, id: ".$this->GetObjectId(),EXCEPTION_COMPANY_DELETE_FAIL);
			die("here");
		}
		
	} 
	
	
	
	private function Archive__CompanyExtendedProfile() {
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$sql = "";
		
		if ($this->GetProfileType() == PROFILE_SUMMERCAMP) {
						
			$sql = "INSERT INTO profile_summercamp_archive ( SELECT * FROM profile_summercamp WHERE company_id = ".$this->GetObjectId().");";
			
		} elseif ($this->GetProfileType() == PROFILE_VOLUNTER_PROJECT) {

			$sql = "INSERT INTO profile_volunteer_project_archive ( SELECT * FROM profile_volunteer_project WHERE company_id = ".$this->GetObjectId()." );";
			
		} elseif ($this->GetProfileType() == PROFILE_SEASONALJOBS) {

			$sql = "INSERT INTO profile_seasonaljobs_archive ( SELECT * FROM profile_seasonaljobs WHERE company_id = ".$this->GetObjectId()." );";
			
		} elseif ($this->GetProfileType() == PROFILE_TEACHING) {

			$sql = "INSERT INTO profile_teaching_archive ( SELECT * FROM profile_teaching WHERE company_id = ".$this->GetObjectId()." );";
		}
		
		if (strlen($sql) > 1) {
			$db->query($sql);
					
			if ($db->getAffectedRows() != 1) throw new Exception("Failed to copy extended comp profile, id: ".$this->GetObjectId(),EXCEPTION_COMPANY_BODY_ARCHIVE_FAIL);

		}
	}

	private function Delete__CompanyExtendedProfile() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$sql = "";
		
		if ($this->GetProfileType() == PROFILE_SUMMERCAMP) {
						
			$sql = "DELETE FROM profile_summercamp WHERE company_id = ".$this->GetObjectId().";";
			
		} elseif ($this->GetProfileType() == PROFILE_VOLUNTER_PROJECT) {

			$sql = "DELETE FROM profile_volunteer_project WHERE company_id = ".$this->GetObjectId().";";
			
		} elseif ($this->GetProfileType() == PROFILE_SEASONALJOBS) {

			$sql = "DELETE FROM profile_seasonaljobs WHERE company_id = ".$this->GetObjectId().";";
			
		} elseif ($this->GetProfileType() == PROFILE_TEACHING) {

			$sql = "DELETE FROM profile_teaching WHERE company_id = ".$this->GetObjectId().";";
		}
			
		if (strlen($sql) > 1) {
			
			$db->query($sql);
			
			if ($db->getAffectedRows() != 1) throw new Exception("Failed delete comp extended profile, id: ".$this->GetObjectId(),EXCEPTION_COMPANY_BODY_DELETE_FAIL);
			
		}
		
	} 
	
	
}



?>