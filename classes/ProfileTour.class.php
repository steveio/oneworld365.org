<?php

/*
 * Tour Profile Class
 *
 *
 */



class TourProfile extends PlacementProfile {

	protected $code;
	//protected $duration_txt; // @depreciated
	protected $dates;
	protected $itinery;
	protected $tour_price; // text - all info about tour costs 
    // @depreciated (merged into tour_price)
	//protected $local_payment;
	//protected $included;
	//protected $not_included;
	protected $tour_requirements;

	protected $duration_from_id;
	protected $duration_to_id;
	protected $price_from_id;
	protected $price_to_id;
	protected $currency_id;
	protected $group_size_id;

	protected $transport_id_list;
	protected $meals_id_list;
	protected $accomodation_id_list;
	
	
	private $sTblName; /* tour profile table */	

	public function __Construct() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		$this->sTblName = "profile_tour";
		$this->SetLinkTo("PLACEMENT");

		$this->transport_id_list = array();
		$this->meals_id_list = array();
		$this->accomodation_id_list = array();

		$this->transport_labels = array();
		$this->meals_labels = array();
		$this->accomodation_labels = array();
	}

	
	public function GetTypeLabel() {
		return "Tour";
	}	
	

	public function GetById($id) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		if (!is_numeric($id)) return false;

		parent::__Construct();
		
		parent::SetSubTypeTable($this->GetSubTypeTable());
		parent::SetSubTypeFields($this->GetSubTypeFields());

		$oResult = parent::GetProfileById($id);

		if (!$oResult) return FALSE;
		
		foreach($oResult as $k => $v) {
			$this->$k = is_string($v) ? stripslashes($v) : $v;
		}

		parent::GetCategoryInfo();
		parent::GetActivityInfo();
		parent::GetCountryInfo();
		parent::GetImages();

		$this->SetTransportIdList();
		$this->SetMealsIdList();
		$this->SetAccomodationIdList();
		
		return TRUE;		
	}


	private function GetSubTypeTable() {
		return $sSubTypeTable = "LEFT OUTER JOIN ".$this->sTblName." p2 ON p.id = p2.p_hdr_id";
	}

	private function GetSubTypeFields() {

		return $sSubTypeFields = "
							,p2.code
							,p2.dates
							,p2.itinery
							,p2.price as tour_price
							,p2.requirements as tour_requirements
							,p2.duration_from_id
							,p2.duration_to_id
							,p2.price_from_id
							,p2.price_to_id
							,p2.currency_id
							,p2.group_size_id
							";			
	}


	public function AddSubTypeRecord($p) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		/* check that profile_hdr id and subtype record exist */
		if (!is_numeric($p['id'])) return false;

		$sql = "INSERT INTO ".$this->sTblName." (
										p_hdr_id
										,code
										,dates
										,itinery
										,price
										,requirements
										,duration_from_id
										,duration_to_id
										,price_from_id
										,price_to_id
										,currency_id
										,group_size_id
									) VALUES (
										".$p['id']."
										,'".$p[PROFILE_FIELD_PLACEMENT_TOUR_CODE]."'
										,'".$p[PROFILE_FIELD_PLACEMENT_START_DATES]."'
										,'".$p[PROFILE_FIELD_PLACEMENT_ITINERY]."'
										,'".$p[PROFILE_FIELD_PLACEMENT_TOUR_PRICE]."'
										,'".$p[PROFILE_FIELD_PLACEMENT_TOUR_REQUIREMENTS]."'
										,".$p[PROFILE_FIELD_PLACEMENT_TOUR_DURATION_FROM]."
										,".$p[PROFILE_FIELD_PLACEMENT_TOUR_DURATION_TO]."
										,".$p[PROFILE_FIELD_PLACEMENT_TOUR_PRICE_FROM]."
										,".$p[PROFILE_FIELD_PLACEMENT_TOUR_PRICE_TO]."
										,".$p[PROFILE_FIELD_PLACEMENT_TOUR_CURRENCY]."
										,".$p[PROFILE_FIELD_PLACEMENT_GROUP_SIZE]."
										);";

		$db->query($sql);

		if ($db->getAffectedRows() == 1) {
			$this->UpdateRefData($p);
			return true;
		} else {
			Logger::DB(1,get_class($this)."::".__FUNCTION__."()",$sql);
		}

	}



	public function UpdateSubTypeRecord($p) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		/* check that profile_hdr id and subtype record exist */
		if (!is_numeric($p['id'])) return false;

		//if (!is_numeric($p['type'])) $p['type'] = 'null';
		//if (!is_numeric($p['duration'])) $p['duration'] = 'null';

		/* is there an existing sub-type record ? */
		if (!$db->getFirstCell("SELECT 1 FROM ".$this->sTblName." WHERE p_hdr_id = ".$p['id'])) {
			return $this->AddSubTypeRecord($p);
		}
		
		$sql = "UPDATE ".$this->sTblName."
					SET  
					code = '".$p[PROFILE_FIELD_PLACEMENT_TOUR_CODE]."' 
					,dates = '".$p[PROFILE_FIELD_PLACEMENT_START_DATES]."'
					,itinery = '".$p[PROFILE_FIELD_PLACEMENT_ITINERY]."'
					,price = '".$p[PROFILE_FIELD_PLACEMENT_TOUR_PRICE]."'
					,requirements = '".$p[PROFILE_FIELD_PLACEMENT_TOUR_REQUIREMENTS]."'
					,duration_from_id = ".$p[PROFILE_FIELD_PLACEMENT_TOUR_DURATION_FROM]."
					,duration_to_id = ".$p[PROFILE_FIELD_PLACEMENT_TOUR_DURATION_TO]."
					,price_from_id = ".$p[PROFILE_FIELD_PLACEMENT_TOUR_PRICE_FROM]."					
					,price_to_id = ".$p[PROFILE_FIELD_PLACEMENT_TOUR_PRICE_TO]."
					,currency_id = ".$p[PROFILE_FIELD_PLACEMENT_TOUR_CURRENCY]."
					,group_size_id = ".$p[PROFILE_FIELD_PLACEMENT_GROUP_SIZE]."
					WHERE p_hdr_id = ".$p['id']."
				";
			
		$db->query($sql);

		if ($db->getAffectedRows() != 1) {
			Logger::DB(1,get_class($this)."::".__FUNCTION__."()",$sql);
			return false;
		}

		$this->UpdateRefData($p);
		
		return true;
	}

	private function UpdateRefdata($p) {
						
		/* update refdata (multiple select) - travel/transport, accomodation, meals */
		$aId = Mapping::GetIdByKey($p,REFDATA_TRAVEL_TRANSPORT_PREFIX);
		Mapping::UpdateRefData("refdata_map",PROFILE_PLACEMENT,$p['id'],REFDATA_TRAVEL_TRANSPORT, $aId);
		
		$aId = Mapping::GetIdByKey($p,REFDATA_ACCOMODATION_PREFIX);
		Mapping::UpdateRefData("refdata_map",PROFILE_PLACEMENT,$p['id'],REFDATA_ACCOMODATION, $aId);
		
		$aId = Mapping::GetIdByKey($p,REFDATA_MEALS_PREFIX);
		Mapping::UpdateRefData("refdata_map",PROFILE_PLACEMENT,$p['id'],REFDATA_MEALS, $aId);
		
	}
	

	public function GetCode() {
		return $this->code;
	}


	public function GetDates() {
		return $this->dates;
	}

        public function GetItinery() {
                if ($this->GetLastUpdatedAsTs() < strtotime(CK_EDITOR_PROFILE_INTRO_DT) ) {
			return  nl2br($this->itinery);
		}
                return $this->itinery;
        }

	public function GetPrice() {
		return $this->tour_price;
	}

	
	public function GetRequirements() {
		return $this->tour_requirements;
	}

	public function GetDurationFromId() {
		return $this->duration_from_id;
	}

	public function GetDurationToId() {
		return $this->duration_to_id;
	}

	public function GetDurationFromLabel() {
		return $this->GetDurationRefdataObject()->GetValueById($this->duration_from_id);
	}

	public function GetDurationToLabel() {
		return $this->GetDurationRefdataObject()->GetValueById($this->duration_to_id);
	}
	
	public function GetPriceFromId() {
		return $this->price_from_id;
	}

	public function GetPriceToId() {
		return $this->price_to_id;
	}
	
	public function GetCurrencyId() {
		return $this->currency_id;
	}

	public function GetCurrencyLabel() {
		return $this->GetCurrencyRefdataObject()->GetValueById($this->currency_id);
	}

	public function GetPriceFromLabel() {
		return $this->GetCostsRefdataObject()->GetValueById($this->price_from_id);
	}

	public function GetPriceToLabel() {
		return $this->GetCostsRefdataObject()->GetValueById($this->price_to_id);
	}
	
	
	public function GetGroupSizeId() {
		return $this->group_size_id;
	}
	
	private function SetTransportIdList() {
		
		$result = Refdata::Get(REFDATA_TRAVEL_TRANSPORT, PROFILE_PLACEMENT, $this->GetId(), $labels = TRUE);
		
		$this->transport_id_list = array_keys($result);
		$this->transport_labels = array_values($result); 
	}
	
	public function GetTransportLabels() {
		return $this->transport_labels;
	}
		
	public function GetTransportIdList() {
		return $this->transport_id_list;
	}
	
	private function SetMealsIdList() { 
		$result = Refdata::Get(REFDATA_MEALS, PROFILE_PLACEMENT, $this->GetId(), $labels = TRUE);
		$this->meals_id_list  = array_keys($result);
		$this->meals_labels  = array_values($result);
	}
	
	public function GetMealsIdList() {
		return $this->meals_id_list;	
	}
	
	public function GetMealsLabels() {
		return $this->meals_labels;
	}

	public function SetAccomodationIdList() {
		
		$result = Refdata::Get(REFDATA_ACCOMODATION, PROFILE_PLACEMENT, $this->GetId(), $labels = TRUE);
		
		$this->accomodation_id_list = array_keys($result); 
		$this->accomodation_labels = array_values($result);
	}
	
	public function GetAccomodationIdList() {
		return $this->accomodation_id_list;
	}
	
	public function GetAccomodationLabels() {
		return $this->accomodation_labels;	
	}
	
}


?>
