<?php



class ProfileFactory {

	public static function Get($sProfileType) {
		switch($sProfileType) {
			case PROFILE_PLACEMENT :
				return $oProfile = new PlacementProfile();
				break;
			case PROFILE_COMPANY :
				return $oProfile = new CompanyProfile();
				break;
			case PROFILE_VOLUNTEER :
				return $oProfile = new GeneralProfile();
				break;
			case PROFILE_TOUR :
				return $oProfile = new TourProfile();
				break;
			case PROFILE_JOB :
				return $oProfile = new JobProfile();
				break;
			case PROFILE_SUMMERCAMP :
				return $oProfile = new SummerCampProfile; 
				break;
			case PROFILE_VOLUNTEER_PROJECT :
				return $oProfile = new VolunteerTravelProjectProfile();
				break;
			case PROFILE_SEASONALJOBS :
				return $oProfile = new SeasonalJobEmployerProfile();
				break;
			case PROFILE_TEACHING :
				return $oProfile = new TeachingProjectProfile();
				break;
		}

	}
	
	
	public static function GetCompanyProfile($sProfileType) {
		
		
	}

};




?>
