<?php

/*
 * SOLR Search for Company Profiles
 * 
 * Also used for combined Company & Placement searches
 * 
 * 
 * 
 */


class SolrCompanySearch extends SolrSearch {
	
	public function __construct($solr_config) {
		
		parent::__construct($solr_config);

	}
	

	public function processResult() {
		
		if ($this->getNumFound() >= 1) {
						
			foreach($this->resultset as $doc) {
				$this->aId[] = $doc->profile_id;
				$this->aResultType[$doc->profile_type][] = $doc->profile_id; 
			}
			
			$this->setFacetFieldResult();
	
			$this->setFacetQueryResult();
			
		} // end if projects found					
	}

	public function getId($type = NULL) {
		if ($type == NULL) return $this->aId;
		return $this->aResultType[$type];
	}
	
}