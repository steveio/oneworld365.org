<?php


/*
 * Email - msg composer for send to a friend
 * 
 */

class Email {
	
	private $id;
	private $link_to;
	private $link_id;
	private $to_addr;
	private $from_addr;
	private $from_name;
	private $subject;
	private $message;
	private $ip;
	private $sent_date;
	private $processed; 	
	
	private $oProfile;
	
	
	public function __Construct() {
		
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

	public function SetLinkId($id) {
		$this->link_id = $id;
	}
		
	public function GetLinkTo() {
		return $this->link_to;
	}

	public function SetLinkTo($sLinkTo) {
		$this->link_to = $sLinkTo;
	}	
	
	public function GetToAddr() {
		return $this->to_addr;
	}

	public function SetToAddr($sAddr) {
		$this->to_addr = $sAddr;
	}

	public function GetFromAddr() {
		return $this->from_addr;
	}

	public function SetFromAddr($sAddr) {
		$this->from_addr = $sAddr;
	}

	public function GetFromName() {
		return $this->from_name;
	}

	public function SetFromName($sName) {
		$this->from_name = $sName;
	}

	public function GetSubject() {
		return $this->subject;
	}

	public function SetSubject($sSubject) {
		$this->subject = $sSubject;
	}

	public function GetMessage() {
		return $this->message;
	}

	public function SetMessage($sMsg) {
		$this->message = $sMsg;
	}

	public function GetIp() {
		return $this->ip;
	}

	public function GetSentDate() {
		return $this->sent_date;
	}

	public function GetProcessed() {
		return $this->processed;	
	}

	public function SetProcessed($bProcessed) {
		$this->processed = $bProcessed;	
	}
	
	
	public function SetProfile($oProfile) {
		$this->oProfile = $oProfile;
	}

	public function GetProfile() {
		return $this->oProfile;
	}
	
	public function Process(&$aResponse) {
			
		if (!is_object($this->GetProfile())) return false;
		
		if (!$this->Validate($aResponse)) return false;
		
		if (!$this->Compose()) return false;
		
		if (!$this->Save()) return false;
		
		return true;
	}
	
	public function Validate(&$aResponse) {

		if (strlen($this->GetFromName()) < 1) {
			$aResponse['msg']['from_name'] = "Please enter your name";
		}
		
		if (strlen($this->GetFromName()) >= 59) {
			$aResponse['msg']['from_name'] = "Your name must be less than 60 chars";
		}

		if ((strlen($this->GetFromAddr()) < 1)  || (!Validation::IsValidEmail($this->GetFromAddr()) )) {
			$aResponse['msg']['from_addr'] = "Please enter a valid email address";
		}
		
		if (strlen($this->GetFromAddr()) >= 79) {
			$aResponse['msg']['from_addr'] = "Your email address must be less than 80 chars";
		}

		
		if ((strlen($this->GetToAddr()) < 1)  || (!Validation::IsValidEmail($this->GetToAddr()) )) {
			$aResponse['msg']['to_addr'] = "Please enter a valid recipient email address";
		}
		
		if (strlen($this->GetToAddr()) >= 79) {
			$aResponse['msg']['to_addr'] = "Recipients email address must be less than < 80 chars";
		}		

		if ((strlen($this->GetSubject()) >= 255) || (strlen($this->GetSubject()) <  1)) {
			$aResponse['msg']['subject'] = "Please enter a subject for the email (< 255 chars)";
		}		
		
		
		if (count($aResponse['msg']) < 1) {
			return true;
		}		
	}
	
	public function GetNextId() {
		
		global $db;
		
		return $this->SetId($db->getFirstCell("SELECT nextval('email_seq')"));
	}
	
	public function Save() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;
		
		
		/* check that this isn't a duplicate send request */
		$db->query("SELECT id FROM email WHERE id = ".$a['id']);
		if ($db->getNumRows() == 1) return false;
		
		/* save the message in email queue */
		$sql = "INSERT INTO email (	
								id
								,to_addr
								,from_addr
								,from_name
								,subject
								,message
								,ip
								,sent_date
								,processed
								) VALUES (
								".$this->GetId()."'
								,'".$this->GetFromAddr()."'
								,'".$this->GetFromName()."'
								,'".$this->GetSubject()."'
								,'".$this->GetMessage()."'
								,'".$this->GetIp()."'
								,now()::timestamp
								,'F'
								);";

		$db->query($sql);
		
		if ($db->getAffectedRows() == 1) return true;
		
	}
		
	public function SetFromArray($aParams){
		
		/* santitize input params */
		foreach($aParams as $k => $v) {
			$this->$k = addslashes(htmlentities(trim($v),ENT_NOQUOTES));
		}
		
	}
}

?>