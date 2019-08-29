<?php
/*
*
*******************************************************************************************************************
* Description: A wrapper class to enable sending of templated multi mime part email messages
* 
********************************************************************************************************************
*/

class MailWrapper {

        /*
         * SendMail : Sending multimime part email message
         *
         * @param string html message
         * @param string plain text message
         * @param string to address
         * @param string subject
         * @param string from address
         * @param string return path address
         *
        */
        static public function SendMail($sMsgHTML, $sMsgPlainText, $sTo, $sSubject, $sFromAddr, $sReturnPath) {

                if (debug) Logger::Msg(get_class()."::".__FUNCTION__);

                if (debug) {
                        Logger::Msg("EmailToAddr: ".$sTo);
                        Logger::Msg("EmailSubject: ".$sSubject);
                        Logger::Msg("EmailFromAddr: ".$sFromAddr);
                        Logger::Msg("EmailReturnPath: ".$sReturnPath);
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
                $mail->setHtml($sMsgHTML, $sMsgPlaintext);

                /*
                * Set the return path of the message
                */
                $mail->setReturnPath($sReturnPath);

                /*
                * Set some headers
                */
                $mail->setFrom($sFromAddr);
                $mail->setSubject($sSubject);

                /* send the message */
                $result = $mail->send(array($sTo), 'mail');

                /* catch send error */
                if ($result != 1) {
                        return false;
                }


        }

        /*
         *  Send a profile to one or more recipients
         * 
         * 
         */
        public function Send2aFriend($sRecipient,$sMsg) {
			$aTo = MailWrapper::ParseRecipients($sRecipient);
        }

        /*
         * 
         * 
         */
        public static function ParseRecipients($sRecipient) {

        	if (!preg_match("@",$sRecipients)) return false;
        
        	if (preg_match(",",$sRecipient)) { /* multiple addresses */
				$aAddress = explode(",",$aRecipient);
			} else { /* a single address */
				$aAddress[] = $sRecipient;
			}
			return $aAddress;
        }

}


?>


