<?php

/*
 * An Object to setup and populate an instance of SOLR 
 * search panel UI widget 
 * 
 * this has ended up looking very similar to SolrSearchQuery
 * which might have been used instead
 */


class SolrSearchPanel {

	private $fq;
	private $aFacetField;
	private $aFacet; // facet data returned from query
	
	public function __construct() {
		$this->fq = array();
		$this->aFacetField = array();
		$this->aFacet = array();
	}
	
	public function setFilterQuery($fq) {
		$this->fq = $fq;
	}
	
	public function getFilterQuery() {
		return $this->fq;
	}
	
	public function setFacetField($aFacetField) {
		$this->aFacetField = $aFacetField;
	}
	
	public function getFacetField() {
		return $this->aFacetField;
	}
	
	public function getFacetFieldResultByKey($key) {
		if (array_key_exists($key, $this->aFacetFieldResult)) {
			return $this->aFacetFieldResult[$key];
		}
	}
	
	public function getFacetFieldResult() {
		return $this->aFacetFieldResult;
	}

	
	public function setup($site_id = 0) {

		global $solr_config;
		
		$oSolrQuery = new SolrQuery;
		$oSolrQuery->setQuery("*:*");
		
		$oSolrQuery->setFacetField($this->getFacetField());		
		$oSolrQuery->setFilterQuery($this->getFilterQuery());
		
		$oSolrSearch = new SolrSearch($solr_config);
		$oSolrSearch->setRows($iRows = 0);
		$oSolrSearch->setStart($iStart = 0);
		
		// add facetField
		if (count($oSolrQuery->getFacetField()) >= 1) {
			foreach($oSolrQuery->getFacetField() as $facet) {
				$oSolrSearch->addFacetField($facet);
			}
		}
		
		$aFilter = array(); 
		
		// add facet field excludes
		if (array_key_exists("continent", $this->fq)) { // continent selected, ignore continent filter query when generating facet counts
			$oSolrSearch->addFacetFieldFilterQueryExclude('continent','continent');
			$oSolrSearch->addFacetFieldFilterQueryExclude('country','country');
			$aFilter[] = "country"; // filter countries by continent
		}
		
		if (array_key_exists("country", $this->fq) && !array_key_exists("activity", $this->fq)) { // country selected, ignore country filter query when generating facet counts 
			$oSolrSearch->addFacetFieldFilterQueryExclude('country',array('country'));
			$oSolrSearch->addFacetFieldFilterQueryExclude('continent',array('country','continent'));
		}
		if (array_key_exists("activity", $this->fq)) {
			//$oSolrSearch->addFacetFieldFilterQueryExclude('activity',array('activity'));
		}
		
		$oSolrSearch->setSiteId($site_id);
		$oSolrSearch->search($oSolrQuery->getQuery(),$oSolrQuery->getFilterQuery(),$oSolrQuery->getSort());
		$oSolrSearch->processResult();		
		
		$oSolrSearch->setFacetFieldResult(TRUE,$aFilter);
				
		// add any facetField results
		if (count($oSolrQuery->getFacetField()) >= 1) {
			foreach($oSolrQuery->getFacetField() as $facet) {
				foreach($facet as $key => $value) {
					$this->aFacetFieldResult[$key] = $oSolrSearch->getFacetFieldResult($key);
				}
			}
		}
		
	}
	
}
