<?php
/*
* Created on 21-Aug-2008
* Author: Steve Edwards
*
*******************************************************************************************************************
* Description:  Simple Logging / Debugging Output System
*
*
********************************************************************************************************************
*/



class Logger{

        public static function Msg($mMsg,$sOutFormat = "html") {
        	
				$sNl = "<br />";
				$sPreOpen = "<pre>";
				$sPreClose = "</pre>";
                
				if ($sOutFormat == 'plaintext') {
                        $sNl = "\n\r";
                        $sPreOpen = "\n\r\n\r";
                        $sPreClose = "\n\r\n\r";
                }

                $sOutStr = '';

                switch(true) {
                        case is_array($mMsg) :
                                $sOutStr .= $sPreOpen;
                                $sOutStr .= Logger::var_dump_ret($mMsg);
                                $sOutStr .= $sPreClose;
                                break;
                        case is_object($mMsg) :
                                $sOutStr .= $sPreOpen;
                                $sOutStr .= Logger::var_dump_ret($mMsg);
                                $sOutStr .= $sPreClose;
                                break;

                        default :
                                $sOutStr .= $mMsg.$sNl;
                                break;
                }

                print $sOutStr;
                

        }

        private static function write2file($sPath = debug_logfile_path,$sOutStr) {
                $rFile=fopen($sPath,"a") or die(DEBUG_LOGILE_OPEN_FAILURE);
                fwrite($rFile, $sOutStr);
                fclose($rFile);
        }

        public static function var_dump_ret($mixed = null) {
          ob_start();
          var_dump($mixed);
          $content = ob_get_contents();
          ob_end_clean();
          return $content;
        }

        /*
         * Originally intended to log to database
         * 	but - postgresql has no transaction pragma autonomous so rollback wipes log rows
         *  so - now logs to a file
         * 
         */
        public static function DB($level,$src,$msg = "") {
        	
        	if ($level <= LOG_LEVEL) {
	        	$sPath = LOG_PATH."app_err.log";
	        	switch($level) {
	        		case "1" :
	        			$sType = "[ERROR]";
	        			break;
	        		case "2" :
					$sType = "[INFO]";
					break;
	        		case "3" :
	        			$sType = "[DEBUG]";
	        			break;			
	        	}
	        	
	        	$sData = date("M d H:i:s")." ".$sType." ".$src ." " .$msg ."\n\r";
	        	
	        	//Logger::Msg($sData);
	        	File::Write($sData,$sPath,$mode = "a");
	        	
        	}        	
        	        	
        }
}

?>
