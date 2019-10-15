<?php

define("SALES_ENQUIRY_STATUS_PENDING",0);
define("SALES_ENQUIRY_STATUS_VALID",1);
define("SALES_ENQUIRY_STATUS_SAVED",2);
define("SALES_ENQUIRY_STATUS_NOTIFIED",3);
define("SALES_ENQUIRY_STATUS_COMPLETE",4);
define("SALES_ENQUIRY_STATUS_VALIDATION_ERROR",9);
define("SALES_ENQUIRY_STATUS_ERROR",999);
define("SALES_ENQUIRY_GENERAL_ERROR", "Sorry, an error occured and we were unable to process your request, please try again later.");


class ValidationError extends Exception {}

class DuplicateInsertError extends Exception {}


class SalesEnquiry { 
	
	private $id;
	private $website_id;
	private $to_email;
	private $ip_address;
	private $sent_date;
	
	private $enq_name;
	private $enq_role;
	private $enq_comp_name;
	private $enq_email;
	private $enq_tel;
	private $enq_details;
	
	private $status;
	private $aValidationError = array();
	private $error_message;

	private $oSecurityQuestion;
	
	public function __Construct() {
	
		$this->SetStatus(SALES_ENQUIRY_STATUS_PENDING);
		
	}	
	
	public function GetId() {
		return $this->id;
	}
	
	public function SetId($id) {
		if (is_numeric($id)) {
			$this->id = $id;
		}
	}
	
	public function GetStatus() {
		return $status;
	}
	
	public function SetStatus($status) {
		$this->status = $status;
	}
	
	public function GetValidationError() {
		return $this->aValidationError;
	}
	
	public function GetValidationErrorById($field) {
		return $this->aValidationError[$field];
	}	
	
	public function SetValidationError($field,$message) {
		$this->aValidationError[$field] = $message;
	}	
	
	private function SetErrorMessage($message) {
		$this->error_message = $message;
	}
	
	public function GetErrorMessage() {
		return $this->error_message;
	}
	
	public function GetWebsiteId() {
		return $this->website_id;
	}
	
	public function SetWebsiteId($id) {
		$this->website_id = $id;
	}
	
	public function GetToEmail() {
		return $this->to_email;
	}
	
	public function SetToEmail($email) {
		$this->to_email = $email;
	}
	
	public function GetIpAddress() {
		return $this->ip_address;
	}
	
	public function SetIpAddress($ip_address) {
		$this->ip_address = $ip_address;
	}
	
	public function GetSentDate() {
		return $this->sent_date;
	}
	
	public function SetSentDate($date) {
		$this->sent_date = $date;
	}
	
	/*  ENQUIRY DETAILS */
	
	public function GetName() {
		return $this->enq_name;
	}
	
	public function SetName($name) {
		$this->enq_name = $name;
	}

	public function GetRole() {
		return $this->enq_role;
	}
	
	public function SetRole($role) {
		$this->enq_role = $role;
	}
	
	
	public function GetCompanyName() {
		return $this->enq_comp_name;
	}
	
	public function SetCompanyName($name) {
		$this->enq_comp_name = $name;
	}

	public function GetEmail() {
		return $this->enq_email;
	}
	
	public function SetEmail($email) {
		$this->enq_email = $email;
	}

	public function GetTel() {
		return $this->enq_tel;
	}
	
	public function SetTel($tel) {
		$this->enq_tel = $tel;
	}
	
	public function GetEnquiry() {
		return $this->enq_details;
	}
	
	public function SetEnquiry($enquiry) {
		$this->enq_details = $enquiry;
	}
	
	public function SetSecurityQuestion($oSecurituyQuestion) {
		$this->oSecurityQuestion = $oSecurituyQuestion;
	}
	
	public function GetSecurityQuestion() {
		return $this->oSecurityQuestion;
	}
	
	
	public function SetFromArray($aFormPost) {
		
		if (!is_array($aFormPost)) return FALSE;

		Validation::Sanitize($aFormPost);
		
		$this->SetId(base64_decode($aFormPost['id']));
		$this->SetName(trim($aFormPost['enq_name']));
		$this->SetRole(trim($aFormPost['enq_role']));
		$this->SetCompanyName(trim($aFormPost['enq_comp_name']));
		$this->SetEmail(trim($aFormPost['enq_email']));
		$this->SetTel(trim($aFormPost['enq_tel']));
		$this->SetEnquiry(trim($aFormPost['enq_details']));
				
	}

	
	public function GetNextId() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		$this->SetId($db->getFirstCell("SELECT nextval('sales_enquiry_seq')"));

		return $this->GetId();

	}
	
	
	public function Process() {
	
		try {

			$this->DuplicateCheck();
			$this->Validate();
			$this->Save();
			$this->Notify();

		} catch (DuplicateInsertError $e) {
		
			$this->SetStatus(SALES_ENQUIRY_STATUS_COMPLETE);
			return TRUE;
			
		} catch (ValidationError $e) {
		
			$this->SetStatus(SALES_ENQUIRY_STATUS_VALIDATION_ERROR);
			return FALSE;
			
		} catch (Exception $e) {		
			$this->SetErrorMessage(SALES_ENQUIRY_GENERAL_ERROR);
			$this->SetStatus(SALES_ENQUIRY_STATUS_ERROR);
			return FALSE;
			
		}
		
		return TRUE;
	}
	
	public function DuplicateCheck() {
		
		global $db;
		
		$db->query("SELECT id FROM sales_enquiry WHERE id = ".$this->GetId());
		if ($db->getNumRows() == 1) throw new DuplicateInsertError;
		
		
	}
	
	public function Validate() {
				
		if (!is_numeric($this->GetWebsiteId())) {
			$this->SetValidationError('website_id','Invalid website id');
		}

		if (strlen($this->GetName()) < 1 || strlen($this->GetName()) > 32)  {
			$this->SetValidationError('enq_name','Please enter a valid name upto 32 characters');
		}
		
		if (strlen($this->GetRole()) < 1 || strlen($this->GetRole()) > 32)  {
			$this->SetValidationError('enq_role','Please enter your role / position eg Marketing Director');
		}		
		
		if (strlen($this->GetCompanyName()) < 1 || strlen($this->GetCompanyName()) > 32)  {
			$this->SetValidationError('enq_comp_name','Please enter the name of your company / organisation');
		}				
		
		if (!self::ValidEmail($this->GetEmail()) || strlen($this->GetEmail()) > 56) {
			$this->SetValidationError('enq_email','Please enter a valid email address');
		}

		if (strlen($this->GetTel()) < 1 || strlen($this->GetTel()) > 32)  {
			$this->SetValidationError('enq_tel','Please enter a valid contact telephone number including country/region code');
		}		

		if (strlen($this->GetEnquiry()) < 1 || strlen($this->GetEnquiry()) > 2000)  {
			$this->SetValidationError('enq_details','Please enter your enquiry');
		}		
		
		if (!$this->GetSecurityQuestion()->Valid()) {
			$this->SetValidationError('security_q','Please answer the security question correctly');
		}
		
		if (count($this->GetValidationError()) >= 1) throw new ValidationError;

		$this->SetStatus(SALES_ENQUIRY_STATUS_VALID);
		
		return TRUE;
		
	}	
	
	public function Save() {
	
		global $db;
		
		if (!is_object($db)) throw new Exception('Database unavailable');
		
		$sql = "INSERT INTO sales_enquiry 
					(id,
					website_id,
					enq_name,
					enq_role,
					enq_comp_name,
					enq_email,
					enq_tel,
					enq_details,
					ip_address,
					sent_date) 
				VALUES 
					(
					".$this->GetId().",
					".$this->GetWebsiteId().",
					'".pg_escape_string(addslashes($this->GetName()))."',
					'".pg_escape_string(addslashes($this->GetRole()))."',
					'".pg_escape_string(addslashes($this->GetCompanyName()))."',
					'".pg_escape_string(addslashes($this->GetEmail()))."',
					'".addslashes($this->GetTel())."',
					'".pg_escape_string(addslashes($this->GetEnquiry()))."',
					'".$this->GetIpAddress()."',
					now()::timestamp
					);
				";
		
		$db->query($sql);
		
		if ($db->getAffectedRows() == 1) {			
			return TRUE;
		} else {
			throw new Exception('Save sales enquiry FAILED: '.$sql);
		}
	
	}
		
	/* send sales enquiry as an email notification */
	public function Notify() {
		
		global $_CONFIG;

		$sHtmlTemplatePath = $_CONFIG['root_path'].$_CONFIG['template_home']."/generic_html.php";
		$sTextTemplatePath = $_CONFIG['root_path'].$_CONFIG['template_home']."/generic_txt.php";
		
		$subject = "New Enquiry from ".$_CONFIG['site_title'];
		$from_email = $_CONFIG['website_email'];
		$to_email = $_CONFIG['admin_email'];
		$return_path = $_CONFIG['website_email'];
		
		
		$sMsg = "Name: ".$this->GetName()."\n";
		$sMsg .= "Role: ".$this->GetRole()."\n";
		$sMsg .= "Company: ".$this->GetCompanyName()."\n";
		$sMsg .= "Email: ".$this->GetEmail()."\n";
		$sMsg .= "Tel: ".$this->GetTel()."\n";
		$sMsg .= "Enquiry: ".$this->GetEnquiry()."\n"; 
		
		$aParams = array("MSG_TXT" => $sMsg,
						  "MSG_HTML" => nl2br($sMsg));
		
		
		EmailSender::SendMail($sHtmlTemplatePath, $sTextTemplatePath, $aParams, $to_email, $subject, $from_email, $return_path,null);
		
	}
	
	
	private static function ValidEmail( $sEmail ){
		
		if(preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", $sEmail)) {
			return true;
		}
		
	}	
}

?>
