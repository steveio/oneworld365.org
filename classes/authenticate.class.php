<?php
/*
*
* Authenticate.class
*
* Handles secure access, user account and permissions.
*
*/




class Authenticate {

    public function __construct(&$db,$redirect = false)
    {
        $this->Authenticate($db,$redirect);
    }

	function Authenticate(&$db,$redirect = false) {
		$this->db = $db;
		global $_CONFIG;
		$this->cookiename = $_CONFIG['cookiename'];
		$this->redirect = $redirect; // should we eject the user if authentication fails?
		$this->redirect_url = $_CONFIG['url']; // where to redirect a nonauthorised user to
		$this->isError = ""; // error flag
		$this->errorMsg = ""; // error condition
		$this->sessionID = ""; // unique user session ID
		$this->oUser = null; // a valid user object
	}


	function main() {
		// true represents error condition
		switch (true) {
			case $this->getSessionCookie():
			case $this->getUserInfo():

			// user is not successfully authenticated
			$this->authenticateFailed();
			break;
			// no errors detected
			default:
		}
	}


	// get client side session cookie
	function getSessionCookie() {
		$this->sessionId =  $_COOKIE[$this->cookiename];
		if ($this->sessionId == "") {
			$this->errorMsg = "Not Authenticated or Session Expired  -  Please Login...";
			return true;
		}
	}


	// get user info
	function getUserInfo() {

		$this->oUser = new User($this->db);

		$this->oUser->getUserBySessionId($this->sessionId);

		if (!$this->oUser->isValidUser) {
			$this->errorMsg = "Session expired - Please login to continue";
			return true;
		}
	}


	// authentication result handlers ------------------------------------------------

	// authenticate failed : display error msg and redirect 2 login page
	function authenticateFailed() {
		$this->isError = true;
	}


	function doRedirect() {
		?>
		<script language="JavaScript">
			alert('Your session has expired.  Please login again...');
			window.location="<?= $this->redirect_url; ?>";
		</script>
		<?
		die;
	}



	// -----------------------------------------------------------------------


} // end authentication class -----------------------------------------------
?>
