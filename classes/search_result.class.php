<?php



class SearchResult {
	
	private $aRequest; 
	private $aSearch; /* array of search objects representing completed searches */

	private $iLimit; /* number of results to return */
	

	public function __Construct($aRequest) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $_CONFIG;
		
		$this->SetRequest($aRequest);
		$this->SetLimit($_CONFIG['results_per_page']);
		
	}
		

	public function GetSearch($sType) {
		
		if (is_object($this->aSearch[$sType])) return $this->aSearch[$sType];

		return array();
	}
	
	
	private function SetSearch($oSearch) {
		$this->aSearch[$oSearch->GetType()] = $oSearch;
	}
	
	public function SetLimit($iLimit) {
		$this->iLimit = $iLimit;
	}
	
	public function GetLimit() {
		return $this->iLimit;
	}
	
	public function CompanySearch() {

		global $_CONFIG;
		
		/* get company results */
		$oSearch = new CompanySearch();		
		
		$aParams = array(
						"Query" => $this->GetRequest('cat_name'),
						"SubQuery" => $this->GetRequest('sub_cat'),
						"Limit" => $this->GetLimit(),
						"PagerKey" => "P1", 
						"Offset" => $this->GetRequest('P1')
						);


		$oSearch->SetUp($aParams);
		$oSearch->Run();
		
		$this->SetSearch($oSearch);		

	}

	public function PlacementSearch() {

		global $_CONFIG;
		
		/* get company results */
		$oSearch = new PlacementSearch();		
		
		$aParams = array(
						"Query" => $this->GetRequest('cat_name'),
						"SubQuery" => $this->GetRequest('sub_cat'),
						"Limit" => $this->GetLimit(),
						"PagerKey" => "P2", 
						"Offset" => $this->GetRequest('P2')
						);

		$oSearch->SetUp($aParams);
		$oSearch->Run();
		
		$this->SetSearch($oSearch);		

	}
	
	
	/*
	 * Get / Set $_REQUEST params
	 */	
	public function SetRequest($aRequest) {
		
		$this->aRequest = $aRequest;
	}
	
	public function GetRequest($key = null) {
		
		if ($key != null) {
			return $this->aRequest[$key]; 
		} else {
			return $this->aRequest;
		}
	}
	
	public function GetSearchQueryAsText() {
		$sQuery = preg_replace("/-/"," ",$this->GetRequest('cat_name')). preg_replace("/-/"," ",$this->GetRequest('sub_cat'));
		$sQuery = trim($sQuery);
		$a = explode(" ",$sQuery);
		for($i=0;$i<count($a);$i++) {
			$s .= ucfirst($a[$i])." ";
		}
		return $s = trim($s);
	}
	
	
}





/*
 * Wrapper Classes for Search API to handle specialised search types
 * 		eg.  	Company Profile Search
 * 				Placement Profile Search
 *  
 */

abstract class AbstractSearch {
	
	protected $aResult; /* array collection of profile objects representing a result set */
	protected $oPager; /* an instance of a pager object */
	protected $sSearchType; /* a label representing the type of search */ 
	
	protected $sQuery; /* string primary search query eg /country/<query> */
	protected $sSubQuery; /* string secondary search query eg /country/brazil/<sub-query> */
	protected $iResultCount; /* int total no matching results */
	protected $iLimit; /* int max no results to return */ 
	protected $iOffset; /* int result offset */
	protected $sPagerKey; /* string pager unique identifier */
	protected $sSubCatSQL; /* string optional extra search SQL filter constraint criterea */
	
	
	public function __Construct() {
		
		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."()");
		
		$this->aResult = array();
		$this->oPager = null;
		$this->sSearchType ="";
		$this->iResultCount = 0;
		$this->sQuery = "";
		$this->iLimit = 0;
		$this->iOffset = 0;
		$this->sPagerKey = "";
		$this->sSubCatSQL ="";
		
	}

	
	public function SetUp($aParams = array()) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		foreach($aParams as $k => $v) {
			$m = "Set".$k;	
			$this->$m($v);
		}
	}
	
	/*
	 * Execute a search
	 * 
	 */
	abstract protected function Run();

	/*
	 * Return a label for the type of search
	 * 
	 */
	public function GetType() {
		return $this->sSearchType;
	}
	
	protected function SetType($sType) {
		$this->sSearchType = $sType;
	}
	
	/*
	 * Setup Additional Search Params
	 *  
	 */
	
	public function GetResultCount() {
		return $this->iResultCount;
	}

	protected function SetResultCount($i) {
		
		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."() ".$i);
		
		$this->iResultCount = $i;
	}
	
	protected function GetLimit() {
		return $this->iLimit;
	}
	
	protected function SetLimit($i) {
		
		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."() ".$i);
		
		$this->iLimit = $i;
	}

	protected function SetPagerKey($sKey) {
		
		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."() ".$sKey);
		
		$this->sPagerKey = $sKey;
	}
	
	public function GetPagerKey() {
		return $this->sPagerKey;
	}
	
	
	protected function SetOffSet($i) {
		
		if (DEBUG) Logger::Msg(get_class()."::".__FUNCTION__."() ".$i);
		
		$this->iOffset = is_numeric($i) ? (($i -1) * $this->GetLimit()) : 0;
	}

	protected function GetOffSet() {
		return $this->iOffset;
	}
	
	protected function SetSubCatSQL($sSQL) {
		$this->sSubCatSQL = $sSQL;
	}
	
	protected function GetSubCatSQL() {
		return $this->sSubCatSQL;
	}
	
	/*
	 * Set & process keyword search query (eg.  "administration and accounts", "africa")
	 *
	 */
	protected function SetQuery($sQuery) {
						
		$this->sQuery = Search::ProcessUri("//".$sQuery);
	}
	
	protected function GetQuery() {
		return $this->sQuery;
	}
	
	protected function SetSubQuery($s) {
		$this->sSubQuery = $s;
	}
	
	protected function GetSubQuery() {
		return $this->sSubQuery;
	}
	
	public function SetResult($a) {
		if (is_array($a)) {
			$this->aResult = $a;
		}
	}
	
	public function GetResult() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		return $this->aResult;
	}
	
	public function GetPager() {
		return $this->oPager;
	}
	
	protected function SetPager($oPager) {
		$this->oPager = $oPager;
	}
	
}


/*
 * Concrete Search Class : Company Profile Search 
 * 
 */

class CompanySearch extends AbstractSearch {

	public function __Construct() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		parent::__Construct();
		
		$this->SetType("COMPANY");
		
	}
	

	public function Run() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $_CONFIG;

		$iResultCount = 0;
		
		$aId = array();
		$aId = Search::KeywordSearch($this->GetQuery(),$this->GetLimit(),$this->GetOffSet(),$iResultCount,$mode = "INTERSECT",$this->GetSubCatSQL());
		
		$this->SetResultCount($iResultCount);
				
		if (is_array($aId) && count($aId) >= 1) {
			$this->SetResult(CompanyProfile::Get("ID_SORTED",$aId));
		}
	
		if ($this->GetResultCount() > $this->GetLimit()) {
			$oPager = new PagedResultSet();
			$oPager->GetByCount($this->GetResultCount(),$this->GetPagerKey());
			$this->SetPager($oPager); 
		}
		
	}
	

	/*
	 * Allows passing of additional SQL where clause criterea to the search API
	 * 
	 */
	protected function GetSubCatSQL() { 
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $_CONFIG;
		
		switch($this->GetSubQuery()) {
			case "volunteer" :
				/* get results for VOLUNTEER */
				return $sSubCatSQL = $_CONFIG['company_table']." c, comp_cat_map m WHERE s.id = c.id AND c.id = m.company_id AND m.category_id = 0 ";
				break;
			case "work" :
				/* get results for WORK */
				return $sSubCatSQL = $_CONFIG['company_table']." c, comp_cat_map m WHERE s.id = c.id AND c.id = m.company_id AND m.category_id = 1 ";		
				break;
			case "travel-tour" :
				/* get results for TRAVEL / TOUR */
				return $sSubCatSQL = $_CONFIG['company_table']." c, comp_cat_map m WHERE s.id = c.id AND c.id = m.company_id AND m.category_id = 2 ";
				break;
			case "teach" :
				/* get results for TEACHING */
				return $sSubCatSQL = $_CONFIG['company_table']." c, comp_cat_map m WHERE s.id = c.id AND c.id = m.company_id AND m.category_id = 4 ";
				break;
			default : 
				return "DEFAULT"; /* flag search API to use default SQL where clause */
				break;
		}
	}	
}


/*
 * Concrete Search Class : Placement / Project Profile Search 
 * 
 */


class PlacementSearch extends AbstractSearch {
	
	public function __Construct() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		parent::__Construct();
		
		$this->SetType("PLACEMENT");
		
	}
	

	public function Run() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $_CONFIG;

		$iResultCount = 0;

		$aId = array();

		
		$aId = Search::PlacementSearch($this->GetQuery(),$this->GetLimit(),$this->GetOffSet(),$iResultCount,false,$this->GetSubCatSQL(),$sSort= "WEIGHT",$bPageResults = FALSE);

		$oBalancedDistributor = new BalancedDistributor($aId);
		$oBalancedDistributor->SetFetchSize($this->GetLimit());
		$oBalancedDistributor->SetStartIdx($this->GetOffSet());
		$iTotalResults = $oBalancedDistributor->GetTotalElements();
		
		/*
		Logger::Msg($this->GetLimit());
		Logger::Msg($this->GetOffSet());
		Logger::Msg($aId);
		Logger::Msg("TotalResults: ".$iTotalResults);
		*/
		
		$aPlacementId = $oBalancedDistributor->Fetch($this->GetLimit());
		
		if (is_array($aPlacementId) && count($aPlacementId) >= 1) {
			$this->SetResult(PlacementProfile::Get("ID_LIST",$aPlacementId));
		}
				
		$this->SetResultCount($iTotalResults);
				
	
		if ($this->GetResultCount() > $this->GetLimit()) {
			$oPager = new PagedResultSet();
			$oPager->GetByCount($this->GetResultCount(),$this->GetPagerKey());
			$this->SetPager($oPager); 
		}
		
	}
	

	/*
	 * Allows passing of additional SQL where clause criterea to the search API
	 * 
	 */
	protected function GetSubCatSQL() { 
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $_CONFIG;

		switch($this->GetSubQuery()) {
			case "volunteer" :
				/* get results for VOLUNTEER */
				return $sSubCatSQL = $_CONFIG['company_table']." c, prod_cat_map m WHERE s.id = p.id AND p.company_id = c.id AND p.id = m.prod_id AND m.category_id = 0 ";
				break;
			case "work" :
				/* get results for WORK */
				return $sSubCatSQL = $_CONFIG['company_table']." c, prod_cat_map m WHERE s.id = p.id AND p.company_id = c.id AND p.id = m.prod_id AND m.category_id = 1 ";		
				break;
			case "travel-tour" :
				/* get results for TRAVEL / TOUR */
				return $sSubCatSQL = $_CONFIG['company_table']." c, prod_cat_map m WHERE s.id = p.id AND p.company_id = c.id AND p.id = m.prod_id AND m.category_id = 2 ";
				break;
			case "teach" :
				/* get results for TEACHING */
				return $sSubCatSQL = $_CONFIG['company_table']." c, prod_cat_map m WHERE s.id = p.id AND p.company_id = c.id AND p.id = m.prod_id AND m.category_id = 4 ";
				break;
			default : 
				return "DEFAULT"; /* flag search API to use default SQL where clause */
				break;
		}
		
	}	
	
}





?>