<?php



define("LINK_TO_COMPANY",0);
define("LINK_TO_PLACEMENT",1);
define("LINK_TO_ARTICLE",2);


class Review {

	private $id;
	private $link_to;
	private $link_id;
	private $type; // review or comment
	private $name;
	private $email;
	
	private $age;
	private $gender;
	private $nationality;
	private $title;
	private $review;
	private $rating;
	
	private $ip_addr;
	private $date;
	private $status;

	private $iTotalReviews;

	public function __Construct() {

	}

	public function GetId() {
		return $this->id;
	}

	public function SetId($id) {
		$this->id = $id;
	}

	public function GetLinkId() {
		return $this->link_id;
	}

	public function SetLinkId($iLinkId) {
		$this->link_id = $iLinkId;
	}

	public function GetLinkTo() {
		return $this->link_to;
	}

	public function GetType() {
		return isset($this->type) ? $this->type : 'review';
	}

	public function SetLinkTo($sLinkTo) {
		$this->link_to = $sLinkTo;
	}


	public function GetName() {
		return $this->name;
	}

	public function SetName($name) {
		$this->name = $name;
	}
	

	public function GetEmail() {
		return $this->email;
	}

	public function GetAge() {
		return is_numeric($this->age) ? $this->age : 0;
	}
	
	public function GetGender() {
		return $this->gender;
	}
	
	public function GetNationality() {
		return $this->nationality;
	}

	public function GetTitle() {
		return $this->title;
	}

	public function GetReview() {
		return $this->review;
	}
	
	public function GetRating() {
		return is_numeric($this->rating) ? $this->rating : -1; // comments do not submit rating
	}

	public function GetIpAddr() {
		return $this->ip_addr;
	}

	public function GetDate() {
		return $this->date;
	}

	public function GetTotalReviews()
	{
	    return $this->iTotalReviews;
	}
	
	public function SetStatus($id,$iStatus) {

		global $db;

		if ((!is_numeric($id)) ||(!is_numeric($iStatus))) return false;

		$db->query("UPDATE review SET status = ".$iStatus ." WHERE id = ".$id);
	}

	public function GetStatusLabel() {
		switch($this->status) {
			case 0 :
				return "Pending";
				break;
			case 1 :
				return "Approved";
				break;
			case 2 :
				return "Rejected";
				break;
		}
	}


	public function GetByStatus($iStatus) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		if (!is_numeric($iStatus)) return false;

		$db->query("SELECT id FROM review WHERE status = ".$iStatus);

		if ($db->getNumRows() >= 1) {
			$aTmp = $db->getRows();
			$aId = array();
			foreach($aTmp as $k => $v) {
				$aId[] = $v['id'];
			}
			return $aId;
		}
	}

	public function Get($link_id,$link_to, $status = 1,$iPageSize = 30, $iStart = 0) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;
		
		if (is_numeric($link_id))
			$linkIdConstraint = " AND link_id = ".$link_id;

		if (strlen($link_to) >= 1)
			$linkToConstraint = " AND link_to = '".$link_to."'";

		if (is_numeric($status))
			$statusConstraint = " AND status = ".$status;
		
		$sql = "SELECT
					r.*
				FROM review r
				WHERE 1=1 
				".$linkIdConstraint."
				".$linkToConstraint."
				".$statusConstraint."
                 ORDER BY date desc";

		$db->query($sql);

		$this->iTotalReviews = $db->getNumRows();

		
		if ($db->getNumRows() >= 1) {
			$aRows = $db->getRows(PGSQL_ASSOC);
			$aResult = array();
			$i = 0;

			foreach($aRows as $aRow)
			{
                if ($i<$iStart || $i > $iStart + $iPageSize)
                {
                    $i++;
                    continue;
                }
                $i++;
                    
				foreach($aRow as $k => $v) {
					$a[$k] = is_string($v) ? stripslashes($v) : $v;
				}
				$oReview = new Review();
				$oReview->SetFromArray($a);
				$aResult[] = $oReview;
			}
			return $aResult;
		} else {
			return false;
		}
	}


	
	public function GetNextId() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		$this->SetId($db->getFirstCell("SELECT nextval('review_seq')"));

		return $this->GetId();

	}

	public function Validate(&$aResponse)
	{
		$aError = array();
		if (strlen($this->GetName()) < 1 || strlen($this->GetName()) > 45)
			$aError['name'] = "Please enter your name (upto 45 chars)";

		if ((strlen($this->GetEmail()) < 1)  || (!Validation::IsValidEmail($this->GetEmail()) ))
			$aError['email'] = "Please enter a valid email address";

		if ($this->GetType() == "review")
		{
			if ((!is_numeric($this->GetAge())))
				$aError['age'] = "Please enter your age";

			if ((!in_array($this->GetGender(), array("M","F"))))
				$aError['gender'] = "Please enter your gender";

            if ((!is_numeric($this->GetRating())))
	                $aError['rating'] = "Please enter a rating";

            if (strlen($this->GetNationality()) < 1 || strlen($this->GetNationality()) > 32)
                $aError['nationality'] = "Please enter your nationality (upto 32 chars)";

            if (strlen($this->GetTitle()) < 1 || strlen($this->GetTitle()) > 128)
                $aError['review'] = "Please enter a review title (upto 128 chars)";
                    
		}
		
		if (strlen($this->GetReview()) < 1)
			$aError['review'] = "Please enter your review";


		if (count($aError) >= 1) {
			$aResponse['error'] = "<ul>";
			foreach($aError as $k => $v)
			{
				$aResponse['error'] .= "<li>".$v."</li>";
			}
			$aResponse['error'] .= "</ul>";
			return false;
		}

		return true;
	} 

	public function Add(&$aResponse) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db,$_CONFIG;

		$ip = IPAddress::GetVisitorIP();

		$sql = "INSERT INTO review (
					id
					,link_to
					,link_id
					,name
					,email
    				,age
    				,gender
					,nationality
					,title
    				,review
    				,rating
					,ip_addr
					,date
					,status
				) VALUES (
					".$this->GetNextId()."
					,'".$this->GetLinkTo()."'
					,".$this->GetLinkId()."
					,'".$this->GetName()."'
					,'".$this->GetEmail()."'
					,".$this->GetAge()."
					,'".$this->GetGender()."'
					,'".$this->GetNationality()."'
					,'".$this->GetTitle()."'
					,'".$this->GetReview()."'
					,".$this->GetRating()."
					,'".$ip."'
					,now()::timestamp
					,0
				)";


		if (!$db->query($sql)) {
			return false;
		} else {
			return true;
		}

	}

	public function SetFromArray($a, $bStripSlashes = true) {
		foreach($a as $k => $v) {
			if ($bStripSlashes) {
				$this->$k = (is_string($v)) ? stripslashes($v) : $v;
			} else {
				$this->$k = $v;
			}
		}
	}



}


?>
