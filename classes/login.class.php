<?

/*
 * Login Class
 *
 * - Handles requests to login
 * - authenticates supplied credentials against an identity repositary
 * - upon successful authentication sets up a valid user session
 * - sets up user permissions
 * - anti-spam captcha functionality
 * - optionally handles secondary authentication/session setup against a phpBB database
 *
 * Use -
 *
 *
 *
 * @created 25/02/2009
 */


class Login {

	private $uname;
	private $pass;
	private $cryptedpass;
	private $user_id;
	private $userIP;
	private $cookiename;
	private $cookie_domain;
	private $cookie_path;
	private $session_expires;
	private $recaptchCheckFl;
	private $encryptPassFl;
	private $passHashSalt;
	private $validCharRegex;
	private $aError;
	private $redirectUrl;

	public function __construct() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;

		/* user details */
		$this->uname = "";
		$this->pass = "";
		$this->cryptedpass = "";
		$this->user_id = "";

		/* cookie params */
		$this->cookiename = $_CONFIG['cookiename'];
		$this->cookie_domain = $_CONFIG['cookie_domain'];
		$this->cookie_path =  $_CONFIG['cookie_path'];
		$this->session_expires = $_CONFIG['session_expires'];

		$this->recaptchCheckFl = $_CONFIG['CAPTCHA_CHECK_FL']; /* use recaptcha anti-spam verification? */
		$this->encryptPassFl = false; /* use encrypted | plain text passwords? */
		$this->passHashSalt = ''; /* salt used to harden password hashing function */
		$this->validCharRegex = $_CONFIG['VALID_CHAR_REGEX']; /* valid chars for username, password */

		$this->redirectUrl = $_CONFIG['url'].$_CONFIG['login_redirect_url']; /* where to redirect to upon sucessful login */

		$this->aError = array();
	}

	
	public function doPasswordReminder($sEmail,&$aError) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
		if ((strlen(trim($sEmail)) < 1) || (!Validation::IsValidEmail($sEmail))) {
			$aError['EMAIL_INVALID'] = "Please enter a valid email address.";
			return false;	
		}
		
		$res = $this->GetAccountByEmail($sEmail);
		if (!$res) {
			Logger::DB(1,get_class($this)."::".__FUNCTION__."()",'PASSWORD : EMAIL_NOT_FOUND : '.$sEmail . ' : '.IPAddress::GetVisitorIP());
			$aError['EMAIL_NOT_FOUND'] = "Please enter a valid email address.";
			return false;	
		}
		
		$this->SendPasswordReminderEmail($res['email'],$res['uname'],$res['pass']);
		
		Logger::DB(1,get_class($this)."::".__FUNCTION__."()",'PASSWORD : REMINDER_SENT : '.$sEmail . ' : '.IPAddress::GetVisitorIP());
				
		return true;
				
	}
	
	private function SendPasswordReminderEmail($sEmail,$sUname,$sPass) {
		
			global $_CONFIG;
		
			$sMsg = "Login Details for ".$_CONFIG['site_title'].".\n\n";
			
			$sMsg .= "Url : ". $_CONFIG['url']."/client_login.php \n";
			$sMsg .= "Username : ". $sUname." \n";
			$sMsg .= "Password : ". $sPass." \n\n";
			
			$sMsg .= "For further assistance please email ".$_CONFIG['admin_email'].".\n\n";
			$sMsg .= "With Thanks,\n\n";
			$sMsg .= $_CONFIG['site_title']."\n\n";
			
			$sTo = $sEmail;
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
		
	}
	
	private function GetAccountByEmail($sEmail) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$db->query("SELECT uname, pass, email FROM euser WHERE email = '".$sEmail."'");
		
		if ($db->getNumRows() != 1) return false;
		
		return $aRow = $db->getRow(PGSQL_ASSOC,0);

	}


	/*
	 * Main login processing entry point
	 *
	 * @param string username
	 * @param string password
	 * @param string captcha challenge hash
	 * @param string captcha reponse hash
	 *
	 */
	public function doLogin($uname,$pass,$recaptcha_challenge = '',$recaptcha_response = '') {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		$this->uname = $uname;
		$this->pass = $pass;
		$this->recaptcha_challenge = isset($recaptcha_challenge) ? $recaptcha_challenge : "";
		$this->recaptcha_response = isset($recaptcha_response) ? $recaptcha_response : "";

		/* true represents error condition */
		switch (true) {

			case $this->isLoginComplete():
			case $this->validateStr($this->uname, $msg = "Please enter a valid username."):
			case $this->validateStr($this->pass, $msg = "Please enter a valid password."):
			case $this->checkAccountStatus():
			case $this->ipAddressCheck():
			case $this->encryptPass($this->pass):
			case $this->recaptchaCheck($this->recaptcha_challenge, $this->recaptcha_response):
			case $this->createSessionID():
			case $this->authenticate():
			case $this->setSessionCookieHeader():
				break;

				/* login OK : redirect to secure homepage */
			default:
				$this->doRedirect($this->redirectUrl);
		}

		/* login FAILED : return details of the error */
		return $this->aError;

	}


	/* check uname & password are not empty */
	private function isLoginComplete() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		if (!isset($this->uname) || $this->uname == "") {
			$this->aError['CREDENTIAL_UNAME_MISSING'] = "Please enter a valid username.";
		}

		if (!isset($this->pass) || $this->pass == "") {
			$this->aError['CREDENTIAL_PASSWD_MISSING'] = "Please enter a valid password.";
		}

		if (count($this->aError) >= 1) return true;
	}


	/* syntax check (uname & pass) */
	private function validateStr($str,$errorMsg) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		if (ereg($this->validCharRegex, $str)) {
			return false;
		} else {
			$this->aError['CREDENTIAL_CHAR_SYNTAX'] = $errorMsg;
			return true;
		}
	}

	/*
	 *  Check that supplied username exists, that account is not locked and retrieve password hash salt
	 *
	 */
	private function checkAccountStatus() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG, $db;

		$db->query("SELECT id,locked,pass_salt,ip FROM euser WHERE uname = '".$this->uname."'");

		if ($db->getNumRows() == 1) {
			$aRes = $db->getRow();
			$this->id = $aRes['id'];
			$this->locked = $aRes['locked'];
			if ($this->locked == 1) {
				$this->aError['CREDENTIAL_ACCOUNT_LOCKED'] = "Your account has been suspended.  Please contact support";
				return true;
			}
			$this->passHashSalt = $aRes['pass_salt'];
			$this->userIP = $aRes['ip'];


		} else {
			$this->aError['CREDENTIAL_USER_NOTFOUND'] = "Please enter a valid username.";
			return true;
		}

	}


	private function ipAddressCheck() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;

		if (!$_CONFIG['IP_ADDRESS_ACCESS_CHECK']) return false; /* is IP address check turned on? (defined in config) */


		if ($this->userIP == "") return false; /* No IP restriction applied for this user */

		/* get the users IP address from the request (including if they arrived via a proxy) */
		$requestIP = "";
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$requestIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (isset($_SERVER['REMOTE_IP'])) {
			$requestIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."() UserIP: ".$this->userIP);
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."() RemoteIp: ".$requestIP);

		if ($this->userIP != $requestIP) {
			$this->aError['IP_ADDR_CHECK_FAILED'] = "Access to your account is not permitted from this location.";
			return true;
		}

	}
 
	
	private function encryptPass() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		$this->encryptedPass = sha1($this->passHashSalt . $this->pass);

	}


	/* generate secure password hash - called by add user */
	public static function generatePassHash($pass,$salt) {

		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."()");

		return sha1($salt . $pass);

	}


	/* random salt to harden password hashing function, called by add user */
	public static function generatePassHashSalt() {

		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."()");

		return substr(md5(uniqid(rand(), true)), 0, SALT_LENGTH);
	}


	/*
	 * Recaptcha Anti-Spam Check (http://recaptcha.net/)
	 *
	 */
	private function recaptchaCheck($challenge_field, $response_field) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;

		if (!$this->recaptchCheckFl) return false; /* is captcha checking enabled? */

		$resp = null; /* the response from reCAPTCHA */

		if (isset($_POST["recaptcha_response_field"]) && (strlen($_POST["recaptcha_response_field"]) < 1)) {
			$this->aError['CAPTCHA'] = "Please enter captcha text matching displayed word.";
			return true;
		}

		$resp = recaptcha_check_answer ($_CONFIG['CAPTCHA_PRIVATE_KEY'],
		$_SERVER["REMOTE_ADDR"],
		$challenge_field,
		$response_field);

		if (!$resp->is_valid) {
			$this->aError['CAPTCHA'] = "The anti-spam captcha text was not correct.";
			return true;
		}
	}


	/* authenticate user against database */
	private function authenticate() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db,$_CONFIG;

		/* are we storing encrypted passwords ? */
		$pass = ($this->encryptPassFl) ? $this->encryptedPass : $this->pass;

		
		
		if ($db->query("SELECT id FROM euser WHERE uname = '".$this->uname."' AND  pass = '".$pass."';")) {

			if ($db->getNumRows() == 1) {
				$aRow = $db->getRow(PGSQL_ASSOC,0);
				$this->user_id = $aRow['id'];
				
				$db->query("UPDATE euser SET logins = logins+1, failed_logins = 0, last_login = CURRENT_TIMESTAMP, sess_id = '".$this->sess_id."' WHERE id = '".$this->user_id."';");
				
				$ip = (getenv(HTTP_X_FORWARDED_FOR)) ?  getenv(HTTP_X_FORWARDED_FOR) :  getenv(REMOTE_ADDR);
								
				Logger::DB(1,get_class($this)."::".__FUNCTION__."()",'LOGIN'.' Username: '.$this->uname." IP: ".$ip);
				return false;
			} else {
				$this->aError['CREDENTIAL_PASS_NOTFOUND'] = "Please enter a valid password.";
				$this->handleFailedLoginAttempt(); /* disable account if failed_logins > login attempts */
				return true;
			}
		} else {
			$this->aError['CREDENTIAL_TABLE_NOFOUND'] = "An error occured.  We are not able to process your login at this time.";
			Logger::DB(1,get_class($this)."::".__FUNCTION__."()",'ERROR : CREDENTIAL_TBL_WRITE_ERR ');
			return true;
		}
	}


	/* update failed logins counter, lock account if failed_logins > max_login_attempts */
	private function handleFailedLoginAttempt() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db,$_CONFIG;

		
		$iFailedLogins = $db->getFirstCell("SELECT failed_logins FROM euser WHERE id = ".$this->id);
		if (!is_numeric($iFailedLogins)) $iFailedLogins = 0;

		if ($iFailedLogins >= MAX_LOGIN_ATTEMPTS) {
			$db->query("UPDATE euser SET locked = 1 WHERE id = ".$this->id );
			Logger::DB(1,get_class($this)."::".__FUNCTION__."()",'LOGIN : ACCOUNT_LOCKED : '.'Username: '.$this->uname);
		} else {
			$iFailedLogins++;
			$db->query("UPDATE euser SET failed_logins = ".$iFailedLogins." WHERE id = ".$this->id );
		}
	}



	/* create unique md5 encrypted session token */
	private function createSessionID() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		srand((double)microtime()*1000000);
		$this->sess_id = md5(uniqid(rand()));
	}


	private function setSessionCookieHeader() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		$sess_id = $this->sess_id;
		$expires = time()+$this->session_expires;
		$cookiename =$this->cookiename;
		$domain = $this->cookie_domain;
		$path = $this->cookie_path;

		$COOKIE = "Set-Cookie: $cookiename=$sess_id";
		if (isset($expires) && ($expires > 0)) {
			$COOKIE .= "; EXPIRES=".gmdate("D, d M Y H:i:s",$expires) . " GMT";
		}
		if (isset($domain)) {
			$COOKIE .= "; DOMAIN=$domain";
		}
		if (isset($path)) {
			$COOKIE .= "; PATH=$path";
		}
		if (isset($secure) && $secure>0) {
			$COOKIE .= "; SECURE";
		}

		header($COOKIE,false);
	}


	/*
	 * Sets up a session to implement single sign-on for phpBB
	 *
	 * @note - required phpBB init headers in calling script
	 *
	 */
	private function phpBBLogin() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG,$user,$auth,$db;


		$user->session_begin();
		$auth->acl($user->data);


		$username = isset($_POST['uname']) ? $_POST['uname'] : "";
		$password = isset($_POST['pass']) ? $_POST['pass'] : "";
		$sid = isset($_SID) ? $_SID : "";

		$result = $auth->login($username, $password);

		if ($result['status'] == LOGIN_SUCCESS) {

			$expires = time()+$_CONFIG['SESSION_EXPIRES'];

			setcookie($_CONFIG['FORUM_COOKIENAME'], $sid, $expires, $_CONFIG['COOKIE_PATH'], $_CONFIG['COOKIE_DOMAIN']);

		}
	}


	public function doLogout($redirectUrl) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		//$this->phpBBLogout(); /* invalidate the phpBB session */
		$this->sess_id = "";
		$this->setSessionCookieHeader();
		$this->doRedirect($redirectUrl);
	}


	private function phpBBLogout() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		$sid = '';

		setcookie($_CONFIG['FORUM_COOKIENAME'], $sid, $_CONFIG['SESSION_EXPIRES'], $_CONFIG['COOKIE_PATH'], $_CONFIG['COOKIE_DOMAIN']);

	}


	private function doRedirect($redirectUrl) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		if (strlen($redirectUrl) < 1) die();

		?>
<script language="JavaScript">
			window.location="<?= $redirectUrl; ?>";
		</script>
		<?
		die;
	}


	// ---------------------------------------------------------------------


} // end login class ----------------------------------------------



?>
