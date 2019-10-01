<?

/* set no cache headers */
header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); 
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); 
header( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
header( 'Cache-Control: post-check=0, pre-check=0', false ); 
header( 'Pragma: no-cache' ); 



/* required extranet classes */
require_once('./conf/config.php');
require_once($_CONFIG['root_path'].'/classes/login.class.php');
//require_once($_CONFIG['root_path'].'/lib/recaptcha/recaptchalib.php');
require_once($_CONFIG['root_path']."/header_new.php");
require_once($_CONFIG['root_path']."/footer_new.php");
require_once($_CONFIG['root_path']."/classes/EmailSender.php");
require_once($_CONFIG['root_path']."/classes/htmlMimeMail.php");
require_once($_CONFIG['root_path']."/classes/RFC822.php");
require_once($_CONFIG['root_path']."/classes/smtp.php");
require_once($_CONFIG['root_path']."/classes/mimePart.php");




$sDisplay = "REMINDER_FORM";

$aError = array(); /* a container to hold details of any errors */

/* get any referrer supplied a message (ie secure page login required) */  
if (isset($_GET['msg'])) $aError['REFERER_MSG'] = base64_decode($_GET['msg']);


ob_start(); /* start output buffering so we can issue header() (eg during login) */

/* begin the debug output block */
if (DEBUG) Logger::Msg("<div id='debug'><h1>_DEBUG</h1>");

/* connect to the database */
$db = new db($dsn,$debug = false);
  
//if (DEBUG) Logger::Msg($_REQUEST);


/* Handle Login Request */
if (isset($_POST["submit"])) {
	/* login to the extranet */
	$oLogin = new Login();

	$email = isset($_POST['email']) ? $_POST['email'] : ""; 
	
	$aError = array();
	
	if ($oLogin->doPasswordReminder($email,$aError)) {
		$sDisplay = "REMINDER_SENT";
	}
}


/* format any errors that occured for display */
$sMsgHTML = '';
if (count($aError) >= 1) {
	$sMsgHTML = AppError::GetErrorHtml($aError);
}

?>



<?= $oHeader->Render(); ?>

<!-- BEGIN Page Content Container -->
<div class="page_content content-wrap clear">
<div class="row pad-tbl clear">
<div class="col four-sm pad">


<? if ($sDisplay == "REMINDER_FORM") { ?>

	<div class="col four-sm pad-b">
	<h2>Password Reminder</h2>
	</div>

	<p>If you have forgotten your login details please enter your email address below.</p>
	<p>Alternatively <a href='/contact' title='contact us'>contact us</a> for further assistance.</p>

	<div id="login">
		<form action="" method="post">

		<div class="col two-sm border pad clear">
		
			<div id="msgtext" style="color: red; font-size: 10px;">
				<?= $sMsgHTML; ?>	
			</div>

			<div class="row2">		
				<span  class="label_col2">
					<label for="email" style="<?= strlen($aError['EMAIL']) > 1 ? "color:red;" : ""; ?>">Email Address: </label>
				</span>
				<span class="input_col2">
					<input title="Submit" type="text" class="ophsoInputBox" style='width: 200px;' name="email" id="email" maxlength="90" value="<?= isset($_POST['email']) ? $_POST['email'] : ""; ?>" />
				</span> 
			</div>

			<span class="label_col2">&nbsp;</span>
			<span class="input_col2"><input type="submit" title="submit" name="submit" value="submit" class="ophsoLoginButton" /> </span>
		</div>
		
		</form>
	</div>
	<div class="row2">
		<p2>Unauthorised access and/or misuse of the system is an offence under the Computer Misuse Act of 1990.<p2/>
		<p2>Any use must be in accordance with the remote access information security policy. Any actions taken in breach of this policy may result in legal action being taken.</p2>
	</div>
<?  } else { ?>

	<h2>Your login details have been emailed to you.</h2>
	<div class="pad-b">
	<a class="more-link" href='/contact' title='contact us'>Contact us for further assistance.</a>
	</div>
	<div class="pad-t">
	<a class="more-link" href='./login' title='login'>Click here to login.</a>
	</div>

<? } ?>
</div>
</div>
</div>
<!--  END page container -->

<?= $oFooter->Render(); ?>
