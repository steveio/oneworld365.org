<?php




class ListingOption {

	public static function GetAll($iSiteId,$currency = 'GBP',$from = 0, $to = 2) {
	
		global $db;
		
		$db->query("SELECT  l.*
							,r.* 
						FROM 
							listing_option l
							,listing_rate r 
						WHERE
							l.site_id = ".$iSiteId."
							AND l.type >= ".$from."
							AND l.type <= ".$to." 
							AND l.id = r.listing_id 
							AND r.currency = '".$currency."'
						ORDER BY type ASC;
					");
		
		$aTmp = $db->GetRows();
		
		foreach($aTmp as $aRow) {
			$aListingOption[$aRow['code']] = $aRow; 
		}
		
		return $aListingOption;
	}
	
	public static function GetByCode($sCode,$currency = 'GBP') {

		global $db,$_CONFIG;
		
		$db->query("SELECT  l.*
							,r.* 
						FROM 
							listing_option l
							,listing_rate r 
						WHERE
							l.site_id = ".$_CONFIG['site_id']."
							AND l.code = '".$sCode."'
							AND l.id = r.listing_id 
							AND r.currency = '".$currency."'
						ORDER BY type ASC;
					");
		
		if ($db->getNumRows() == 1) {
			return $aRow = $db->GetRow(PGSQL_ASSOC);
		}
	}
}


class Listing {

	private $company_id;
	private $listing_id;
	private $code;
	private $type;
	private $label;
	private $start_date;
	private $end_date;
	private $detail; /* holds either listing duration or profile credit quantity */
	private $price;
	private $currency;
	private $active;
	private $expiredFl;
	private $added;

	public function __Construct() {
	
		$this->SetStartDate("now()::timestamp");
		$this->SetActive("T");
		$this->SetAddedDate("now()::timestamp");
	}
	
	public function GetCompanyId() {
		return $this->company_id;
	}
	
	public function SetCompanyId($id) {
		$this->company_id = $id;
	}

	public function GetCode() {
		return $this->code;
	}

	public function GetType() {
		return $this->type;
	}	

	public function SetType($sType) {
		$this->type = $sType;
	}	
	
	public function GetLabel() {
		return $this->label;
	}	
	
	public function GetListingId() {
		return $this->listing_id;
	}

	public function SetListingId($id) {
		$this->listing_id = $id;
	}	
	
	public function GetStartDate() {
		return $this->start_date;
	}
	
	public function SetStartDate($sStartDate) {
		$this->start_date = $sStartDate;
	}
		
	public function GetAddedDate() {
		return $this->added;
	}
	
	public function SetAddedDate($sDate) {
		$this->added = $sDate;
	}

	public function GetDuration() {
		return (int) $this->detail;
	}
	
	public function SetDuration($iDuration) {
		$this->detail = $iDuration;
	}	
	
	public function GetPrice() {
		return $this->price;
	}
	
	public function SetPrice($nPrice) {
		$this->price = $nPrice;
	}	

	public function GetCurrency() {
		return $this->currency;
	}	
	
	public function GetActive() {
		return $this->active;
	}

	public function SetActive($bActive) {
		$this->active = $bActive;
	}
	
	public function GetEndDate() {
		return $this->end_date;
	}

	public function SetEndDate() {
	
		$sStartTs =  strtotime($this->GetStartDate());
		$sEndTs = strtotime("+".$this->GetDuration()." days",$sStartTs);
		$this->end_date = date("d-m-Y",$sEndTs);		
	}
	
	public function Expired() {
		return $this->expiredFl;
	}
	
	public function SetExpired() {
		
		$sNowTs =  strtotime("now");
		$sEndTs = strtotime($this->GetEndDate());
		$this->expiredFl = ($sNowTs > $sEndTs) ? true : false;
			
	}
	
	/*
	 * Checks to see if a listing expires within a given number of days
	 */
	public function ExpiresWithin($iDays = 30) {
	
		if ($this->GetEndDate() == null) return false;
		
		$sNowTs =  strtotime("now");
		//print date("d-m-Y",$sNowTs);
		$sEndTs = strtotime("-".$iDays." days",strtotime($this->GetEndDate()));
		//print date("d-m-Y",$sEndTs);
		
		return ($sNowTs > $sEndTs) ? true : false;
		
	}
	
	public function GetCurrentByCompanyId($id) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
		global $db;
		
		$db->query("SELECT 
					id
					,company_id
					,listing_id
					,code
					,type
					,label
					,to_char(start_date,'DD-MM-YYYY') as start_date
					,duration as detail
					,price
					,currency
					,active
					,to_char(added,'DD-MM-YYYY') as added		
					FROM listing 
					WHERE company_id = ".$id." 
					AND active = 'T'");
		
		if ($db->getNumRows() == 1) {
			$this->SetFromArray($db->getRow(PGSQL_ASSOC));
			$this->SetEndDate(); /* must be called before SetExpired() */
			$this->SetExpired();
			return true;
		}
	
	}
	
	
	/*
	 * A company record can have n listing records
	 * This function sets status flag on all existing records to enable identification of current listing 
	 */
	public function SetActiveByCompanyId($iCompanyId,$bActive = 'F') {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
		global $db;
		
		if (!is_numeric($iCompanyId)) return false;
		
		$db->query("UPDATE listing SET active = '".$bActive."' WHERE company_id = ".$iCompanyId);
	
	}
		
	public function Add() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
		global $db;
		
		$sStartDate = ($this->GetStartDate() == "now()::timestamp") ? $this->GetStartDate() : "'".$this->GetStartDate()."'";   
	
		$sql = "INSERT INTO listing (	id
										,company_id
										,listing_id
										,code
										,type
										,label
										,start_date
										,duration
										,price
										,currency
										,active
										,added
										) VALUES (
										nextval('listing_seq')
										,".$this->GetCompanyId()."
										,".$this->GetListingId()."
										,'".$this->GetCode()."'
										,".$this->GetType()."
										,'".$this->GetLabel()."'
										,".$sStartDate."
										,".$this->GetDuration()."
										,".$this->GetPrice()."
										,'".$this->GetCurrency()."'
										,'".$this->GetActive()."'
										,".$this->GetAddedDate()."
										);";		

		$db->query($sql);

		if ($db->getAffectedRows() == 1) {
			return true;
		} else {
			Logger::DB(1,get_class($this)."::".__FUNCTION__."()",$sql);		
		}

	}

	/*
	 * Update profile quota - creating a history record for each update
	 * 	called by ProfileCompany::Update() (admin only) and from account.php (user quota purchase)
	 * 
	 * @param id profile_quota_history primary key - must be generate by caller (to prevent duplicate purchase)
	 * @param id company id
	 * @param string product code for profile quota option purchased
	 * 
	 */
	public static function UpdateProfileQuota($id,$iCompanyId,$sProdCode) {

		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."()");
	
		global $db;
		
		/*
		 * Check that we haven't already processed this transaction
		 * 
		 */
		if (!is_numeric($id)) return false;
		$db->query("SELECT id FROM profile_quota_history WHERE id = ".$id);
		if ($db->getNumRows() >= 1) return false;
		
		
		$aAllProduct = ListingOption::GetAll($currency = 'GBP',$from = 101, $to = 101);
		

		/*
		 * First, update company record with new profile (job_credit) quota
		 *  
		 */
		$iExistingQuota = $db->getFirstCell("SELECT job_credits FROM COMPANY WHERE id = ".$iCompanyId);
					
		$iNewQuota = ($iExistingQuota + $aAllProduct[$sProdCode]['detail']);  
		
		if (DEBUG) Logger::Msg("Quantity Purchased : ".$aAllProduct[$sProdCode]['detail']);
		if (DEBUG) Logger::Msg("Existing Quota : ".$iExistingQuota);
		if (DEBUG) Logger::Msg("New Quota : ".$iNewQuota);
		
		/* update the company record listing field (prod_type) */
		$db->query("UPDATE COMPANY SET job_credits = ".$iNewQuota." WHERE id = ".$iCompanyId);
	
		
		/*
		 * Secondly, add a history record to track/audit profile quota changes over time
		 * 
		 */
		$sql = "INSERT INTO profile_quota_history (	
										id
										,company_id
										,old_quota
										,listing_id
										,code
										,type
										,label
										,quantity
										,price
										,currency
										,added
								) VALUES (
										".$id."
										,".$iCompanyId."
										,".$iExistingQuota."
										,".$aAllProduct[$sProdCode]['id']."
										,'".$aAllProduct[$sProdCode]['code']."'
										,'".$aAllProduct[$sProdCode]['type']."'
										,'".$aAllProduct[$sProdCode]['label']."'
										,".$aAllProduct[$sProdCode]['detail']."
										,".$aAllProduct[$sProdCode]['price']."
										,'".$aAllProduct[$sProdCode]['currency']."'
										,now()::timestamp
										);";
		
		$db->query($sql);

		if ($db->getAffectedRows() == 1) {
			return true;
		} else {
			Logger::DB(1,get_class($this)."::".__FUNCTION__."()",$sql);		
		}

	}
	

	public function SetFromArray($a) {
		foreach($a as $k => $v) {
			$this->$k = (is_string($v)) ? stripslashes($v) : $v;
		}
	}
	
}

?>