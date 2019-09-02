<?php


/*
 * Email - msg composer for send to a friend
 * 
 */

class Email2Friend {
	
	private $id;
	private $link_to;
	private $link_id;
	private $to_addr;
	private $from_addr;
	private $from_name;
	private $subject;
	private $message;
	private $aMsgParams;
	private $ip;
	private $sent_date;
	private $processed; 	
	
	private $oProfile;
	
	
	public function __Construct() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__);
		
	}

	
	public function GetByStatus($bStatus,$iSiteId) {
		
		global $db;
		
		if (!is_numeric($iSiteId)) return false;

		$db->query("SELECT id FROM email_to_friend WHERE status = '".$bStatus."' AND site_id = ".$iSiteId);
		
		if ($db->getNumRows() >= 1) {
			$aTmp = $db->getRows();
			$aId = array();
			foreach($aTmp as $k => $v) {
				$aId[] = $v['id'];
			}
			return $aId;
		}		
	}	
	
	public function GetById($id) {
		
		global $db;
		
		$sql = "SELECT 
				id
				,to_addr
				,from_addr
				,from_name
				,subject
				,message
				,link_to
				,link_id
				,ip
				,to_char(sent_date,'DD/MM/YYYY HH24:MI') as sent_date
				,status
				,processed
				FROM email_to_friend
				WHERE id = ".$id ."
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
	
	
	public function GetMsgParams() {
		return $this->aMsgParams;
	}

	public function SetMsgParams($aMsgParams) {
		$this->aMsgParams = $aMsgParams;
	}

	public function GetIp() {
		return $ip = IPAddress::GetVisitorIP();	
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
			
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__);
		
		if (!is_object($this->GetProfile())) return false;
		
		if (!$this->Validate($aResponse)) return false;
		
		if (!$this->Save()) return false;
		
		return true;
	}
	
	public function Compose() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__);
		

		$oProfile = $this->GetProfile();
		
		$sTo = $oAccount->email;
		
		$sProfileName = ($oProfile->GetLinkTo() == PROFILE_PLACEMENT) ? " : " .$oProfile->GetTitle() : "";
		
		$sSubject = "Fwd: ".$oProfile->GetCompanyName() . $sProfileName;

		$sFromAddr = $_CONFIG['website_email'];
		$sReturnPath = $_CONFIG['admin_email'];

		$sMsg = "from : \n".stripslashes($this->GetFromName())." (".$this->GetFromAddr().") \n";
		$sMsg .= "subject : \n".stripslashes($this->GetSubject()).".\n";
		$sMsg .= "comments : \n".stripslashes($this->GetMessage()).".\n\n";
		
		if ($oProfile->GetLinkTo() == PROFILE_COMPANY) {
			$sMsg .= $oProfile->GetCompanyName().".\n";
			$sMsg .= $oProfile->GetDescShort().".\n\n";
			$sMsg .= $oProfile->GetProfileUrl().".\n\n";
		} elseif ($oProfile->GetLinkTo() == PROFILE_PLACEMENT) {
			$sMsg .= $oProfile->GetCompanyName().".\n";
			$sMsg .= $oProfile->GetTitle().".\n";
			$sMsg .= $oProfile->GetDescShort().".\n\n";
			$sMsg .= $oProfile->GetProfileUrl().".\n\n";
		}

		$sMsgHtml = "<p>";
		$sMsgHtml .= "<b>from :</b><br /> ".stripslashes($this->GetFromName())." (".$this->GetFromAddr().") <br />";
		$sMsgHtml .= "<b>subject :</b><br /> ".stripslashes($this->GetSubject()).".<br />";
		$sMsgHtml .= "<b>comments :</b><br /> ".stripslashes($this->GetMessage()).".<br /><br /><br />";
		
		$sMsgHtml .= "<table cellpadding='2' cellspacing='4' border='0' width='500px'>";
		if (strlen($oProfile->GetLogoUrl()) > 1) {
			$sMsgHtml .= "<tr><td>&nbsp;</td><td valign='top' colspan='2'><img src='".$oProfile->GetLogoUrl()."' border='0' /></td></tr>";
		}
		
		if ($oProfile->GetLinkTo() == PROFILE_COMPANY) {
			$sMsgHtml .= "<tr><td valign='top' width='100px'><b>name :</b></td><td valign='top'>".$oProfile->GetCompanyName().".</td></tr><br />";
			$sMsgHtml .= "<tr><td valign='top'><b>info :</b></td><td valign='top'> ".$oProfile->GetDescShort().".</td></tr><br /><br />";
			$sMsgHtml .= "<tr><td valign='top'><b>url :</b></td><td valign='top'><a href='".$oProfile->GetProfileUrl()."' title='View ".$oProfile->GetTitle()." Profile'>".$oProfile->GetProfileUrl().".</a></td></tr><br /><br />";
		} elseif ($oProfile->GetLinkTo() == PROFILE_PLACEMENT) {
			$sMsgHtml .= "<tr><td width='100px'><b>name :</b></td><td valign='top'>".$oProfile->GetCompanyName().".</td></tr><br />";
			$sMsgHtml .= "<tr><td>&nbsp;</td><td valign='top'>".$oProfile->GetTitle().".</td></tr><br />";
			$sMsgHtml .= "<tr><td><b>info :</b></td><td valign='top'>".$oProfile->GetDescShort().".</td></tr><br /><br />";
			$sMsgHtml .= "<tr><b>url :</b></td><td valign='top'><a href='".$oProfile->GetProfileUrl()."' title='".$oProfile->GetTitle()."'>".$oProfile->GetProfileUrl()."</a>.</td></tr><br /><br />";
		}		
		$sMsgHtml .= "</table>";
		$sMsgHtml .= "</p>";
		
		//Logger::Msg($sMsgHtml);
		
		$aMsgParams = array("MSG_TXT" => $sMsg,
								  "MSG_HTML" => $sMsgHtml);

		$this->SetMsgParams($aMsgParams);
		
		return true;
	}
	
	public function Validate(&$aResponse) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__);
		
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

		if (strlen($this->GetToAddr()) < 1) {
			$aResponse['msg']['to_addr'] = "Please enter one or more valid recipient email address(s)";
		}
	
		if (trim($this->GetFromAddr()) == trim($this->GetToAddr())) {
			$aResponse['msg']['to_addr'] = "From address and to address must be different";
		}	
		
		if (preg_match("/,/",$this->GetToAddr())) {
		
			$aAddr = explode(",",$this->GetToAddr());
			
			foreach($aAddr as $sEmail) {
				if (!Validation::IsValidEmail($sEmail)) {
					$aResponse['msg']['to_addr'] = "Please enter valid recipient email addresses, seperate each address with a comma";
				}
			}
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
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__);
		
		global $db;
				
		$this->SetId($db->getFirstCell("SELECT nextval('email_seq')"));
				
	}
	
	public function Save() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
				
		global $db,$_CONFIG;
				
		/* check that this isn't a duplicate send request */
		$db->query("SELECT id FROM email_to_friend WHERE id = ".$this->GetId());
		if ($db->getNumRows() == 1) return true;
		
		/* save the message in email queue */
		$sql = "INSERT INTO email_to_friend (	
								id
								,to_addr
								,from_addr
								,from_name
								,subject
								,message
								,link_to
								,link_id
								,ip
								,sent_date
								,status
								,site_id
								) VALUES (
								".$this->GetId()."
								,'".addslashes($this->GetToAddr())."'
								,'".addslashes($this->GetFromAddr())."'
								,'".addslashes($this->GetFromName())."'
								,'".addslashes($this->GetSubject())."'
								,'".addslashes($this->GetMessage())."'
								,".$this->GetLinkTo()."
								,".$this->GetLinkId()."
								,'".$this->GetIp()."'
								,now()::timestamp
								,'F'
								,".$_CONFIG['site_id']."
								);";
		
		
		$db->query($sql);
		
		if ($db->getAffectedRows() == 1) return true;
		
	}
		
	public function SetFromArray($aParams){
		
		/* santitize input params */
		foreach($aParams as $k => $v) {
			$this->$k = htmlentities(trim($v),ENT_NOQUOTES);
		}
		
	}
}

?>
