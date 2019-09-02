<?



class Date {

	public static function GetDateInput($sName,$bHasDay = true, $bHasMonth = true, $bHasYear = true,$iYFrom = 0, $iYTo = 5) {
		if ($bHasDay) {
			$oSelect = new Select($sName."Day",$sName."Day","",Date::GetDays(),true,$_REQUEST[$sName."Day"]);
			$sOutStr .= $oSelect->GetHtml();
		}
		if ($bHasYear) {
			$oSelect = new Select($sName."Month",$sName."Month","",Date::GetMonths(),false,$_REQUEST[$sName."Month"]);
			$sOutStr .= $oSelect->GetHtml();
		}
		if ($bHasYear) {
			$oSelect = new Select($sName."Year",$sName."Year","",Date::GetYears($iYFrom,$iYTo),true,$_REQUEST[$sName."Year"]);
			$sOutStr .= $oSelect->GetHtml();
		}
			
		return $sOutStr;
	}
	
	public static function GetDays() { 
		$aDays = array();
		foreach(range(1,31) as $iDay) {
			$aDays[] = sprintf("%02d",$iDay);
		}
		return $aDays;
	}
	
	public static function GetMonths() {
		return $aMonths = array("01"=>"Jan","02"=>"Feb","03"=>"Mar","04"=>"Apr","05"=>"May","06"=>"Jun","07"=>"Jul","08"=>"Aug","09"=>"Sep","10"=>"Oct","11"=>"Nov","12"=>"Dec");
	}
	
	public static function GetYears($iFrom = 0, $iTo = 5) {
		//return $aYears = array(2009,2010,2011,2012,2013,2014);

		$yearRangeFrom = $iFrom;
		$yearRangeTo = $iTo;

		$thisYear = date('Y');
		$startYear = ($thisYear - $yearRangeFrom);
		$endYear = ($thisYear + $yearRangeTo);
		
		$aYears = array();
		
		foreach (range($endYear, $startYear) as $year) {
			$aYears[] = $year; 
		}
		
		return array_reverse($aYears);
		
	}
}

?>