<?php


class Validation {

	
	public static function Sanitize(&$input) {
		
		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."()");

		if (is_array($input)) {
			foreach($input as $k => $v) {
				if (is_string($v)) {
					$input[$k] = htmlspecialchars($v,ENT_NOQUOTES,"UTF-8"); 
				}
			}
		} elseif (is_string($input)) {
			$input = htmlspecialchars($input,ENT_NOQUOTES,"UTF-8");
		}
			
	}

	
	public static function AddSlashes(&$input) {
		
		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."()");

		
		if (is_array($input)) {
			foreach($input as $k => $v) {
				if (is_string($v)) {
					$input[$k] = addslashes($v); 
				}
			}
		} elseif (is_string($input)) {
			$input = addslashes($input);
		}
				
		return $input;
	}	
	
	public static function IsValidDate($sStr) { 
		if (preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}/",$sStr)) {
			return true;
		}
	}
	
	public static function ValidatePlacement($p,&$aResponse) {

		global $db,$oAuth;
		
		$aResponse['msg'] = array();
		
        if (strlen($p['title']) < 1) $aResponse['msg']['title'] = "Please enter a title.";
        if (strlen($p['title']) > 80) $aResponse['msg']['title'] = "Title should be short - 80 characters or less.";
		
		
		if (($p['company_id'] == "select") || (!is_numeric($p['company_id']))) {
			$aResponse['msg']['company'] = "You must select a valid <b>company</b>.";
		}

		if (strlen($p['desc_short']) < 1) $aResponse['msg']['desc_short'] = "Please enter a short description (220 chars or less).</b>.";
        if (strlen($p['desc_short']) > 220) $aResponse['msg']['desc_short'] = "Short Description should be brief - a single paragraph, 220 characters or less.";

        if (strlen($p['apply_url']) > 255) { 
        	$aResponse['msg']['apply_url'] = "Apply URL must be less than 256 chars.";
        }

        if (strlen($p['keyword_exclude']) > 255) { 
        	$aResponse['msg']['keyword_exclude'] = "Keyword exclude must be less than 256 chars.";
        }
        
        
		$iCat = 0;
		$iAct = 0;
		$iCty = 0;	
		foreach($p as $k => $v) {
			if ((preg_match("/cat_/",$k)) && ($v == "on")) $iCat++;
			if ((preg_match("/act_/",$k)) && ($v == "on")) $iAct++;
			if ((preg_match("/cty_/",$k)) && ($v == "on")) $iCty++;			
		}

		if ($iCat == 0) $aResponse['msg']['category'] = "ERROR: Select at least one category.";
		if ($iAct == 0) $aResponse['msg']['activity'] = "ERROR: Select at least one activity.";
		if ($iCty == 0) $aResponse['msg']['country'] = "ERROR: Select at least one country.";
		
		//if (strlen($p['email']) < 1) $aResponse['msg']['email'] = "Please enter an enquiry / sales email.</b>.";
		if (strlen($p['url']) < 1) $aResponse['msg']['url'] = "Please enter a more info / apply / bookings url.</b>.";

		
		/*
		 * if admin is adding a placement 
		 * check that the selected profile type 
		 * is enabled on the company profile
		 * 
		 */
		if ($oAuth->oUser->isAdmin) {			
			$oCProfile = new CompanyProfile();
			$oCProfile->SetId($p['company_id']);
			$oCProfile->GetProfileOptionBitmapFromDB();
			if (!$oCProfile->HasProfileOption($p['profile_type'])) {
				$aResponse['msg']['profile_type'] = "This type of profile is not enabled on company profile.";
			}
		}		
		
		if ($p['profile_type'] == PROFILE_VOLUNTEER) {
			//if (strlen($p['duration_txt']) < 1) $aResponse['msg']['duration_txt'] = "Please specify placement duration.";
			//if (strlen($p['benefits']) < 1 ) $aResponse['msg']['benefits'] = "Please specify placement costs / salary.";
			//if (strlen($p['start_dates']) < 1 ) $aResponse['msg']['start_dates'] = "Please specify placement start date(s).";
		}
		
		if ($p['profile_type'] == PROFILE_TOUR) {
			if ($p['tour_duration'] == "null") $aResponse['msg']['tour_duration'] = "Please specify tour duration.";
			//if (strlen($p['tour_price']) < 1 ) $aResponse['msg']['tour_price'] = "Please specify the tour cost.";
		}

		if ($p['profile_type'] == PROFILE_JOB) {

			/*
			if (($p['StartDateDay'] == "null") || 
				($p['StartDateMonth'] == "null") ||
				($p['StartDateYear'] == "null")) {
				$aResponse['msg']['job_start_date'] = "Please specify job start date.";
			}
			
			if (($p['CloseDateDay'] == "null") || 
				($p['CloseDateMonth'] == "null") ||
				($p['CloseDateYear'] == "null")) {
				$aResponse['msg']['close_date'] = "Please specify job application closing date.";
			}
			*/

			if (strlen($p['reference']) > 29) {
				$aResponse['msg']['reference'] = "Reference must be less than 30chars.";
			}
			
			if (strlen($p['job_salary']) > 119) {
				$aResponse['msg']['job_salary'] = "Job salary must be less than 119 chars.";
			}

			if (strlen($p['experience']) > 2000) {
				$aResponse['msg']['experience'] = "Experience must be less than 2000 chars.";
			}
			
			if (strlen($p['start_dt_multiple']) > 219) {
				$aResponse['msg']['contract_type'] = "Start dates must be less than 220chars.";
			}
			
			/*
			if ($p['contract_type'] == "") {
				$aResponse['msg']['contract_type'] = "Please specify contract type.";
			}
			*/

			if (strlen($p['job_salary']) < 1) {
				$aResponse['msg']['job_salary'] = "Please specify the salary / pay.";
			}
						
		}
        
        /* validate contact email / info url */
        // @note - disabled 13/12/2008 pending bugfix
        /*
        if (strlen($p['email']) > 1) {
    		if (!Validation::IsValidEmail($p['email'])) {
				$aResponse['msg'] = "ERROR: Contact email does not appear to be valid.";
              	return false;    			    	
    		}
        }
        if (strlen($p['img_url1']) > 1) {
        	if (!Validation::IsValidRemoteImage($p['img_url1'],"Photo Url 1",$aResponse)) {
        		return false;
        	}
        }
        if (strlen($p['img_url2']) > 1) {
        	if (!Validation::IsValidRemoteImage($p['img_url2'],"Photo Url 2",$aResponse)) {
        		return false;
        	}
        }
		if (strlen($p['img_url3']) > 1) {
        	if (!Validation::IsValidRemoteImage($p['img_url3'],"Photo Url 3",$aResponse)) {
        		return false;
        	}
        }
		*/

		if (count($aResponse['msg']) >= 1) return false;
		
		return true;
	}

	public static function IsValidRemoteImage($sUrl,$sFieldName,&$aResponse) { 
		/* removed pending addition of a robust regex for url checking */
		//if (!Validation::IsValidUrl($sUrl)) {
        //        $aResponse['msg'] = "ERROR: ".$sFieldName." must be a valid url to your image eg. http://www.yourdomain.com/photo.jpg";
        //      return false;
        //} else {
		//	if (!Validation::RemoteFileExists($sUrl)) {
        //       $aResponse['msg'] = "ERROR: ".$sFieldName." not found at url : ". $sUrl;
        //      	return false;
		//	}
        //}
        return true;
	}
	
	
	/*
	 * Check that a remote file specified by url exists
	 * 
	 * @param string url
	 * @return boolean  
	 *  
	*/
	public static function RemoteFileExists($sUrl) {
		$addr = parse_url($sUrl);
		$addr['port']= 80;
		if (!$sh=@fsockopen($addr['host'],$addr['port'],$errorno,$errorstr,3)) return false;
		fputs($sh,"HEAD {$addr['path']} HTTP/1.1\r\nHost: {$addr['host']}\r\n\r\n");		
		$line=@fgets($sh,16);		
		if (preg_match('/^HTTP\/1.1 200 OK/',$line,$m)) {
			fclose($sh);
			return true;
		}
	}


	public static function ValidateCompany($p,&$aResponse) {

		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."()");
		
		global $oAuth;
		
		if (preg_replace("/ /","",$p['title']) == "") {
			$aResponse['msg']['title'] = "You must enter valid title.";
		}

		if (strlen($p['title']) > 80) {
			$aResponse['msg']['title'] = "Title should be short - 80 characters or less.";
		}
		
		if (preg_replace("/ /","",$p['desc_short']) == "") {
			$aResponse['msg']['desc_short'] = "You must enter valid short description.";
		}

		if (preg_replace("/ /","",$p['url']) == "") {
			$aResponse['msg']['url'] = "You must enter valid url.";
		}
		
		if (strlen($p['desc_short']) > 300) {
			$aResponse['msg']['desc_short'] = "Short Description should be brief - a single paragraph, 300 characters or less.";
		}

		if (strlen($p['desc_long']) > 20000) {
			$aResponse['msg']['desc_long'] = "Full Description should be less than 20000chars.";
		}

		if (strlen($p['logo_url']) > 256) {
			$aResponse['msg']['logo_url'] = "Logo Url must be less than 256 chars.";
		}

		if (strlen($p['img_url1']) > 256) {
			$aResponse['msg']['img_url1'] = "Photo url must be less than 256 chars.";
		}
			
		$iCat = 0;
		$iAct = 0;
		$iCty = 0;	
		foreach($p as $k => $v) {
			if ((preg_match("/cat_/",$k)) && ($v == "on")) $iCat++;
			if ((preg_match("/act_/",$k)) && ($v == "on")) $iAct++;
			if ((preg_match("/cty_/",$k)) && ($v == "on")) $iCty++;			
		}

		if ($iCat == 0) { 
			$aResponse['msg']['category'] = "Select at least one category.";
		}

		if ($iAct == 0) { 
			$aResponse['msg']['activity'] = "Select at least one activity.";
		}
		if ($iCty == 0) { 
			$aResponse['msg']['country'] = "Select at least one country.";
		}

		/*  validate listing options (ADMIN ONLY) */
		if (($oAuth->oUser->isAdmin) && ($p['prod_type'] >= BASIC_LISTING)) {

			global $aListingOption,$oProfile;
			
			if ($p['listing_type'] == "null") {
				$aResponse['msg']['listing_type'] = "Please select a listing deal type.";
			}
			/* listing deal type must match listing type */;
			if ($aListingOption[$p['listing_type']]['type'] != $p['prod_type']) {

				switch($p['prod_type']) {
					case SPONSORED_LISTING :
						$listing = "Sponsored Listing";
						break; 
					case ENHANCED_LISTING :
						$listing = "Enhanced Listing";
						break; 
					case BASIC_LISTING :
						$listing = "Basic Listing";
						break;
					default :				
						$listing = "Free Listing";
						break;
				}

				$aResponse['msg']['listing_type'] = "Listing deal type (".$aListingOption[$p['listing_type']]['label'].") does not match listing type (".$listing.").";
			}
						
			if (($p['ListingMonth'] == "null") || ($p['ListingYear'] == "null")) {
				$aResponse['msg']['listing_start_date'] = "Please enter the listing start date.";
			}
			
		}

		
		if ($oAuth->oUser->isAdmin) {
			if ((!isset($p['prof_opt_1'])) &&
				(!isset($p['prof_opt_2'])) &&
				(!isset($p['prof_opt_3']))
			) {
				$aResponse['msg']['prof_opt_1'] = "At least one profile type must be enabled or this company will be unable to post placements.";
			}
		}
				
		if (is_array($aResponse['msg']) && (count($aResponse['msg']))) {
			return false;
		}
		
		return true;
	}

	public static function IsValidEmail( $sEmail ){
		if(preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", trim(strtolower($sEmail)))) {
			return true;
		}
	}


}


?>
