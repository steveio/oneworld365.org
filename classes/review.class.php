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
	
	public function GetStatus()
	{
	    return $this->status;
	}
	
	public function SetStatus($id,$iStatus) {

		global $db;

		if ((!is_numeric($id)) ||(!is_numeric($iStatus))) return false;

		$db->query("UPDATE review SET status = ".$iStatus ." WHERE id = ".$id);
		
		if ($db->getAffectedRows() == 1) return true;
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

	public function GetById($id) {

	    global $db;
	    
	    if (!is_numeric($id)) return false;

	    $sql = $sql = "SELECT
        					r.*
        				FROM review r
        				WHERE id = ".$id."  
                        ";

	    $db->query($sql);

	    $aRow = $db->getRow();

	    foreach($aRow as $k => $v) {
	        $a[$k] = is_string($v) ? stripslashes($v) : $v;
	    }

	    $this->SetFromArray($a);
	    
	    return true;
	}

	public function Get($link_id,$link_to, $status = 1) {

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
			
			foreach($aRows as $aRow)
			{                    
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

	public function GetReviewRating($link_id,$link_to, $status = 1) {
	    
	    global $db;

	    if (!is_numeric($link_id)) return false;

	    $linkIdConstraint = " AND link_id = ".$link_id;
	        
        if (strlen($link_to) < 1) return false;

        $linkToConstraint = " AND link_to = '".$link_to."'";
            
        if (!is_numeric($status)) return false;

        $statusConstraint = " AND status = ".$status;

        $sql = "SELECT
        	    link_id
        	    ,count(*)
        	    ,FLOOR(AVG(r.rating)) as rating
        	    FROM review r
        	    WHERE 1=1
				".$linkIdConstraint."
				".$linkToConstraint."
				".$statusConstraint."
        	    GROUP BY link_id";

        $db->query($sql);

        $aResult = array();

        if ($db->getNumRows() == 1)
            $aResult = $db->getFirstRow($sql);

        return $aResult;
	}

	public function GetReport($aOptions)
	{

	    global $db;

	    if ($aOptions['report_date_from'] != null) {
	        $strStartDateSQL = " and r.date > '".$aOptions['report_date_from']."'";
	    }

	    if ($aOptions['report_date_to'] != null) {
	        $strEndDateSQL = " and r.date < '".$aOptions['report_date_to']."'";
	    }
	    
	    if (isset($aOptions['report_status'])) {
	        $strStatusSQL = " and r.status = ".$aOptions['report_status'];
	    }
	    
	    $sql = "
        select * from 
        (
    	    select
            r.link_to ||'<br />'||a.title||'<br />'||'<a href=\"http://www.oneworld365.org'||m.section_uri||'\">'||m.section_uri||'</a>' as posted_to 
    	    ,r.id as post_id
    	    ,r.title ||'<br />'||r.review as post_details
            ,r.name ||'<br />'||r.email||'<br />'||r.ip_addr as posted_by
    	    ,r.date::date as post_date
            ,case 
                when r.status = 0 then 'PENDING'
                when r.status = 1 then 'APPROVED'
                when r.status = 2 then 'REJECTED'
            end as post_status 
    	    from
    	    review r
    	    ,article a
    	    ,article_map m
    	    where
            1=1
    	    and r.link_to = 'ARTICLE'
    	    and r.link_id = a.id
    	    and a.id = m.article_id
            ".$strStartDateSQL."
            ".$strEndDateSQL."
            ".$strStatusSQL."
    	    UNION
    	    select
            r.link_to ||'<br />'||c.title||'<br /><a href=\"http://www.oneworld365.org/company/'||c.url_name||'\" target=_new>/company/'||c.url_name||'</a>' as posted_to 
    	    ,r.id as post_id
    	    ,r.title||'<br />'||r.review as post_details
            ,r.name ||'<br />'||r.email||'<br />'||r.ip_addr as posted_by
            ,r.date::date as post_date
            ,case 
                when r.status = 0 then 'PENDING'
                when r.status = 1 then 'APPROVED'
                when r.status = 2 then 'REJECTED'
            end as post_status 
    	    from
    	    review r
    	    ,company c
    	    where
            1=1
    	    and r.link_to = 'COMPANY'
    	    and r.link_id = c.id
            ".$strStartDateSQL."
            ".$strEndDateSQL."
            ".$strStatusSQL."
    	    UNION
    	    select
            r.link_to ||' '||p.title||' '||'/'||c.url_name ||'/'|| p.url_name as posted_to
    	    ,r.id as post_id
    	    ,r.title||'<br />'||r.review as post_text
            ,r.name ||'<br />'||r.email||'<br />'||r.ip_addr as posted_by
            ,r.date::date as post_date
            ,case 
                when r.status = 0 then 'PENDING'
                when r.status = 1 then 'APPROVED'
                when r.status = 2 then 'REJECTED'
            end as post_status 
    	    from
    	    review r
    	    ,profile_hdr p
    	    ,company c
    	    where
            1=1
    	    and r.link_to = 'PLACEMENT'
    	    and r.link_id = p.id
    	    and p.company_id = c.id
            ".$strStartDateSQL."
            ".$strEndDateSQL."
            ".$strStatusSQL."
        ) q1
        order by q1.post_date desc
        ";

	    $db->query($sql);

	    if ($db->getNumRows() >= 1) {
	        $aRows = $db->getRows(PGSQL_ASSOC);
	        
	        $aResult = array();
	        
	        foreach($aRows as $aRow)
	        {
	            foreach($aRow as $k => $v) {
                    $aRow[$k] = (is_string($v)) ? stripslashes($v) : $v;
	            }
	            $aResult[] = $aRow;
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

	public function Update(&$aResponse) {
	    
	    global $db,$_CONFIG;
	    
	    if (!is_numeric($this->GetId())) throw new Exception("Update failed invalid id");

	    $sql = "UPDATE review SET 
					name = '".$this->GetName()."',
					email = '".$this->GetEmail()."',
					nationality = '".$this->GetNationality()."',
					title = '".$this->GetTitle()."',
    				review = '".$this->GetReview()."',
    				rating = ".$this->GetRating().",
					status = ".$this->GetStatus()."
                WHERE id = ".$this->GetId()."
                ";

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


	public function GetLinkedContentDesc()
	{
	    global $db; 
	    
	    $strDetails = '';
	    if ($this->GetLinkTo() == 'COMPANY')
	    {
	        $oCompany = new Company($db);
	        $objCompany = $oCompany->GetById($this->GetLinkId(),"title,url_name");
	        $strDetails = "Company: ".$objCompany->title;
	    } elseif ($this->GetLinkTo() == 'PLACEMENT') {
	        $oProfile = new PlacementProfile();
	        $objProfile = $oProfile->GetProfileById($this->GetLinkId(),$key = "PLACEMENT_ID");
	        $strDetails = "Placement: ".$objProfile->company_name." : ".$objProfile->title;
	    } elseif ($this->GetLinkTo() == 'ARTICLE') {
	        $oArticle = new Article();
	        $oArticle->GetById($this->GetLinkId());
	        $strDetails = "Article: ".$oArticle->GetTitle();
	    }
	    
	    return $strDetails;
	    
	}

}

?>