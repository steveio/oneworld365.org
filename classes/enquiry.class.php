<?php



define("ENQUIRY_GENERAL",0);
define("ENQUIRY_BOOKING",1);
define("ENQUIRY_JOB_APP",2);


class Enquiry {

	private $id;
	private $link_to;
	private $link_id;
	private $name;
	private $country;
	private $country_name;
	private $email;
	private $tel;
	private $enq_type;
	private $grp_size;
	private $budget;
	private $currency;
	private $dep_dt;
	private $enquiry;
	private $contact_type;
	private $brochure_type;
	private $addr1;
	private $addr2;
	private $addr3;
	private $apply_letter;
	private $experience;
	private $dob;
	private $ip_addr;
	private $date;
	private $status;
	private $site_id;
	private $site_name;

	private $company_name;
	private $company_url_name;
	private $placement_name;
	private $placement_url_name;
	
	private $oProfile;

	public function __Construct() {

	}

	public function GetEnquiryType() {
		return $this->enq_type;
	}

	public function SetEnquiryType($sEnqType) {
		$this->enq_type = $sEnqType;
	}

	public function GetId() {
		return $this->id;
	}

	public function SetId($id) {
		$this->id = $id;
	}

	public function GetLinkId() {
		return $this->link_id;
	}

	public function SetLinkId($iLinkId) {
		$this->link_id = $iLinkId;
	}

	public function GetLinkTo() {
		return $this->link_to;
	}

	public function SetLinkTo($sLinkTo) {
		$this->link_to = $sLinkTo;
	}


	public function GetName() {
		return $this->name;
	}


	public function GetCountryId() {
		return $this->country;
	}

	public function GetCountryName() {
		return $this->country_name;
	}

	public function GetEmail() {
		return $this->email;
	}

	public function GetTel() {
		return $this->tel;
	}

	public function GetGroupSize() {
		return $this->grp_size;
	}

	public function GetBudget() {
		return $this->budget;
	}

	public function GetDeptDate() {
		return $this->dep_dt;
	}

	public function GetEnquiry() {
		return $this->enquiry;
	}

	public function GetContactType() {
		return $this->contact_type;
	}

	public function GetBrochureType() {
		return $this->brochure_type;
	}

	public function GetAddr1() {
		return $this->addr1;
	}

	public function GetAddr2() {
		return $this->addr2;
	}

	public function GetAddr3() {
		return $this->addr3;
	}

	public function GetApplyLetter() {
		return $this->apply_letter;
	}

	public function GetExperience() {
		return $this->experience;
	}

	public function GetDOB() {
		return $this->dob;
	}

	public function GetIpAddr() {
		return $this->ip_addr;
	}

	public function GetDate() {
		return $this->date;
	}

	public function GetProfile() {
		return $this->oProfile;
	}

	public function SetProfile($oProfile) {
		$this->oProfile = $oProfile;
	}

	public function GetStatus() {
		return $this->status;
	}

	public function GetSiteId() {
		return $this->site_id;
	}

	public function GetSiteName() {
		return $this->site_name;
	}

	public function GetCompanyName() {
		return $this->company_name;
	}
	
	public function GetCompanyUrlName() {
		return $this->company_url_name;
	}

	public function GetPlacementName() {
		return $this->placement_name;
	}
	
	public function GetPlacementUrlName() {
		return $this->placement_url_name;
	}
	
	public function SetStatus($id,$iStatus) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		if ((!is_numeric($id)) ||(!is_numeric($iStatus))) return false;

		$db->query("UPDATE enquiry SET status = ".$iStatus ." WHERE id = ".$id);
	}

	public function GetStatusLabel() {
		switch($this->status) {
			case 0 :
				return "Pending";
				break;
			case 1 :
				return "Approved";
				break;
			case 2 :
				return "Sent";
				break;
			case 3 :
				return "Rejected";
				break;
			case 4 :
				return "Failed (no addr)";
				break;
			case 5 :
			    return "Sent - No Auto-Response"; // historical enquiries pre-auto response implementation
			    break;
			case 6 :
			    return "Sent - Auto-Response Failed";
			    break;			    
			case 7 :
			    return "Sent - Auto-Response Sent";
			    break;

		}
	}


	public function GetEnquiryTypeLabel() {

		switch($this->GetEnquiryType()) {
			case "BOOKING" :
				$sEnquiryTypeLabel = "Booking Enquiry";
				break;
			case "GENERAL" :
				$sEnquiryTypeLabel = "General Enquiry";
				break;
			case "BROCHURE" :
				$sEnquiryTypeLabel = "Brochure Request";
				break;
			case "JOB_APP" :
				$sEnquiryTypeLabel = "Job Application";
				break;
		}

		return $sEnquiryTypeLabel;
	}

	public function GetByStatus($iStatus,$iSiteId) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		if ((!is_numeric($iStatus)) || (!is_numeric($iSiteId))) return false;

		$db->query("SELECT id FROM enquiry WHERE status = ".$iStatus. " AND site_id = ".$iSiteId);

		if ($db->getNumRows() >= 1) {
			$aTmp = $db->getRows();
			$aId = array();
			foreach($aTmp as $k => $v) {
				$aId[] = $v['id'];
			}
			return $aId;
		} else {
		    return array();
		}
	}

	public function GetById($id) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		$sql = "SELECT
					e.id
					,e.link_to
					,e.link_id
					,e.name
					,e.country
					,e.email
					,e.tel
					,e.enq_type
					,e.grp_size
					,e.budget
					,dep_dt
					,e.enquiry
					,e.contact_type 
					,e.brochure_type
					,e.addr1
					,e.addr2
					,e.addr3
					,e.apply_letter
					,e.experience
					,e.site_id
					,w.name as site_name
					,to_char(e.dob,'DD/MM/YYYY HH24:MI:SS') as dob
					,e.ip_addr
					,to_char(e.date,'DD/MM/YYYY HH24:MI:SS') as date
					,e.status,
					c.name as country_name
				FROM enquiry e,
					country c,	
					website w
				WHERE e.id = ".$id ."
				AND e.country = c.id
				AND e.site_id = w.id
				";

		$db->query($sql);

		if ($db->getNumRows() == 1) {
			$aResult = $db->getRow(PGSQL_ASSOC);
			foreach($aResult as $k => $v) {
				$this->$k = is_string($v) ? stripslashes($v) : $v;
			}
		} else {
			return false;
		}
	}


	public function GetAll($iStatusFrom = 0, $iStatusTo = 1, $iLimit = 50,$company_id = NULL) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");


		global $db,$_CONFIG;

		$sLimit = (is_numeric($iLimit)) ? "LIMIT ".$iLimit : "";
		
		/*
		 * filter report by company id : 
		 * enquiry table holds company & placement enquiries
		 * we must lookup these seperately and then sort the results
		 */
		if (is_numeric($company_id)) {
			
			/* get the company enquiry results */
			$sql_filter_company = " e.link_to = '0' AND e.link_id = ".$company_id;
			$join = " LEFT OUTER JOIN ".$_CONFIG['company_table']." comp ON comp.id = e.link_id ";
			$join_fields = "comp.title as company_name,comp.url_name as company_url_name,comp.id as company_id ";
			$aCompEnquiry = $this->GetEnquiryResults($join, $join_fields, $sql_filter_company, $iStatusFrom, $iStatusTo, $sLimit);
			
			/* get placement enquiry result */
			$sql = "SELECT id FROM ".$_CONFIG['profile_hdr_table']." WHERE company_id = ".$company_id;
			$db->query($sql);
			if ($db->getNumRows() >= 1) {	
				for($i=0;$i<$db->getNumRows();$i++) {
					$row = $db->getRow();
					$aProfileId[] = $row['id']; 
				}
			}
			if (count($aProfileId)) {
				$sql_filter_placement = " e.link_to = '1' AND e.link_id IN (".implode(",",$aProfileId).") ";
				$join = " LEFT OUTER JOIN ".$_CONFIG['profile_hdr_table']." p ON p.id = e.link_id ";
				$join .= " LEFT OUTER JOIN ".$_CONFIG['company_table']." comp ON p.company_id = comp.id ";
				$join_fields = "p.title as placement_name,p.url_name as placement_url_name,p.company_id, comp.title as company_name, comp.url_name as company_url_name ";
				$aPlacementEnquiry = $this->GetEnquiryResults($join, $join_fields,$sql_filter_placement, $iStatusFrom, $iStatusTo, $sLimit);
			} else {
				$aPlacementEnquiry = array();
			}
			
		} else {
			/* no company filter - return all enquiries */
			$sql_filter_company = " e.link_to = '0' ";
			$join = " LEFT OUTER JOIN ".$_CONFIG['company_table']." comp ON comp.id = e.link_id ";
			$join_fields = "comp.title as company_name,comp.url_name as company_url_name,comp.id as company_id ";
			$aCompEnquiry = $this->GetEnquiryResults($join, $join_fields, $sql_filter_company, $iStatusFrom, $iStatusTo, $sLimit);

			$sql_filter_placement = " e.link_to = '1' ";
			$join = " LEFT OUTER JOIN ".$_CONFIG['profile_hdr_table']." p ON p.id = e.link_id ";
			$join .= " LEFT OUTER JOIN ".$_CONFIG['company_table']." comp ON p.company_id = comp.id ";
			$join_fields = "p.title as placement_name,p.url_name as placement_url_name,p.company_id, comp.title as company_name, comp.url_name as company_url_name ";
			$aPlacementEnquiry = $this->GetEnquiryResults($join, $join_fields,$sql_filter_placement, $iStatusFrom, $iStatusTo, $sLimit);
			
		}
		
		$aResult = array();
		
		/* merge and sort (recent first) company and placement results */
		for($i=0;$i<count($aCompEnquiry);$i++) {
			$aResult[$aCompEnquiry[$i]->id] = $aCompEnquiry[$i]; 	
		}
		for($i=0;$i<count($aPlacementEnquiry);$i++) {
			$aResult[$aPlacementEnquiry[$i]->id] = $aPlacementEnquiry[$i]; 	
		}
		
		ksort($aResult);
		return array_reverse($aResult);


	}


	private function GetEnquiryResults($join, $join_field_sql, $sql_filter, $iStatusFrom, $iStatusTo, $sLimit) {

		global $db,$_CONFIG;
		
		$sql = "SELECT
		e.id
		,e.link_to
		,e.link_id
		,e.name
		,e.country
		,e.email
		,e.tel
		,e.enq_type
		,e.grp_size
		,e.budget
		,dep_dt
		,e.enquiry
		,e.contact_type
		,e.brochure_type
		,e.addr1
		,e.addr2
		,e.addr3
		,e.apply_letter
		,e.experience
		,to_char(e.dob,'DD/MM/YYYY HH24:MI:SS') as dob
		,e.ip_addr
		,to_char(e.date,'DD/MM/YYYY HH24:MI:SS') as date
		,e.status
		,e.site_id
		,w.name as site_name
		,c.name as country_name
		,".$join_field_sql."
		FROM
		enquiry e ".$join."
		,country c
		,website w
		WHERE
		$sql_filter
		AND e.status >= $iStatusFrom
		AND e.status <= $iStatusTo
		AND e.country = c.id
		AND e.site_id = w.id
		ORDER BY id DESC
		$sLimit
				";
		
		$db->query($sql);

		$aResult = array();

		if ($db->getNumRows() >= 1) {
			$aRows = $db->getRows(PGSQL_ASSOC);
			foreach($aRows as $aRow) {
				$oEnquiry = new Enquiry();
				$oEnquiry->SetFromArray($aRow);
				$aResult[] = $oEnquiry;
			}
		}

		return $aResult;
		
	}
	
	public function GetNextId() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		$this->SetId($db->getFirstCell("SELECT nextval('enquiry_seq')"));

		return $this->GetId();

	}

	public static function GetRequestUrl($sEnquiryType,$iProfileId,$sProfileType) {

		global $_CONFIG;

		//$sUrl = $_CONFIG['url']."/enquiry/?&q=";
		$sUrl = $_CONFIG['url']."/enquiry?&q=";

		$sQs = base64_encode($sEnquiryType."::".$iProfileId."::".$sProfileType);

		return $sUrl.$sQs;

	}

	/*
	 * Get a list of enquiry types
	 *
	 */
	public function GetEnquiryTypes() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		$db->query("SELECT name,code,sort_order FROM enquiry_type ORDER BY sort_order ASC;");

		if ($db->GetNumRows() >= 1) {
			$aRes = $db->getRows(PGSQL_ASSOC);

			foreach($aRes as $aRow) {
					
				$this->aEnquiry[$aRow['sort_order']] = array('name' => $aRow['name']
				,'code' => $aRow['name']
				,'sort_order' => $aRow['sort_order']
				);
			}
		}
			
	}


	public function Process($a,&$aResponse) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		Validation::Sanitize($a);

		Validation::AddSlashes($a);

		if (!$this->Validate($a,$aResponse)) return false;


		/* check this isn't a duplicate submission */
		$db->query("SELECT id FROM enquiry WHERE id = ".$this->GetId());
		if ($db->getNumRows() == 1) return true;


		if ($a['enquiry_type'] == "JOB_APP") {
			if (!$this->ProcessCV($aResponse)) return false;
		}

		/* store the enquiry details in a queue table */
		if (!$this->Add($a,$aResponse)) return false;

		return true;
	}


	private function ProcessCV(&$aResponse) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db, $_CONFIG;

		if (!is_numeric($this->GetId())) return false;


		/* define permitted filetypes */
		$sField = "candidate_cv";
		$aExt = array("doc","docx","pdf","rtf","txt");
		$aMimeType = array("application/msword","application/vnd.openxmlformats-officedocument.wordprocessingml.document", "application/pdf","text/rtf","text/plain","application/octet-stream");
		$iMaxSize = 1572864;
		$sTmpPath = $_CONFIG['root_path'] . "/upload/cv";
		$sFilePrefix = "cv_";
		$sFileName = $sFilePrefix.$this->GetId();


		if (File::Upload($sField,$aExt,$aMimeType,$iMaxSize,$sTmpPath,$sFileName,$response)) {
				
			if (DEBUG) Logger::Msg("File Upload OK");
				
			if ($response['isError']) {
			  return false;
			}

			$response['file']['name'] = preg_replace("/ /","_",$response['file']['name']);
			$sName = preg_replace("/[^a-zA-Z0-9\-_\.]/","",$response['file']['name']);
				
			$sql = "INSERT INTO cv (id
										,enquiry_id
										,name
										,size
										,ext
										,mime
										) VALUES (
										nextval('cv_seq')
										,".$this->GetId()."
										,'".addslashes(strtoupper($sName))."'
										,".$response['file']['size']."							
										,'".addslashes(strtoupper($response['file']['ext']))."'
										,'".$response['file']['type']."'
										);";
			$db->query($sql);
				
			if ($db->getAffectedRows() == 1) {
				return true;
			} else {
				$aResponse['msg']['candidate_cv'] = 'DB insert error';
			}
				
		} else {
			$aResponse['msg']['candidate_cv'] = $response['msg']['error'];
		}
	}

	private function Validate($a,&$aResponse) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		if (strlen($a['name']) < 1) {
			$aResponse['msg']['name'] = "Please enter your full name";
		}

		if (strlen($a['name']) > 44) {
			$aResponse['msg']['name'] = "Your name should be less than 44 characters";
		}


		if (!is_numeric($a['country_id'])) {
			$aResponse['msg']['country_id'] = "Please select your country";
		}

		if ((strlen($a['email']) < 1)  || (!Validation::IsValidEmail($a['email']) )) {
			$aResponse['msg']['email'] = "Please enter a valid email address";
		}

		if (trim(strtolower($a['email'])) != trim(strtolower($a['email_conf']))) {
			$aResponse['msg']['email'] = "Email & email confirm do not match";
		}

		if (strlen($a['email']) > 49) {
			$aResponse['msg']['email'] = "Email address should be less than 49 chars";
		}

		if (strlen($a['tel']) > 29) {
			$aResponse['msg']['tel'] = "Telephone number should be less than 30 chars";
		}


		if ($a['enquiry_type'] == "null") {
			$aResponse['msg']['enquiry_type'] = "Please specify enquiry type";
		}

		if ($a['enquiry_type'] == "BOOKING") {
				
			if (strlen($a['enquiry']) < 1) {
				$aResponse['msg']['enquiry'] = "Please enter details of your enquiry";
			}
			
			if ((strlen($a['grp_size']) > 4) || (!is_numeric($a['grp_size'])))  {
				$aResponse['msg']['grp_size'] = "Group size should be numeric and less than 4 characters";
			}			

			if (strlen($a['budget']) > 119)  {
				$aResponse['msg']['budget'] = "Budget should be less than 120 chars";
			}			
			
				
			if ($a['contact_type'] == "null") {
				$aResponse['msg']['contact_type'] = "Please specify a prefered contact method";
			}

			if ($a['contact_type'] == "PHONE") {
				if (strlen($a['tel']) < 1) {
					$aResponse['msg']['tel'] = "Please specify a contact telephone number including dialing codes";
				}
			}
				
		}

		if ($a['enquiry_type'] == "GENERAL") {

			if (strlen($a['general_enquiry']) < 1) {
				$aResponse['msg']['general_enquiry'] = "Please enter details of your enquiry";
			}

		}

		if ($a['enquiry_type'] == "BROCHURE") {
			if ($a['brochure_type'] == "PRINT") {

				if (strlen($a['addr1']) < 1) {
					$aResponse['msg']['addr1'] = "Please your address";
				}

				if (strlen($a['addr2']) < 1) {
					$aResponse['msg']['addr2'] = "Please enter your town / country";
				}

				if (strlen($a['addr3']) < 1) {
					$aResponse['msg']['addr3'] = "Please enter your zip / postcode";
				}

			}
				
		}

		if (strlen($a['addr1']) > 120) {
		        $aResponse['msg']['addr1'] = "Address must be less than 120 chars";
		}

		if (strlen($a['addr2']) > 40) {
		        $aResponse['msg']['addr2'] = "Town / country must be less than 40 chars";
		}

		if (strlen($a['addr3']) > 10) {
		        $aResponse['msg']['addr3'] = "Zip / Postcode must be less than 10 chars";
		}



		if ($a['enquiry_type'] == "JOB_APP") {

				
			if (strlen($a['apply_letter']) < 1) {
				$aResponse['msg']['apply_letter'] = "Please provide an application covering letter";
			}
				
		}


		if (count($aResponse['msg']) < 1) {
			return true;
		}


	}


	private function Add($a,&$aResponse) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db,$_CONFIG;



		if ($a['enquiry_type'] == "GENERAL") {
			$a['enquiry'] = $a['general_enquiry'];
		}

		$sDeptDate = "";
		if ($a['enquiry_type'] == "BOOKING") {
			if (($a['DepartureMonth'] != "null") && ($a['DepartureYear'])) {
				$sDeptDate = $a['DepartureMonth']."/".$a['DepartureYear'];
			}
		}

		if ((is_numeric($a['DOBDay'])) && (is_numeric($a['DOBMonth'])) && (is_numeric($a['DOBYear']))) {
			$sDt = $a['DOBDay']."-".$a['DOBMonth']."-".$a['DOBYear'];
			$sDtSQL = ",'".$sDt."'";
		} else {
			$sDtSQL = ",null";
		}

		$ip = IPAddress::GetVisitorIP();

		$sql = "INSERT INTO enquiry (
					id
					,link_to
					,link_id
					,name
					,country
					,email
					,tel
					,enq_type
					,grp_size
					,budget
					,dep_dt
					,enquiry
					,contact_type 
					,brochure_type
					,addr1
					,addr2
					,addr3
					,apply_letter
					,experience
					,dob
					,ip_addr
					,date
					,status
					,site_id
				) VALUES (
					".$this->GetId()."
					,".$this->GetLinkTo()."
					,".$this->GetLinkId()."
					,'".$a['name']."'
					,".$a['country_id']."
					,'".$a['email']."'
					,'".$a['tel']."'
					,'".$a['enquiry_type']."'
					,'".$a['grp_size']."'
					,'".$a['budget']."'
					,'".$sDeptDate."'
					,'".$a['enquiry']."'
					,'".$a['contact_type']."'
					,'".$a['brochure_type']."'
					,'".$a['addr1']."'
					,'".$a['addr2']."'
					,'".$a['addr3']."'
					,'".$a['apply_letter']."'
					,'".$a['experience']."'
		$sDtSQL
		,'".$ip."'
					,now()::timestamp
					,0
					,".$_CONFIG['site_id']."
				)";

		if (!$db->query($sql)) {
			$aResponse['msg']['db_update'] = "Error: There was a problem processing your enquiry.<br />Email admin@oneworld365.org for assistance.";
			return false;
		} else {
			return true;
		}


	}


	public function SetFromArray($a) {
		foreach($a as $k => $v) {
			$this->$k = (is_string($v)) ? stripslashes($v) : $v;
		}
	}


	private function SetFailed($id) {

		global $db;

		if (!is_numeric($id)) return false;

		$sql = "UPDATE enquiry SET status = 4, processed = now()::timestamp WHERE id = ".$id;

		$db->query($sql);

	}


}


?>
