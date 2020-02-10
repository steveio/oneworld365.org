<?php

/*
 * SOLR Search for both Company & Placement Profiles
 * 
 * Extends SolrSearch to provide  
 * result set processing - balanced distributor 
 * is invoked on returned ID's to fetch an even  
 * spread of profiles across range of advertisers
 * 
 * 
 */


class SolrCombinedProfileSearch extends SolrSearch {
	
    protected $_aBalancedPlacementId;
    protected $_aProfile;
    
	public function __construct($solr_config) {
		
		parent::__construct($solr_config);
		
	}

	public function processResult() 
	{

		$aResult = array();
		
		if ($this->getNumFound() >= 1) {
			$this->resultset = $this->getResultSet();
			
			$this->_balancePlacementDistribution();

			$aProfileId = $this->_getBalancedPlacementId();			
			$aCompanyId = $this->_getId(PROFILE_COMPANY);
			$aPlacement = array();
			$aCompany = array();

			if (is_array($aProfileId) && count($aProfileId) >= 1)
				$aPlacement = PlacementProfile::Get("ID_LIST_SEARCH_RESULT",$aProfileId);			
				
			if (is_array($aCompanyId) && count($aCompanyId) >= 1)
				$aCompany = CompanyProfile::Get("ID_SORTED",$aCompanyId);

			$aCombinedProfile = $aPlacement + $aCompany;
			$this->_aProfile = array();

			$idx = 0;
			foreach($this->resultset as $doc)
			{
			    if (array_key_exists($doc->profile_id,$aCombinedProfile))
			    {
			        $this->_aProfile[$idx] = $aCombinedProfile[$doc->profile_id];
			        $this->aId[$idx] = $doc->profile_id;
			        $idx++;
			    }
			    if ($idx == $this->getRows()) break;
			}

			$this->setFacetFieldResult();
			$this->setFacetQueryResult();

		} // end if projects found					
 		
	}

	protected function _balancePlacementDistribution()
	{
	    $aResult = array();
	    
	    foreach($this->resultset as $doc) {
	        if ($doc->profile_type == PROFILE_PLACEMENT)
	           $aResult[$doc->company_id][] = $doc->profile_id;
	    }

	    // reindex the array so placement keys for each company are a sequential numeric index
	    $aIdIndexedNumeric = array();
	    $i = 0;
	    $aId = array();
	    foreach($aResult as $company_id => $aPlacementId) {
	        $aId[$i++] = $aPlacementId;
	    }
	    
	    $oBalancedDistributor = new BalancedDistributor($aId);
	    $oBalancedDistributor->SetFetchSize(count($aId));
	    $oBalancedDistributor->SetStartIdx(0);
	    
	    $this->_aBalancedPlacementId = $oBalancedDistributor->Fetch($this->getRows());
	    
	}

	protected function _getBalancedPlacementId()
	{
	    return $this->_aBalancedPlacementId;
	}

	/**
	 * Return Profiles from result set according to specified type (PROFILE_PLACEMENT, PROFILE_COMPANY)
	 * @param constant int $type
	 * @return array result index => profile id
	 */
	protected function _getId($type)
	{
	    $arrId = array();

	    foreach($this->resultset as $idx => $doc) 
	    {
	        if ($doc->profile_type == $type)
	            $arrId[$idx] = $doc->profile_id;
	    }
	    
	    return $arrId;
	}

	public function getProfile()
	{
	    return $this->_aProfile;
	}	
	
}
