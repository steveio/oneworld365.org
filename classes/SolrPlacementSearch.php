<?php

/*
 * SOLR Search for Placements
 * 
 * Extends SolrSearch to provide placement specific 
 * result set processing - balanced distributor 
 * is invoked on returned ID's to fetch an even  
 * number of placements across range of advertisers
 * 
 * 
 */


class SolrPlacementSearch extends SolrSearch {
	
	public function __construct($solr_config) {
		
		parent::__construct($solr_config);
		
	}

	public function processResult() {
				
		
		$aResult = array();
		
		
		if ($this->getNumFound() >= 1) {
			$this->resultset = $this->getResultSet();
			
			foreach($this->resultset as $doc) {				
				$aResult[$doc->company_id][] = $doc->profile_id;
			}
			
			// reindex the array so placement keys for each company are a sequential numeric index
			$aIdIndexedNumeric = array();
			$i = 0;
			foreach($aResult as $company_id => $aPlacementId) {
				$aId[$i++] = $aPlacementId;
			}				
				
			
			$oBalancedDistributor = new BalancedDistributor($aId);
			$oBalancedDistributor->SetFetchSize($this->getRows());
			$oBalancedDistributor->SetStartIdx(0);
			$iTotalResults = $oBalancedDistributor->GetTotalElements();				
			
			$this->aId = $oBalancedDistributor->Fetch($this->getRows());

			
				
			
			$this->setFacetFieldResult();

			$this->setFacetQueryResult();

		} // end if projects found					
	}
	
	

	
}