<?php

define('TEST_MODE',false);

/*
* Created on 07-Nov-2008
* Author: Steve Edwards (13/06/2008)
* *******************************************************************************************************************
* Description: A wrapper class to enable sending of templated multi mime part messages
* ********************************************************************************************************************
*/

class EmailSender {

	/*
	 * SendMail : Send a multimime part email message
	 * 
	 * @param string path to html message template
	 * @param string path to plain text message template 
	 * @param array template message parameters
	 * @param string to address
	 * @param string subject
	 * @param string from address
	 * @param string return path address
	 *  
	*/
	public static function SendMail($sHtmlTemplatePath, $sTextTemplatePath, $aMsgParams, $sTo, $sSubject, $sFromAddr, $sReturnPath,$aAttachment = array()) {
		
		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__);
		
		if (TEST_MODE) {
			Logger::Msg("EmailHtmlTemplatePath: ".$sHtmlTemplatePath);
			Logger::Msg("EmailTextTemplatePath: ".$sTextTemplatePath);			
			Logger::Msg("EmailMsgParams :");
			Logger::Msg($aMsgParams);
			Logger::Msg("EmailToAddr: ".$sTo);
			Logger::Msg("EmailSubject: ".$sSubject);			
			Logger::Msg("EmailFromAddr: ".$sFromAddr);			
			Logger::Msg("EmailReturnPath: ".$sReturnPath);			
		}
		
		global $_CONFIG;
		
		
		/* load the html and plain text message templates */
		$sHtmlTemplate = file_get_contents ($_CONFIG['root_path'].$_CONFIG['template_home'].$_CONFIG['email_template_hdr']);
		$sHtmlTemplate .= file_get_contents ($sHtmlTemplatePath);
		$sHtmlTemplate .= file_get_contents ($_CONFIG['root_path'].$_CONFIG['template_home'].$_CONFIG['email_template_footer']);
		$sTextTemplate = file_get_contents ($sTextTemplatePath);
		$sTextTemplate .= file_get_contents($_CONFIG['root_path'].$_CONFIG['template_home']."/email_txt_footer.php");

		/* check that we have a set of msg params */
		if (!is_array($aMsgParams)) {
			return false;			
		}
	
		/* add the global site-specific params */
		$aMsgParams["SITE_TITLE"] = $_CONFIG['page_description'];
		$aMsgParams["SITE_INFO"] = $_CONFIG['site_info_email'];
		$aMsgParams["SITE_URL"] = $_CONFIG['url'];
		$aMsgParams["SITE_LOGO_URL"] = $_CONFIG['logo_url'];
		$aMsgParams["SITE_NAME"] = $_CONFIG['brand'];
		$aMsgParams["CONTACT_EMAIL"] = $_CONFIG['admin_email'];
		$aMsgParams["YEAR"] = date("Y");
		$aMsgParams["DISCLAIMER"] = (strlen($aMsgParams["DISCLAIMER"]) > 1) ? $aMsgParams["DISCLAIMER"] : "";
		
		/* parse message template substituting call time message parameters */
		foreach($aMsgParams as $k => $v) {
			$sHtmlTemplate = preg_replace("/::$k::/",$v,$sHtmlTemplate);
			$sTextTemplate = preg_replace("/::$k::/",$v,$sTextTemplate);			
		}
	
		if (TEST_MODE) {
	    		Logger::Msg("\n\n\n\n");
    			Logger::Msg($sHtmlTemplate);
    			Logger::Msg("\n\n\n\n");
   	 		Logger::Msg($sTextTemplate);
		}

		/* check that required msg details were supplied */
		if ((strlen($sReturnPath) < 1) ||
			(strlen($sSubject) < 1) ||
			(strlen($sTo) < 1) ||			
			(strlen($sFromAddr) < 1) ) {
			return false;
		}	

		/*
		* Create the mail object.
		*/
		$mail = new htmlMimeMail();
		
		/*
		* Add the text, html message components
		*/		
		$mail->setHtml($sHtmlTemplate, html_entity_decode($sTextTemplate));
	
		/*
		* Set the return path of the message
		*/
		$mail->setReturnPath($sReturnPath);
	
		/*
		* Set some headers
		*/
		$mail->setFrom($sFromAddr);
		$mail->setSubject(html_entity_decode($sSubject));

		$mail->setBcc($_CONFIG['bcc_list']);

		if (is_array($aAttachment)) {
			if (DEBUG) Logger::Msg("Processing Attachment : path = ".$aAttachment['path']);
			if (file_exists($aAttachment['path'])) {
				if (DEBUG) Logger::Msg("Attachement : File Exists");
				$attachment = $mail->getFile($aAttachment['path']);
				$mail->addAttachment($attachment, $aAttachment['name'], $aAttachment['type']);
			}
		}
		
		
		$sTo = (DEV) ? TEST_EMAIL : $sTo;

		
		/* send the message */
		$result = $mail->send(array($sTo), 'mail');
		
		if ($result) {
			return true;
		} else {
			
			Logger::DB(1, get_class()."::".__FUNCTION__, "Email send error");
		}

		
	}
	


}


?>
