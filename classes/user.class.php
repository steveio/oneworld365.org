<?




class User {

	var $id;
	var $name;
	var $email;
	var $uname;
	var $access_level;
	var $pass;
	var $sess_id;
	var $added;
	var $last_login;
	var $logins;
	var $company_id;
	var $isValidUser;
	var $isAdmin;

	public function __construct($db)
	{
	    $this->User($db);
	}

	function User($db) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$this->db = $db;

		$this->isValidUser = false; // is the user authenticated?
		$this->isAdmin = false; // is this a Super User?
	}
	
	function GetCompanyId() {
		return $this->company_id;	
	}

	function getUserBySessionId($sessId) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		$this->db->query ("SELECT 
								u.*, 
								c.id as company_id,
								c.title as company, 
								c.url_name as comp_url_name,
								c.prod_type,
								c.enq_opt,
								c.prof_opt
							FROM 
								euser u, 
								company c 
							WHERE 
								u.company_id = c.id 
							AND u.sess_id = '".$sessId."';");

		if ($this->db->getNumRows() == 1) {

			$this->isValidUser = true;

			$oResult = $this->db->getObject();

			foreach($oResult as $k => $v) {
				$this->$k = $v;
			}

			if ($this->access_level == 3) {
				$this->isAdmin = true;
			}
		}
	}



	function getUserPermissions() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		/*
			*
			* Not currently implemented - may use later...
			*
			$query = "SELECT A.action_code FROM access_action A, access_user_action U WHERE A.oid = U.action_oid AND U.user_oid = ".$this->user_oid.";";			
			$fsdb->query($query);
			while($row = $fsdb->getRow()) $this->permissions[] = $row['action_code'];
			*/
	}


	function addUser() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		// check for required feilds

		foreach($_POST as $k => $v) {
			if (ereg("^p_",$k)) {
				$k = ereg_replace("p_","",$k);
				$this->$k = $v;
			}
		}


		if (($this->name == "")
			|| ($this->uname == "")
			|| ($this->pass == "")
			|| ($this->email == "")
			|| (!is_numeric($this->company))
		) {
			$this->msg = "Error : One or more fields missing.";
			return;
		}

		// check uniqueness of username
		$this->db->query("SELECT id FROM euser WHERE uname = '".$this->uname."'");
		if ($this->db->getNumRows() >= 1) {
			$this->msg = "Error : Username is in use already, please choose a unique username.";
			return;
		}

		// perform the update
		$sql = "INSERT INTO euser (
		   id
		   ,name
		   ,email
		   ,uname
		   ,access_level
		   ,pass
		   ,pass_salt
		   ,company_id
		   ,added
		) VALUES (
		   nextval('euser_seq')
		   ,'".$this->name."'
		   ,'".$this->email."'
		   ,'".$this->uname."'
		   ,1
		   ,'".$this->pass."'
		   ,''
		   ,'".$this->company."'
		   ,now()::timestamp
		);";

		$this->db->query($sql);
		if ($this->db->getAffectedRows() == 1) {
			$this->msg = "Added new user account for : ".$this->name.".";
			return true;
		} else {
			$this->msg = "There was a problem adding the user.";
			return;
		}
	}

	function getUserEditList($company_id) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		if (!is_numeric($company_id)) return false;

		global $oAuth;

		$this->db->query("SELECT * FROM euser WHERE company_id = $company_id ORDER by uname asc");

		$s = "<table cellpadding='2' cellspacing='4' border='0'>";
		$s .= "<tr><td colspan='3'>User Count : ".$this->db->getNumRows()."</td></tr>"; 
		$s .= "<tr><td>&nbsp;</td><td>Name</td><td>User</td><td>Pass</td><td>Email</td>";
		if ($oAuth->oUser->isAdmin) {
			$s .= "<td>Admin?</td>";
		}
		$s .= "<!--<td>Edit</td>--><td>Delete</td></tr>";
		$i = 1;
		if ($this->db->getNumRows() >= 1) {
			$arr = $this->db->getObjects();

			foreach($arr as $oUser) {
				$s .= "<tr>";
				$s .= "<td>".$i."</td>";
				$s .= "<td>".$oUser->name."</td>";
				$s .= "<td>".$oUser->uname."</td>";
				$s .= "<td>".$oUser->pass."</td>";
				$s .= "<td>".$oUser->email."</td>";
				if ($oAuth->oUser->isAdmin) {
					$super = ($oUser->access_level == 3) ? "Admin" : "no";
					$s .= "<td>".$super."</td>";
				}
				$s .= "<td><a onclick=\"javascript: return confirm('Are you sure you want to delete this user?');\" href=\"".$_CONFIG['url']."/user.php?m=del&id=".$oUser->id."&p_company=".$company_id."\">delete</a></td>";
				$s .= "</tr>";
				$i++;
			}
		} else {
			$s .= "<tr><td colspan='4'>There are 0 users posted for this company.</td></tr>";
		}
		$s .= "</table>";
		return $s;
	}

	
	function deleteUser($id,$company_id) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$this->db->query("SELECT id FROM euser WHERE id = $id AND company_id = $company_id");
		if ($this->db->getNumRows() == 1) {
			return $this->db->query("DELETE FROM euser WHERE id = $id");
		}
	}


}


class AccountApplication {

	private $id; 
	
	public function Validate($a,&$aResponse) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		if (!is_array($aResponse['msg'])) $aResponse['msg'] = array();
		
		if (strlen(trim($a['name'])) < 1) {
			$aResponse['msg']['name'] = "Please enter your name.";
		}

		if (strlen(trim($a['tel'])) < 1) {
			$aResponse['msg']['tel'] = "Please provide a contact number including all dialing codes.";
		}
		
		if (strlen(trim($a['role'])) < 1) {
			$aResponse['msg']['role'] = "Please tell us your position / role or relationship to the organisation.";
		}
		
		if (strlen(trim($a['comments'])) > 1990) {
			$aResponse['msg']['comments'] = "Comments should be no more than 2000chars.";
		}
		
		
		if ( (strlen($a['email']) < 1 ) || (!Validation::IsValidEmail($a['email']) ) ) {
			$aResponse['msg']['email'] = "Please enter a valid email address.";
		}

		/* check that the email address is unique */
		global $db,$_CONFIG;
		$db->query("SELECT id FROM act_app WHERE email = '".$a['email']."'");
		if ($db->getNumRows() >= 1) {
			$aResponse['msg']['email'] = "An account already exists with this email.  Please email ".$_CONFIG['admin_email']." for assistance.";
		}
		/* check the euser table too... */
		$db->query("SELECT id FROM euser WHERE uname = '".$a['email']."'");
		if ($db->getNumRows() >= 1) {
			$aResponse['msg']['email'] = "An account already exists with this email.  Please email ".$_CONFIG['admin_email']." for assistance.";
		}
		
		if (!is_numeric($a['country_id'])) {
			$aResponse['msg']['country_applicant'] = "Please specify which country you are in.";
		}

		if (($a['listing_type']) == "null") {
			$aResponse['msg']['listing_type'] = "Please choose a listing type.";
		}

		if ((strlen(trim($a['password'])) < 4) || (strlen(trim($a['password'])) > 20)) {
			$aResponse['msg']['password'] = "Password should be between 4 and 20 characters.";
		}
		
		if (!ereg("^([a-zA-Z0-9]*)$",$a['password'])) {
			$aResponse['msg']['password'] = "Password should contain only letters and numbers.";
		}

		if (trim($a['password']) !=  trim($a['password_confirm'])) {
			$aResponse['msg']['password_confirm'] = "Password and password confirm must match.";
		}
		
		
		
		if (is_array($aResponse['msg']) && (count($aResponse['msg']))) {
			return false;
		}
		
		return true;
		
	}	
	

	public function Add($a,&$aResponse) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db,$_CONFIG;
		
		if (!is_array($a)) {
			$aResponse['msg']['general'] = "Error (missing_params): Sorry we were unable to process your request at this time";
			return false;
		}
		
		if (!is_numeric($a['company_id'])) {
			$aResponse['msg']['general'] = "Sorry we were unable to process your request at this time";
			return false;
		}
		
		
		$a['apply_date'] = "now()::timestamp";
		$a['approved'] = 'N';
		$a['ip_address'] = IPAddress::GetVisitorIP();
		
		$this->id = $db->getFirstCell("SELECT nextval('act_app_seq')");
		
		foreach($a as $v) $v = (is_string($v)) ? htmlentities(trim($v),ENT_NOQUOTES) : $v;

		$sql = "INSERT INTO act_app (
									id
									,sid
									,company_id
									,name
									,role
									,email
									,password
									,tel
									,country
									,account_type
									,account_code
									,comments
									,approved
									,apply_date
									,ip_address
								) VALUES (
									".$this->GetId()."
									,".$_CONFIG['site_id']."
									,".$a['company_id']."
									,'".addslashes($a['name'])."'
									,'".addslashes($a['role'])."'
									,'".$a['email']."'
									,'".$a['password']."'
									,'".$a['tel']."'
									,".$a['country_id']."
									,'".$a['account_type']."'
									,'".$a['listing_type']."'
									,'".addslashes($a['comments'])."'
									,'".$a['approved']."'
									,".$a['apply_date']."
									,'".$a['ip_address']."');";
		
		
		if (!$db->query($sql)) {
			$aResponse['msg']['general'] = "Error: There was a problem processing your application, we will look into it.";
			return false;
		} else {
	 		return true;
		}
	
	}
	
	public function SetId($id) {
		$this->id = $id;
	}
	
	public function GetId() {
		return $this->id;
	}

	function GetPendingList() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
		global $db,$_CONFIG;
		
		$sql = "SELECT 
					 a.*
					 ,w.name as website_name
					,c.title as company_name
					,c.url_name as comp_url_name
					,c.status as comp_status
					,cty.name as country_name 
				FROM 
					 act_app a
					,website w
					,company c
					,country cty 
				WHERE 
					a.sid = w.id
				AND a.company_id = c.id 
				AND a.country = cty.id 
				AND a.approved = 'N' 
				AND a.rejected != 'Y' 
				ORDER BY 
					a.apply_date DESC";
		
		$db->query($sql);
		
		$aAccount = $db->getObjects();
		
		for($i=0;$i<count($aAccount);$i++) {
			$aAccount[$i]->name = stripslashes($aAccount[$i]->name);
			$aAccount[$i]->role = stripslashes($aAccount[$i]->role);			
			$aAccount[$i]->comments = stripslashes($aAccount[$i]->comments);
			
			$aAccount[$i]->company_type = ($aAccount[$i]->comp_status == 0) ? "NEW" : "EXISTING";
			$aAccount[$i]->company_profile_link = $_CONFIG['url'] . "/company/" .$aAccount[$i]->comp_url_name;
			
			if ($aAccount[$i]->account_type == 0) $aAccount[$i]->account_name = "Free";
			if ($aAccount[$i]->account_type == 1) $aAccount[$i]->account_name = "Enhanced";
			if ($aAccount[$i]->account_type == 2) $aAccount[$i]->account_name = "Sponsored";
						
		}

		return $aAccount;
		
	}

	
	function GetRecentList() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
		global $db,$_CONFIG;
		
		$sql = "SELECT 
					 a.*
					,to_char(a.apply_date,'DD-MM-YYYY') as receieved
					,c.title as company_name
					,c.url_name as comp_url_name
					,c.status as comp_status
					,cty.name as country_name
					,w.name as website_name 
				FROM 
					 act_app a
					,website w
					,company c
					,country cty 
				WHERE 
					a.sid = w.id
				AND a.company_id = c.id 
				AND a.country = cty.id  
				ORDER BY 
					a.apply_date DESC
				LIMIT 20
					";
		
		$db->query($sql);
		
		$aAccount = $db->getObjects();		
		
		for($i=0;$i<count($aAccount);$i++) {
			$aAccount[$i]->name = stripslashes($aAccount[$i]->name);
			$aAccount[$i]->role = stripslashes($aAccount[$i]->role);			
			$aAccount[$i]->comments = stripslashes($aAccount[$i]->comments);
			
			$aAccount[$i]->company_type = ($aAccount[$i]->comp_status == 0) ? "NEW" : "EXISTING";
			$aAccount[$i]->company_profile_link = $_CONFIG['url'] . "/company/" .$aAccount[$i]->comp_url_name;
			
			if ($aAccount[$i]->account_type == 0) $aAccount[$i]->account_name = "Free";
			if ($aAccount[$i]->account_type == 1) $aAccount[$i]->account_name = "Enhanced";
			if ($aAccount[$i]->account_type == 2) $aAccount[$i]->account_name = "Sponsored";
			
			if ($aAccount[$i]->rejected == "t") {
				$aAccount[$i]->status = "Rejected";
			} elseif ($aAccount[$i]->approved  == "t") {
				$aAccount[$i]->status = "Approved";
			} else {
				$aAccount[$i]->status = "Pending";
			}
			
		}

		return $aAccount;
		
	}
	
	
	function GetById($id) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
		global $db,$_CONFIG;
		
		$sql = "SELECT 
					 a.*
					,c.title as company_name
					,c.url_name as comp_url_name
					,c.status as comp_status
					,cty.name as country_name 
				FROM 
					 act_app a
					,company c
					,country cty 
				WHERE 
					a.company_id = c.id
					AND a.country = cty.id 
					AND a.id = ".$id." 
				ORDER BY a.apply_date DESC";
		
		$db->query($sql);
		
		if ($db->getNumRows() != 1) return false;
		
		$oAccount = $db->getObject();
		
		$oAccount->name = stripslashes($oAccount->name);
		$oAccount->role = stripslashes($oAccount->role);			
		$oAccount->comments = stripslashes($oAccount->comments);
		$oAccount->status = ($oAccount->comp_status == 0) ? "PENDING" : "APPROVED";
		$oAccount->company_profile_link = $_CONFIG['url'] . "/company/" .$oAccount->comp_url_name;
		
		
		if ($oAccount->account_type == 0) $oAccount->account_name = "Free";
		if ($oAccount->account_type == 1) $oAccount->account_name = "Enhanced";
		if ($oAccount->account_type == 2) $oAccount->account_name = "Sponsored";

		return $oAccount;

	}
	
	
	function Approve($oAccount,$username,$password,&$response) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
		global $db,$_CONFIG;
		
		/* validate username / password */ 
		if (strlen($username) < 1) {
			$response['msg'] = "Error: Please enter a username";
			return false;
		}
			
		if (strlen($password) < 1) {
			$response['msg'] = "Error: Please enter a password";
			return false;
		}
		
		/* sanitise username / password */
		$username = preg_replace("[^a-zA-Z0-9]","",$username);
		$password = preg_replace("[^a-zA-Z0-9]","",$password);
		
		/* setup a user record for the new account */

		if (!is_numeric($oAccount->company_id)) {
			$response['msg'] = "Account::Approve() ERROR: invalid company_id";
			return false;
		}
		
		/*
		 * Set Comp Profile Listing Type & Insert a Row in Listing Table 
		 *
		 */
		if (($oAccount->account_type == BASIC_LISTING) || ($oAccount->account_type == ENHANCED_LISTING)) {

			/*
			 * Set listing type (prod_type) field and default profile quota on company record   
			 */
			switch($oAccount->account_type) {
				case BASIC_LISTING :
					$iProfileQuota = BASIC_PQUOTA;
					break;
				case ENHANCED_LISTING :
					$iProfileQuota = ENHANCED_PQUOTA;
					break;
				default :
					$iProfileQuota = FREE_PQUOTA;
					break;
			}
			
			$db->query("UPDATE COMPANY set status = 1, prod_type = ".$oAccount->account_type.", job_credits = ".$iProfileQuota." WHERE id = ".$oAccount->company_id);
			if ($db->getAffectedRows() != 1) {
				$response['msg'] = "Account::Approve() ERROR: set company status approved db write error";
				return false;
			}

			$oListing = new Listing();
			$oListing->SetActiveByCompanyId($oAccount->company_id,"F");
			$oListing->SetFromArray(ListingOption::GetByCode($oAccount->account_code));
			$oListing->SetCompanyId($oAccount->company_id);

			if (!$oListing->Add()) {
				return false;
			}	
		}
		
		/* map feilds so they can be processed by $oUser->addUser(); */
		$_POST['p_name'] = addslashes($oAccount->name);
		$_POST['p_email'] = $oAccount->email;
		$_POST['p_uname'] = $username;
		$_POST['p_pass'] = $password;		
		$_POST['p_company'] = $oAccount->company_id;
		$_POST['p_access_level'] = 0;
		
		$oUser = new User($db);
		if ($oUser->addUser()) {			


			$sMsg = "A new account on One World 365 for ".$oAccount->company_name." has been setup and you can view the profile here: ".$_CONFIG['url']."/company/".$oAccount->comp_url_name.".\n\n";
			$sMsg .= "You can login to edit and update the company profile here :\n";
			$sMsg .= "URL : http://admin.oneworld365.org/login \n";
			$sMsg .= "Username :".$oAccount->email."\n";
			$sMsg .= "Password :".$oAccount->password."\n\n";
			$sMsg .= "You might like to add a text link or add our logo to your website and say you are listed with us.\n";
			$sMsg .= "We also offer advertising upgrade packages which includes the ability to add specific trips/programs, improved search results, logo on company profile and search pages, images, website/application links, features, articles, banners and social media promotion.\n";
			$sMsg .= "If you would be interested in learning more about our advertising upgrade packages please contact us info@oneworld365.org \n\n";

			$sMsg .= "Thanks,\n";
			$sMsg .= "One World 365 \n";
			
			$sTo = $oAccount->email;
			$sSubject = $_CONFIG['site_title']. " : Login Details";
			$sFromAddr = $_CONFIG['website_email'];
			$sReturnPath = $_CONFIG['admin_email'];
			
			$aMsgParams = array("MSG_TXT" => $sMsg,
									  "MSG_HTML" => nl2br($sMsg));
			
			EmailSender::SendMail($_CONFIG['root_path'].$_CONFIG['template_home']."/generic_html.php"
										,$_CONFIG['root_path'].$_CONFIG['template_home']."/generic_txt.php"
										,$aMsgParams
										,$sTo
										,$sSubject
										,$sFromAddr
										,$sReturnPath);
										

			/* mark the account pending row as approved */
			$db->query("UPDATE act_app SET approved = 'Y' WHERE id = ".$oAccount->id);
			
			/* if we are processing a paid listing, set company listing_type */
						
			return true;
		} else {
			Logger::DB(1,"Account::Approve()","ERROR: Add User : ".serialize($oUser));
			$response['msg'] = $oUser->msg;
			return false;
		}
		
	}	

	/*
	 *  Reject an application for an account
	 * 
	 * 
	 */
	public function Reject($oAccount,&$response) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
		global $db;
		
		if ( (!is_object($oAccount)) || (!is_numeric($oAccount->company_id)) ||  (!is_numeric($oAccount->id)) ) {
			$response['msg'] = "ERROR : Missing parameter to reject company"; 
			return false;
		}
					
		/* if application includes new company details - delete company row */
		$oCompany = new Company($db);

		/* only delete company if it has been added during a rejected new application */
		if ($oAccount->comp_status == 0) {
			if (!$oCompany->Delete($oAccount->company_id)) {
				$response['msg'] = "ERROR : a problem occured deleting the company.";
				return false; 
			}
		}
		
		/* set the account application row status to rejected */
		$db->query("UPDATE act_app SET rejected = 'T' WHERE id = ".$oAccount->id);
		
		if ($db->getAffectedRows() != 1) {
			$response['msg'] = "ERROR : a problem occured rejecting user account.";
			return false;
		}
				
		return true;
	}

	public function NotifyAdmin($oCProfile,$aListing,&$response) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
		global $_CONFIG;
		
		/* notify admin by email */
		$to = (DEV) ? TEST_EMAIL : $_CONFIG['admin_email'];
		$subject = $_CONFIG['site_title']. " : New Account Application";
		$headers = "From: ".$_CONFIG['website_email']."\r\nReply-To: ".$_CONFIG['website_email'];
		
		$message .= "Account Type : ".$aListing['listing_type_label']."\n\n";
		
		$sListingType = ($aListing['account_type'] >= 1) ? "paid listing" : "free listing";
		$sVerb = ($aListing['account_type'] >= 1) ? "purchased" : "requested";
		
		$message .= "A new ".$sListingType." was ".$sVerb." by ".$_POST['name']." (".$aListing['email'].") from ".$oCProfile->GetTitle().".\n\r";
		
		if ($oCProfile->GetStatus() == 0) { /* new company */
			$message .= $oCProfile->GetTitle() . " is a new company.\n\r";
			$message .= "View Company : ".$_CONFIG['url']."/company/".$oCProfile->GetUrlName()." \n";
		}
		if ($aListing['account_type'] >= 1) {
			$message .= "A new account for will be setup which should be verified.\n\n";
		} else {
			$message .= "The new profile will not be available on the site until approved.\n\n";
			$message .= "Please login to view and approve/reject the request.";
		}

		if (!mail($to,$subject,$message,$headers)) {
			$response['msg'] = "Error : a problem occured sending mail.";
		}

	}
	
}
?>
