<?php

class AppError {
	
	public static function GetErrorHtml($aError) {
		$sErrorStr = '';
		if(is_array($aError) && count($aError) >= 1) {
			$sPlural = (count($aError) > 1) ? "s" : "";
			$sErrorStr .= count($aError)." error$sPlural occured : <br />";
			foreach($aError as $sErrorMsg) {
				$sErrorStr .= $sErrorMsg."<br />";
			}
			
		} elseif (is_string($aError)) { /* for legacy code */
			return $aError;
		}
		return $sErrorStr;
	}

	
	public static function StopRedirect($sUrl,$sMsg) {
		
		
		$sUrl = $sUrl . "?&m=". base64_encode($sMsg);

		header("Location: ".$sUrl);
		die();
		
	}
	
	
	public static function StopDie($sMsg) {
		
		die($sMsg);
	}
}


?>
