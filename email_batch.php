<?php


/*
 * A batch utility (called by cron) to send queued email
 *  
 * eg.
 *  
 * #!/bin/bash
 * 
 * /usr/bin/php /www/vhosts/oneworld365.org/htdocs/email_batch.php oneworld365.org
 * /usr/bin/php /www/vhosts/oneworld365.org/htdocs/email_batch.php gapyear365.com
 * 
 * 
 */


define('LOG',true);
define('TEST_MODE',false); // sets email to address(es) to admin@oneworld365.org, dumps messages and metadata to STDOUT
define('ERROR_COMPANY_PROFILE_NOT_FOUND','Company profile not found id: ');
define('ERROR_PLACEMENT_PROFILE_NOT_FOUND','Placement profile not found id: ');

/*
 * Caller must supply site id
 * 	- config contains site specific email template params
 * 
 */
$brand = (strlen($argv[1]) > 1) ? $argv[1] : $_GET['BRAND'];
if (strlen($brand) < 1) die("ERROR : BRAND identifier must be supplied");

$mode = (isset($argv[2])) ? $argv[2] : "";


include("/www/vhosts/oneworld365.org/htdocs/conf/config.php");

require_once(ROOT_PATH."/classes/db_pgsql.class.php");
require_once(ROOT_PATH."/classes/logger.php");
require_once(ROOT_PATH."/classes/file.class.php");
require_once(ROOT_PATH."/classes/template.class.php");
require_once(ROOT_PATH."/classes/enquiry.class.php");
require_once(ROOT_PATH."/classes/ProfileInterface.php");
require_once(ROOT_PATH."/classes/ProfileAbstract.class.php");
require_once(ROOT_PATH."/classes/ProfileFactory.class.php");
require_once(ROOT_PATH."/classes/ProfilePlacement.class.php");
require_once(ROOT_PATH."/classes/ProfileCompany.class.php");
require_once(ROOT_PATH."/classes/ProfileGeneral.class.php");
require_once(ROOT_PATH."/classes/ProfileTour.class.php");
require_once(ROOT_PATH."/classes/ProfileJob.class.php");
require_once(ROOT_PATH."/classes/email2friend.class.php");
require_once(ROOT_PATH."/classes/image.class.php");
require_once(ROOT_PATH."/classes/EmailBatch.php");
require_once(ROOT_PATH."/classes/review.class.php");

require_once(ROOT_PATH."/classes/EmailSender.php");
require_once(ROOT_PATH."/classes/htmlMimeMail.php");
require_once(ROOT_PATH."/classes/RFC822.php");
require_once(ROOT_PATH."/classes/smtp.php");
require_once(ROOT_PATH."/classes/mimePart.php");






$db = new db($dsn,$debug = false);



define("JOBNAME","EMAIL_BATCH");


if (LOG) Logger::DB(3,JOBNAME,'STARTED PROCESSING');

$oEmailBatch = new EmailBatch();

switch($mode)
{
    case "Send2Friend" :
        $oEmailBatch->ProcessSend2FriendEmail();
        break;
    case "Enquiry" :
        $oEmailBatch->ProcessEnquiryEmail();
        break;
    case "EnquiryAutoResponse" :
        $oEmailBatch->ProcessEnquiryAutoResponseEmail();
        break;
    default: 
        $oEmailBatch->ProcessAll();
        
}

if (LOG) Logger::DB(3,JOBNAME,'FINISHED PROCESSING');




?>
