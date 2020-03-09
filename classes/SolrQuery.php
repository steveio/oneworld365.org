<?php

/*
 * An interface class that serves as a a query pre-processor
 * and API interface between 365 sites and a SOLR search
 *
 *
 *
 */


class SolrQuery {

	private $query;
	private $fq;
	private $sort;
	private $aFacetField;
	private $aFacetQuery;
	private $aFacetFieldFilterQueryExclude;
	private $aFacetFieldSort;
	private $iSiteId;


	public function __construct() {
		$this->query = "";
		$this->fq = array();
		$this->sort = array();
		$this->aFacetField = array();
		$this->aFacetQuery = array();
		$this->aFacetFieldFilterQueryExclude = array(); // facet => tag
		$this->aFacetFieldSort = array();

	}

	public function getQuery() {		    
		return $this->query;
	}

	public function setQuery($query) {
		$this->query = $query;
	}

	public function getFilterQuery() {
		return $this->fq;
	}

	public function setFilterQuery($aFilterQuery) {
		$this->fq = $aFilterQuery;
	}

	public function setFilterQueryByName($key,$value) {
		$this->fq[$key] = $value;
	}

	public function getSort() {
		return $this->sort;
	}

	public function getFacetField() {
		return $this->aFacetField;
	}

	public function setFacetField($aFacetField) {
		$this->aFacetField = $aFacetField;
	}

	public function getFacetQuery() {
		return $this->aFacetQuery;
	}

	public function setFacetQuery($key,$aFacetQuerySet) {
		$this->aFacetQuery[$key] = $aFacetQuerySet;
	}

	public function getFacetFieldFilterQueryExclude() {
		return $this->aFacetFieldFilterQueryExclude;
	}

	public function parseFilterQueryFromRequest() {

		// done this way to collect multiple get params with same name
		// eg ?q=*:*&fq=profile_type:1&fq=country:Brazil&wt=xml&indent=true
		$query  = explode('&', urldecode($_SERVER['QUERY_STRING']));
		$params = array();

		foreach( $query as $param )
		{
			list($name, $value) = explode('=', $param);

			if (preg_match("/^fq/",$name)) {
				$a = explode(":",$value);
				if (in_array($a[0],array("activity","country","continent"))) {
					$quote = "\"";
				}
				$this->fq[$a[0]] = $quote.$a[1].$quote;
			}
		}
		
		if (array_key_exists("duration", $this->fq)) $this->processDurationFilterQuery();
		if (array_key_exists("price", $this->fq)) $this->processPriceFilterQuery();
	}

	public function unsetFilterQuery()
	{
        // unset filter queries, articles don't have these
	    if (isset($this->fq['category_id'])) unset($this->fq['category_id']);
	    if (isset($this->fq['activity_id'])) unset($this->fq['activity_id']);
	    if (isset($this->fq['country_id'])) unset($this->fq['country_id']);
	    if (isset($this->fq['continent_id'])) unset($this->fq['continent_id']);
	}

	public function processPriceFilterQuery() {

		$key = $this->fq['price'];
		unset($this->fq['price']);

		$aFacetQuery[] = array('price_0_250' => '(price_from:[* TO 250])');
		$aFacetQuery[] = array('price_250' => '(price_from:[250 TO 500])');
		$aFacetQuery[] = array('price_500' => '(price_from:[500 TO 750])');
		$aFacetQuery[] = array('price_750' => '(price_from:[750 TO 1000])');
		$aFacetQuery[] = array('price_1000' => '(price_from:[1000 TO 2000])');
		$aFacetQuery[] = array('price_2000' => '(price_from:[2000 TO *])');
		$aFacetQuery[] = array('price_all' => '(*:*)');

		switch($key) {
			case "price_0_250" :
				$this->fq['price_from'] = "[* TO 250]";
				break;
			case "price_250" :
				$this->fq['price_from'] = "[250 TO 500]";
				break;
			case "price_500" :
				$this->fq['price_from'] = "[500 TO 750]";
				break;
			case "price_750" :
				$this->fq['price_from'] = "[750 TO 1000]";
				break;
			case "price_1000" :
				$this->fq['price_from'] = "[1000 TO 2000]";
				break;
			case "price_2000" :
				$this->fq['price_from'] = "[2000 TO *]";
				break;
			case "price_all" :
				break;
		}
	}

	public function processDurationFilterQuery() {

		$key = $this->fq['duration'];
		unset($this->fq['duration']);

		switch($key) {
			case "duration_0_1" :
				$this->fq['duration_from'] = "[* TO 6]";
				break;
			case "duration_1_2" :
				$this->fq['duration_from'] = "[7 TO 13]";
				break;
			case "duration_2_4" :
				$this->fq['duration_from'] = "[14 TO 27]";
				break;
			case "duration_4_8" :
				$this->fq['duration_from'] = "[28 TO 55]";
				break;
			case "duration_8_24" :
				$this->fq['duration_from'] = "[60 TO 167]";
				break;
			case "duration_24_*" :
				$this->fq['duration_from'] = "[182 TO *]";
				break;
			case "duration_all" :
				break;
		}

	}

	public function setDefaultProfileType() {
		// combined content type result  
		if (array_key_exists("profile_type", $this->fq) && in_array($this->fq['profile_type'], array("(1 OR 0)"))) {
			return false;
		}

		// in case profile type is missing or not a recognised value
		if (!array_key_exists("profile_type", $this->fq) || (!is_numeric($this->fq['profile_type'])) || (!in_array($this->fq['profile_type'],array(0,1,2)))) {
			$this->fq['profile_type'] = 1;
		}
	}

	public function getFilterQueryByName($key) {
		if (isset($this->fq[$key])) {
			return $this->fq[$key];
		}
	}

	public function setSort($field,$direction) {
		$this->sort[$field] = $direction;
	}

	public function setSiteSpecificCategoryFilterQuery($query) {
		if (!isset($this->fq['category_id'])) {
			$this->fq['category_id'] = $query;
		}
	}


	/*
	 * setup methods for various search types -
	 * keyword, by category, by activity, by country
	 * or raw solr string search eg q=text:lions%20AND%20category:Volunteer&rows=10
	 *
	 *
	 */

	private function filterQueryKeywords($query) {
		// (optional) array of words to remove from keyword searches
		//$wordlist = array("volunteer","work");
		$wordlist = array();

		foreach ($wordlist as &$word) {
			$word = '/\b' . preg_quote($word, '/') . '\b/';
		}

		return preg_replace($wordlist, '', $query);

	}

	/**
	 * Given a set of URI segments /<seg1>/</seg2>/<seg-n>
	 * Match segment keywords to known identifiers, setup relevant filters & SOLR query
	 *
	 * for example:
	 *  /tefl-courses/united-states should result in country and activity filter queries:
	 *
	 *     [fq:SolrQuery:private] => Array
     *      (
     *          [activity_id] => 144 // "tefl-courses"
     *          [country_id] => 71 // "united states"
     *      )
	 *
	 * This method replaces setup* (setupKeywordSearch(), setupActivitySearch() ..)
	 * methods based on URLs in specific formats with a generic implementation
	 *
	 * @param array URI segments
	 * @return
	 */
  public function setupFilterQuery($aRequestUri)
	{
		if (!is_array($aRequestUri)) throw new Exception("Invalid URI segments");

		$arrProcessedKeywords = array();

        // URI segment terms to exclude from search query eg /search/travel/<country>
		$arrKeywordException = array("search","travel");

		foreach($aRequestUri as $strKeyword)
		{
			if (strlen($strKeyword) < 1) continue;
			if (in_array($strKeyword,$arrKeywordException)) continue;

			$bMatched = false;

			// in order of match probability
			$arrIdentifier = array("activity","country","continent","category");

			foreach($arrIdentifier as $strIdentifierKeyword)
			{
				if ($bMatched) continue;

				try {

			    $result = NameService::lookupNameSpaceIdentifier($strIdentifierKeyword,$strKeyword);
			    if (is_numeric($result['id']))
					{
			    	$this->fq[$strIdentifierKeyword.'_id'] = $result['id'];
						$bMatched = true;
					}

			  } catch (Exception $e) {}
			}

			$arrProcessedKeywords[] = str_replace("-"," ",$strKeyword);
		}

		// setup keyword query string
		$this->query = SolrSearch::SolrQueryCharSafe(implode(" ",$arrProcessedKeywords));
		$this->query = $this->filterQueryKeywords($this->query);

	}

	/**
	*
	* Setup SOLR facet fields relevant to API search request
	*
	**/
	public function setupFacetFieldset()
	{

		// add a facet fields
		$this->aFacetField[] = array("activity" => "activity");

		// do not display Continent / Country filters on a country URL
		if(!is_numeric($this->fq['country_id']) && !is_numeric($this->fq['continent_id']))
		{
		  $this->aFacetField[] = array("country" => "country");
		  $this->aFacetField[] = array("continent" => "continent");
		}
		// display Country filter on a continent URL
		if(is_numeric($this->fq['continent_id']))
		{
		    $this->aFacetField[] = array("country" => "country");
		}

		// display species and habitat facet filters on specific activity and destination pages
		if (in_array($this->fq['activity_id'],array(1,6,81,76)))
		{
			$this->aFacetField[] = array("species" => "species");
			$this->aFacetField[] = array("habitats" => "habitats");
		}
		/*
		if (in_array($this->fq['country_id'],array(1,7,5,148,6,7,120,13,14,18,133,22,24,26,27,28,30,31,118,37,39,40,42,44,46,47,48,53,54,60,63,65,66,73,74,75,114,76)))
		{
			$this->aFacetField[] = array("species" => "species");
			$this->aFacetField[] = array("habitats" => "habitats");
		}
		*/

		$this->aFacetFieldFilterQueryExclude['activity'] = 'activity';
		$this->aFacetFieldFilterQueryExclude['country'] = 'country';
	}

	/*
	 * Keywords only, excludes SOLR syntax queryies
	 *
	 */
	public function setupKeywordSearch($raw_query) {

		$this->query = SolrSearch::SolrQueryCharSafe(str_replace("-"," ",$raw_query));

		$this->query = $this->filterQueryKeywords($this->query);

	}


	public function setupSolrSearch($raw_query) {

 		if (!$this->parseAsSolrQuery($raw_query)) {
 			$this->setupKeywordSearch($raw_query);
 			return;
 		}

	}

	// determine if string conforms to solr query syntax
	public function parseAsSolrQuery($request) {

		if (!isset($request['q']) || strlen($request['q']) < 1) throw new Exception("Invalid query string");

		$this->query = $request['q'];

		$this->solrQuerySyntax = TRUE;

		// @todo - add facet handling

		return TRUE;
	}


}
