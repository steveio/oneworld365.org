<?php

class EmailBatch {
    
    
    private $sHtmlTemplatePath;
    private $sTextTemplatePath;
    private $aMsgParams;
    private $sTo;
    private $sSubject;
    private $sFromAddr;
    private $sReturnPath;
    
    
    public function __Construct() {
        $this->aAttachment = array();
    }
    
    public function ProcessAll() {
        
        if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__);
        
        //if (LOG) Logger::DB(3,JOBNAME,'EmailBatch->ProcessAll()');
        
        /*
         * Email jobs to be batch processed are registered here...
         *
         */
        $this->ProcessEnquiryEmail();
        
        $this->ProcessEnquiryAutoResponseEmail();
        
        $this->ProcessSend2FriendEmail();
        
    }
    
    private function GetHtmlTemplatePath() {
        return $this->sHtmlTemplatePath;
    }
    
    private function SetHtmlTemplatePath($sPath) {
        $this->sHtmlTemplatePath = $sPath;
    }
    
    private function GetTextTemplatePath() {
        return $this->sTextTemplatePath;
    }
    
    private function SetTextTemplatePath($sPath) {
        $this->sTextTemplatePath = $sPath;
    }
    
    private function GetMsgParams() {
        return $this->aMsgParams;
    }
    
    private function SetMsgParams($aMsgParams) {
        $this->aMsgParams = $aMsgParams;
    }
    
    private function SetTo($sTo) {
        //if (LOG) Logger::DB(3,JOBNAME,'EmailBatch->SetTo() '.$sTo);
        $this->sTo = $sTo;
    }
    
    private function GetTo() {
        return $this->sTo;
    }
    
    private function SetSubject($sSubject) {
        $this->sSubject = $sSubject;
    }
    
    private function GetSubject() {
        return $this->sSubject;
    }
    
    private function SetFromAddr($sFromAddr) {
        $this->sFromAddr = $sFromAddr;
    }
    
    private function GetFromAddr() {
        return $this->sFromAddr;
    }
    
    private function SetReturnPath($sReturnPath) {
        $this->sReturnPath = $sReturnPath;
    }
    
    private function GetReturnPath() {
        return $this->sReturnPath;
    }
    
    private function SetAttachment($aAttachment) {
        $this->aAttachment = $aAttachment;
    }
    
    private function GetAttachment() {
        return $this->aAttachment;
    }
    
    private function UnsetAttachment() {
        unset($this->aAttachment);
    }
    
    
    /*
     * Enquiry Email
     *
     */
    public function ProcessEnquiryEmail() {
        
        if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__);
        
        global $db,$_CONFIG;
        
        //if (LOG) Logger::DB(3,JOBNAME,'EmailBatch->ProcessEnquiryEmail()');
        
        $this->SetHtmlTemplatePath($_CONFIG['root_path'].$_CONFIG['template_home']."/enquiry_html.php");
        $this->SetTextTemplatePath($_CONFIG['root_path'].$_CONFIG['template_home']."/enquiry_txt.php");
        
        
        $oEnquiry = new Enquiry();
        
        /*
         * Get all pending enquiries for the site being currently processed
         *
         */
        $aId = $oEnquiry->GetByStatus(1,$_CONFIG['site_id']);
        
        if (!$aId) return false;
        
        if (LOG) Logger::DB(3,JOBNAME,'EmailBatch->ProcessEnquiryEmail() found '.count($aId)." pending enquiry emails to send");
        
        foreach($aId as $id) {
            
            try {
                $oEnquiry->GetById($id); /* get details of the original enquiry */
                
                try {
                    $oProfile = ProfileFactory::Get($oEnquiry->GetLinkTo());
                    $aResult = $oProfile->GetProfileById($oEnquiry->GetLinkId());
                    $oProfile->SetFromArray($aResult);
                    
                } catch (Exception $e) {
                    if (LOG) Logger::DB(3,JOBNAME,'EmailBatch->ProcessEnquiryEmail() '.get_class($oProfile).' ('.$oEnquiry->GetLinkId().') not found');
                    $this->SetFailed($id);
                }
                
                if (TEST_MODE) {
                    $this->SetTo(TEST_EMAIL);
                } else {
                    if (strlen($oProfile->GetEmail()) < 1) {
                        $this->SetFailed($id);
                        continue;
                    }
                    
                    $this->SetTo($oProfile->GetEmail());
                }
                
                $sSubject = "New ".$oEnquiry->GetEnquiryTypeLabel() ." from ".$_CONFIG['site_title'];
                $this->SetSubject($sSubject);
                
                $this->SetFromAddr($_CONFIG['website_email']);
                //$this->SetReturnPath($_CONFIG['website_email']);
                $this->SetReturnPath($this->GetTo());
                
                $aParams = array(
                    "DISCLAIMER" => "This message has been sent to you as your email address is being displayed on a profile on our website.",
                    "ENQUIRY_TYPE" => $oEnquiry->GetEnquiryTypeLabel(),
                    "COMPANY_NAME" => $oProfile->GetCompanyName(),
                    "PROFILE_TYPE_LABEL" => strtolower($oProfile->GetTypeLabel()),
                    "PROFILE_NAME" => $oProfile->GetTitle(),
                    "GENERIC" => "
											<tr><td><b>date:</b></td> <td>".$oEnquiry->GetDate()."</td></tr>
											<tr><td><b>from:</b></td> <td>".htmlentities($oEnquiry->GetName())."</td></tr>
											<tr><td><b>country:</b></td> <td>".$oEnquiry->GetCountryName()."</td></tr>
											<tr><td><b>email:</b></td> <td>".$oEnquiry->GetEmail()."</td></tr>
											<tr><td><b>tel:</b></td> <td>".$oEnquiry->GetTel()."</td></tr>
											",
                    "GENERIC_TXT" => "date: ".$oEnquiry->GetDate()."
	from: ".$oEnquiry->GetName()."
	country: ".$oEnquiry->GetCountryName()."
	email: ".$oEnquiry->GetEmail()."
	tel: ".$oEnquiry->GetTel(),
                    "ENQUIRY" => "",
                    "BOOKING" => "",
                    "BROCHURE" => "",
                    "JOB_APP" => "",
                    
                    "ENQUIRY_TXT" => "",
                    "BOOKING_TXT" => "",
                    "BROCHURE_TXT" => "",
                    "JOB_APP_TXT" => ""
                    
                    
                );
                
                /* enquiry type specific fields */
                
                
                if ($oEnquiry->GetEnquiryType() == "BOOKING") {
                    $aParams['BOOKING'] = "<tr><td><b>group size:</b></td> <td>".$oEnquiry->GetGroupSize() ."</td></tr>
		<tr><td><b>budget:</b></td> <td>".$oEnquiry->GetBudget()."</td></tr>
		<tr><td><b>est departure date:</b></td> <td>".$oEnquiry->GetDeptDate()."</td></tr>
		<tr><td><b>enquiry:</b></td> <td>".htmlentities($oEnquiry->GetEnquiry())."</td></tr>";
                    $aParams['BOOKING_TXT'] = "group size: ".$oEnquiry->GetGroupSize() ."
	budget: ".$oEnquiry->GetBudget()."
	est departure date: ".$oEnquiry->GetDeptDate()."
	enquiry: ".$oEnquiry->GetEnquiry();
                    
                }
                
                if ($oEnquiry->GetEnquiryType() == "GENERAL") {
                    $aParams['ENQUIRY'] = "<tr><td><b>enquiry:</b></td> <td>".htmlentities($oEnquiry->GetEnquiry())."</td></tr>";
                    $aParams['ENQUIRY_TXT'] = "enquiry: ".$oEnquiry->GetEnquiry();
                }
                
                if ($oEnquiry->GetEnquiryType() == "BROCHURE") {
                    $aParams['BROCHURE'] =  "<tr><td></td></tr>";
                    $aParams['BROCHURE_TXT'] =  "";
                }
                
                if ($oEnquiry->GetEnquiryType() == "JOB_APP") {
                    $aParams['JOB_APP'] = "<tr><td><b>application letter:</b></td> <td>".htmlentities($oEnquiry->GetApplyLetter())."</td></tr>
	<tr><td><b>experience:</b></td> <td>".$oEnquiry->GetExperience()."</td></tr><tr><td><b>date of birth:</b></td> <td>".$oEnquiry->GetDOB()."</td></tr>";
                    $aParams['JOB_APP_TXT'] = "application letter: ".$oEnquiry->GetApplyLetter()."
	experience: ".$oEnquiry->GetExperience()."
	date of birth: ".$oEnquiry->GetDOB();
                    
                    /* process cv attachment */
                    $db->query("SELECT name,size,ext,mime FROM cv WHERE enquiry_id = ".$oEnquiry->GetId());
                    
                    if ($db->getNumRows() == 1) {
                        if (DEBUG) Logger::Msg("Found 1 Attachment");
                        $aResult = $db->getRow();
                        
                        $sTmpPath = $_CONFIG['root_path'] . "/upload/cv/";
                        $sFileName = "cv_".$oEnquiry->GetId();
                        
                        $aAttachment = array(
                            "path" => $sTmpPath.$sFileName
                            ,"name" => stripslashes($aResult['name'])
                            ,"type" => $aResult['type']
                        );
                        
                        if (DEBUG) Logger::Msg($aAttachment);
                        
                        $this->SetAttachment($aAttachment);
                    } else {
                        if (DEBUG) Logger::Msg("Found 0 Attachments");
                    }
                    
                    
                }
                
                
                
                if ($oProfile->GetLinkTo() == "COMPANY") {
                    $aParams['MSG_TXT'] = "This is a new enquiry for ".$oProfile->GetTitle()." from ".$_CONFIG['site_title'].".";
                    $aParams['MSG_HTML'] = "<p>".$aParams['MSG_TXT']."</p>";
                } elseif ($oProfile->GetLinkTo() == "PLACEMENT") {
                    $aParams['MSG_TXT'] = "This is a new enquiry for ".$oProfile->GetCompanyName()." about ".$oProfile->GetTitle();
                    $aParams['MSG_HTML'] = "<p>".$aParams['MSG_TXT']."</p>";
                    
                }
                
                
                
                $this->SetMsgParams($aParams);
                
                if ($this->Process()) {
                    $db->query("UPDATE enquiry SET status = 2, processed = now()::timestamp WHERE id = ".$oEnquiry->GetId());
                    $this->UnsetAttachment();
                }
                
            } catch (Exception $e) {
                error_log($e->getMessage());
            }
            
        }
        
    }
    
    /*
     * Enquiry Auto-Response Email
     *
     */
    public function ProcessEnquiryAutoResponseEmail() {
        
        if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__);
        
        global $db,$_CONFIG;
        
        //if (LOG) Logger::DB(3,JOBNAME,'EmailBatch->ProcessEnquiryAutoResponseEmail()');
        
        $this->SetHtmlTemplatePath($_CONFIG['root_path'].$_CONFIG['template_home']."/enquiry_autoresponse_html.php");
        $this->SetTextTemplatePath($_CONFIG['root_path'].$_CONFIG['template_home']."/enquiry_autoresponse_txt.php");
        
        $oEnquiry = new Enquiry();
        
        $db->query("BEGIN TRANSACTION");

        /*
         * Get sent enquiries, where auto-response email is pending,
         * lock rows to prevent concurrent processing error
         *
         */
        $aId = $oEnquiry->GetByStatus(2,$_CONFIG['site_id'], $bForUpdate = true);        
        if (!$aId) return false;

        if (LOG) Logger::DB(3,JOBNAME,'EmailBatch->ProcessEnquiryAutoResponseEmail() found '.count($aId).' enquiries pending auto-response email');

        $aEmailAddress = array(); // only send 1 enquiry auto-response per sender email address

        foreach($aId as $id) {

            if (LOG) Logger::DB(3,JOBNAME,'EmailBatch->ProcessEnquiryEmail() processing enquiry id: '.$id);

            $oEnquiry->GetById($id); /* get details of the original enquiry */

            try {
                try {
                    $oProfile = ProfileFactory::Get($oEnquiry->GetLinkTo());
                    $aResult = $oProfile->GetProfileById($oEnquiry->GetLinkId());
                    $oProfile->SetFromArray($aResult);
                    
                } catch (Exception $e) {
                    if (LOG) Logger::DB(3,JOBNAME,'EmailBatch->ProcessEnquiryEmail() '.get_class($oProfile).' ('.$oEnquiry->GetLinkId().') not found');
                    $this->SetFailed($id);
                }
                
                if (TEST_MODE) {
                    $this->SetTo(TEST_EMAIL);
                } else {
                    if (strlen($oEnquiry->GetEmail()) < 1) {
                        $this->SetFailed($id,6);
                        continue;
                    }
                    
                    $this->SetTo($oEnquiry->GetEmail());
                }
                
                $sSubject = "Thank you for using ".$_CONFIG['site_title'];
                $this->SetSubject($sSubject);
                
                $this->SetFromAddr($_CONFIG['website_email']);
                $this->SetReturnPath($_CONFIG['website_email']);
                
                $aParams = array(
                    "DISCLAIMER" => "",
                    "ENQUIRY_TYPE" => $sSubject,
                    "COMPANY_NAME" => $oProfile->GetCompanyName(),
                    "PROFILE_TYPE_LABEL" => strtolower($oProfile->GetTypeLabel()),
                    "PROFILE_NAME" => $oProfile->GetTitle(),
                    "SENDER_NAME" => $oEnquiry->GetName(),
                    "GENERIC" => "",
                    "GENERIC_TXT" => "",
                    "ENQUIRY" => "",
                    "BOOKING" => "",
                    "BROCHURE" => "",
                    "JOB_APP" => "",
                    "ENQUIRY_TXT" => "",
                    "BOOKING_TXT" => "",
                    "BROCHURE_TXT" => "",
                    "JOB_APP_TXT" => ""

                );
                
                $this->SetMsgParams($aParams);
                
                if ($this->Process()) {
                    
                    if (!in_array($oEnquiry->GetEmail(),$aEmailAddress))
                        $db->query("UPDATE enquiry SET status = 7, processed = now()::timestamp WHERE email = '".$oEnquiry->GetEmail()."' AND id IN (".implode(",",$aId).")");

                    $aEmailAddress[] = $oEnquiry->GetEmail();

                    $db->query("UPDATE enquiry SET status = 7, processed = now()::timestamp WHERE id = ".$oEnquiry->GetId());

                } else {
                    throw new Exception("Failed to send enquiry id: ".$oEnquiry->GetId());
                }

            } catch (Exception $e) {
                // leave existing enquiry.status to allow re-processing in case of transient send error
                error_log($e->getMessage());
            }
            
        }

        $db->query("COMMIT");
    }

    /*
     * Send to friend Email
     *
     */
    public function ProcessSend2FriendEmail() {
        
        if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__);
        
        global $db,$_CONFIG;
        
        //if (LOG) Logger::DB(3,JOBNAME,'EmailBatch->ProcessSend2FriendEmail()');
        
        $this->SetHtmlTemplatePath($_CONFIG['root_path'].$_CONFIG['template_home']."/generic_html.php");
        $this->SetTextTemplatePath($_CONFIG['root_path'].$_CONFIG['template_home']."/generic_txt.php");
        
        
        $oEmail = new Email2Friend();
        
        $aId = $oEmail->GetByStatus('F',$_CONFIG['site_id']);
        
        if (!$aId) return false;
        
        foreach($aId as $id) {
            
            /* get message details */
            $oEmail->GetById($id);
            
            /* get profile details */
            $oProfile = ProfileFactory::Get($oEmail->GetLinkTo());
            $aResult = $oProfile->GetProfileById($oEmail->GetLinkId());
            $oProfile->SetFromArray($aResult);
            
            /* compose the message html & text parts */
            $oEmail->SetProfile($oProfile);
            $oEmail->Compose();
            
            
            if (TEST_MODE) {
                $this->SetTo(TEST_EMAIL);
            } else {
                $this->SetTo($oEmail->GetToAddr());
            }
            
            $this->SetSubject($oEmail->GetSubject());
            
            $this->SetFromAddr($_CONFIG['website_email']);
            $this->SetReturnPath($_CONFIG['website_email']);
            
            
            $this->SetMsgParams($oEmail->GetMsgParams());
            
            if ($this->Process()) {
                $db->query("UPDATE email_to_friend SET status = 'T', processed = now()::timestamp WHERE id = ".$oEmail->GetId());
            }
        }
        
    }
    
    
    private function Process() {
        
        if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__);
        
        sleep(15); // to prevent spam policy violation and/or memory spike when sending large email batches
        return EmailSender::SendMail($this->GetHtmlTemplatePath(), $this->GetTextTemplatePath(), $this->GetMsgParams(), $this->GetTo(), $this->GetSubject(), $this->GetFromAddr(), $this->GetReturnPath(),$this->GetAttachment());
        
    }
    
    
    private function SetFailed($id,$iStatus = 4) {
        
        global $db;
        
        if (!is_numeric($id)) return false;
        
        $sql = "UPDATE enquiry SET status = ".$iStatus.", processed = now()::timestamp WHERE id = ".$id;
        
        $db->query($sql);
        
    }
    
}

?>
