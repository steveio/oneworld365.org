<?php



class Mapping {
	
	public static function Update($bAdminRequired,$sTbl,$sKey,$iId,$aFormValues,$sFormKeyPrefix,$sKey2) {

		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."()");
		
		global $db,$oAuth;

		if (!is_numeric($iId)) return false;
		if (!is_array($aFormValues)) return false;
		
		if ($bAdminRequired) {
			if (!$oAuth->oUser->isAdmin) {
				return false;
			}
		}
		
		$res = $db->query("DELETE FROM ".$sTbl." WHERE ".$sKey." = ".$iId);
		if (!$res) return false; 
		
		// insert new mappings....
		foreach ($aFormValues as $k => $v) {
			
			//if (DEBUG) Logger::Msg($sFormKeyPrefix.":::".$k.":::".$v);
			
			switch(true) {				
				case preg_match("/".$sFormKeyPrefix."/",$k) :
						$iId2 = (int) substr($k,4,5);

						//if (DEBUG) Logger::Msg($iId2);
						
						if ((is_numeric($iId2)) && ($v == "on")) {
							$res = $db->query("INSERT INTO ".$sTbl." ($sKey,".$sKey2.") VALUES ($iId,$iId2);");
							if (!$res) return false;
						}
					break;
			}
		}
		return true;
	}

	public static function GetFromRequest($aRequest,$mode = "KEYS") {
		
		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."()");
		
		if (!is_array($aRequest)) return array();
		
		$aResult['cat'] = array();
		$aResult['act'] = array();
		$aResult['cty'] = array();
		$aResult['ctn'] = array();
		
		foreach($aRequest as $k => $v) {

				switch(true) {
					case preg_match("/cat_/",$k) :
						if ($mode == "KEYS") {
							if ($v == "on") $aResult['cat'][] = preg_replace("/cat_/","",$k);
						}
						break;
					case preg_match("/act_/",$k) :
						if ($mode == "KEYS") {
							if ($v == "on") $aResult['act'][] = preg_replace("/act_/","",$k);
						}
						break;
					case preg_match("/cty_/",$k) :
						if ($mode == "KEYS") {
							if ($v == "on") $aResult['cty'][] = preg_replace("/cty_/","",$k);
						}
						break;
					case preg_match("/ctn_/",$k) :
						if ($mode == "KEYS") {
							if ($v == "on") $aResult['ctn'][] = preg_replace("/ctn_/","",$k);
						}
						break;
						
				}
		}
		
		return $aResult;
	}

	/*
	 * @NOTE - not sure this was ever finished or if it is used?
	 * 			looks like it should have been fully generalised
	 * 			use GetByKey() below instead			
	 * 
	 * 
	 * 
	 * 
	 */
	
	public static function GetFromRequestByKey($aRequest,$sKey) {
		
		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."()");
		
		if (!is_array($aRequest)) return array();
		
		$aResult = array();
		
		foreach($aRequest as $k => $v) {

				switch(true) {
					case preg_match("/".$sKey."/",$k) :
						if ($mode == "KEYS") {
							if ($v == "on") $aResult['cat'][] = preg_replace("/cat_/","",$k);
						}
						break;
						
				}
		}
		
		return $aResult;
	}
	
	public static function GetIdByKey($a,$key) {

		if (!is_array($a)) return false;
		
		$aOut = array();
		
		foreach($a as $k => $v) {
			if (preg_match("/".$key."/",$k)) {
				$id = preg_replace("/".$key."/","",$k);
				if (is_numeric($id)) {
					$aOut[] = (int) $id;
				}
			}
		}		

		return $aOut;
	}
	
	
	/*
	 * Given a key prefix and an array, create an 8bit bitmap based on submitted values
	 * 
	 * eg 
	 * $sKey = "prof_opt_";
	 * $aInput = array ("prof_opt_1" => "on","prof_opt_3" => "on"); 
	 *  
	 * return $sBitmap = "10100000";
	 * 
	 */
	public static function GetBitmapFromRequest($sKey,$aInput,$default = 0) {

		$aOut = array();
		
		/* initialise the bitmap */
		$sVal = ($default == 1) ? "1" : "0";
		for($i=1;$i<8;$i++) {
			$aOut[$i] = $sVal; 	
		}
 
		foreach($aInput as $k => $v) {
			$aBits = explode("_",$k);
			$idx = $aBits[2];
			if (preg_match("/".$sKey."/",$k)) {
				$aOut[$idx] = ($v == "on") ? "1" : "0";  
			}
		}
		
		return implode("",$aOut);
	}

	
}



?>