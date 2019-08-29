<?php

/*
 * A data object to encapsulate a SOLR search panel search
 * 
 */


class SolrSearchPanelSearch {
	
	private $oAct;
	private $oCty;
	private $oCtn;
	private $sKeywords;
	private $iDurationFromId;
	private $iDurationToId;
	
	
	private $sForwardUrl;
	
	private $aFiltersEnabled;
	
	public function __construct() {
		
		$this->aFiltersEnabled = array();
	}

	public function getCategory() {
		return $this->oCat;
	}
	
	public function setCategory($oCat) {
		$this->oCat = $oCat;
	}
	
	public function haveCategory() {
		return is_object($this->oCat) ? TRUE : FALSE;
	}

	public function getActivity() {
		return $this->oAct;
	}
	
	public function setActivity($oAct) {
		$this->oAct = $oAct;
	}
	
	public function haveActivity() {
		return is_object($this->oAct) ? TRUE : FALSE;
	}
	
	public function setCountry($oCty) {
		$this->oCty = $oCty;
	}
	
	public function getCountry() {
		return $this->oCty;
	}

	public function haveCountry() {
		return is_object($this->oCty) ? TRUE : FALSE;
	}
	
	public function setContinent($oCtn) {
		$this->oCtn = $oCtn;
	}
	
	public function getContinent() {
		return $this->oCtn;
	}

	public function haveContinent() {
		return is_object($this->oCtn) ? TRUE : FALSE;
	}
	
	public function setKeywords($sKeywords) {
		$this->sKeywords = $sKeywords;
	}	
	
	public function getKeywords() {
		return $this->sKeywords;
	}
	
	public function haveKeywords() {
		return strlen($this->sKeywords) > 1 ? TRUE : FALSE;
	}

	public function getDurationFromId() {
		return $this->iDurationFromId;
	}
	
	public function setDurationFromId($id) {
		return $this->iDurationFromId = $id;
	}
	
	public function getDurationToId() {
		return $this->iDurationToId;
	}
	
	public function setDurationToId($id) {
		return $this->iDurationToId = $id;
	}
	
	public function getForwardUrl() {
		return $this->sForwardUrl;
	}

	public function setup($aSearchParams) {
	
		global $db;
	
		$oActivity = new Activity($db);
		$oCategory = new Category($db);
		$oCountry = new Country($db);
		$oContinent = new Continent($db);

		$oCat = null;
		$oAct = null;
		$oCty = null;
		$oCtn = null;

		if ($aSearchParams['search-panel-activity'] != "NULL") {
			$oCat = $oCategory->GetByName($aSearchParams['search-panel-activity']);
			if (!$oCat)
				$oAct = $oActivity->GetByName($aSearchParams['search-panel-activity']);
		}
		if ($aSearchParams['search-panel-destinations'] != "NULL") {
			$oCtn = $oContinent->GetByName($aSearchParams['search-panel-destinations']);
			if (!$oCtn)
				$oCty = $oCountry->GetByName($aSearchParams['search-panel-destinations']);
			
		}
		if (is_numeric($aSearchParams['search-panel-duration-from'])) {
			$this->setDurationFromId($aSearchParams['search-panel-duration-from']);
		}
		if (is_numeric($aSearchParams['search-panel-duration-to'])) {
			$this->setDurationToId($aSearchParams['search-panel-duration-to']);
		}
		
	
		$this->setCategory($oCat);
		$this->setActivity($oAct);
		$this->setCountry($oCty);
		$this->setContinent($oCtn);
		//$this->setKeywords($aSearchParams['search-panel-keywords']);
	
		$this->setForwardUrl();
	}
	
	/*
	 * Work out which search result page 
	 * search routes to
	 * 
	 */
	public function setForwardUrl() {
		
		/*
		 * Straightforward single variable routes - 
		 * 	 an activity
		 * 	 a country	
		 *   a continent
		 *   keywords
		 */
		if ($this->haveActivity() && !$this->haveCategory() && !$this->haveCountry() && !$this->haveContinent() && !$this->haveKeywords()) {
			return $this->sForwardUrl = "/".$this->getActivity()->url_name;
		} elseif ($this->haveCategory() && !$this->haveActivity() && !$this->haveCountry() && !$this->haveContinent() && !$this->haveKeywords()) {
			return $this->sForwardUrl = "/".$this->getCategory()->url_name;
		} elseif ($this->haveCountry() && !$this->haveCategory() && !$this->haveActivity() && !$this->haveContinent() && !$this->haveKeywords()) {
			return $this->sForwardUrl = "/travel/".$this->getCountry()->url_name;
		} elseif ($this->haveContinent() && !$this->haveCategory() && !$this->haveActivity() && !$this->haveCountry() && !$this->haveKeywords()) {
			return $this->sForwardUrl = "/continent/".$this->getContinent()->url_name;
		} elseif ($this->haveKeywords() && !$this->haveCategory() && !$this->haveActivity() && !$this->haveCountry() && !$this->haveContinent()) {
			return $this->sForwardUrl = "/search/".$this->getKeywords();
		}

		/*
		 * Compound searchs eg -
		 *  { activity OR keywords } and  { country OR continent }
		 *  { activity OR country OR continent } AND keywords
		 */
		if ($this->haveActivity() && $this->haveCountry()) {
			return $this->sForwardUrl = "/".$this->getActivity()->url_name."/".$this->getCountry()->url_name;;
		}

                if ($this->haveActivity() && $this->haveContinent()) {
                        return $this->sForwardUrl = "/".$this->getActivity()->url_name."/".$this->getContinent()->url_name;;
                }


		if ($this->haveCategory() && $this->haveCountry()) {
			return $this->sForwardUrl = "/".$this->getCategory()->url_name."/".$this->getCountry()->url_name;;
		}

                if ($this->haveCategory() && $this->haveContinent()) {
                        return $this->sForwardUrl = "/".$this->getCategory()->url_name."/".$this->getContinent()->url_name;;
                }

		if ($this->haveCountry() && $this->haveKeywords()) {
			return $this->sForwardUrl = "/country/".$this->getCountry()->url_name."/".$this->getKeywords();
		}
		
		if ($this->haveContinent() && $this->haveKeywords()) {
			return $this->sForwardUrl = "/continent/".$this->getContinent()->url_name."/".$this->getKeywords();
		}
		
		
		return $this->sForwardUrl = "/search/".$sQuery;
		
	}
	
	public function setFiltersByUri($sUri) {
		
		// only return filters if page viewed is same as forward url from search
		if ($sUri != $this->sForwardUrl) {
			SolrSearchPanelSearch::clearFromSession();
			return FALSE;
		}

		if (strpos($this->sForwardUrl, 'country') !== 0) {
			if ($this->haveActivity()) $this->aFiltersEnabled['activity'] = TRUE;
		}
		if (strpos($this->sForwardUrl, 'continent') !== 0) {
			if ($this->haveActivity()) $this->aFiltersEnabled['activity'] = TRUE;
		}
	}

	public function filterEnabled($key) {
		return (array_key_exists($key,$this->aFiltersEnabled) && $this->aFiltersEnabled[$key] == TRUE) ? TRUE : FALSE; 
	}
	
	public function getFilterAsCheckbox($key) {
		
		switch($key) {
			case "activity" : 
				$o = $this->getActivity();
				break;
		}
		
		return "<input id='".$key."_0' class='facet' type='checkbox' name='".$o->name."' checked />";
	}

	
	public static function clearFromSession() {
		unset($_SESSION['search_params']);
	}
	
	public function saveInSession() {
		$_SESSION['search_params'] = serialize($this);	
	}

	public static function getFromSession() {
		if (isset($_SESSION['search_params'])) return unserialize($_SESSION['search_params']);
	} 
}
