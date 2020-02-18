<?php

require_once '/www/vhosts/365admin.org/htdocs/vendor/autoload.php';

//require('/www/vhosts/365admin.org/htdocs/vendor/solarium/solarium/library/Solarium/Autoloader.php');
//Solarium_Autoloader::register();



class SolrSearch {
	
	protected $client;
	protected $resultset;
	
	private $rows;  // page size
	private $start; // start index

	protected $aId;
	
	private $aFilterQuery;
	
	private $aFacetField;
	private $aFacetFieldResult;
	
	private $aFacetQuery;
	private $aFacetQueryResult;

	private $boostQuery;

	public $aFacetFieldFilterQueryExclude; // array 'facetField' => array('tag1','tag2')

	private $iSiteId;
	
	public function __construct($solr_config) {

		$this->start = 0;
		$this->rows = 20; // default num rows to return
		$this->client = new Solarium\Client($solr_config); 
		$this->client->getEndpoint('localhost')->setCore('collection1');

		$this->aId = array();
		
		$this->aFacetQuery = array();
		$this->aFacetField = array();
		$this->aFacetFieldResult = array();
		$this->aFacetFieldFilterQueryExclude = array();
	}

	public static function SolrQueryCharSafe($str) {
		return preg_replace("/[\/-]/"," ",$str);
	}
	
	public function setStart($idx) {
		$this->start = $idx;
	}
	
	public function getStart() {
		return $this->start;
	}
	
	public function setRows($rows) {
		$this->rows = $rows;
	}
	
	public function getRows() {
		return $this->rows;
	}
	
	public function getId($bIdOnly = false) {

	    if ($bIdOnly)
	    {
	        $aId = array();
	        foreach($this->aId as $arr)
	        {
	            $aId[] = $arr['profile_id'];
	        }
	      return $aId;
	    } else {
		  return $this->aId;
	    }
	}
	
	public function setSiteId($site_id) {
		$this->iSiteId = $site_id;
	}
	
	public function getSiteId() {
		return $this->iSiteId;
	}
	
	public function setFilterQuery($fq) {
		$this->aFilterQuery = $fq;
	}

	public function getFilterQuery() {
		return $this->aFilterQuery;
	}
	
	public function getFilterQueryByKey($key) {
		return $this->aFilterQuery[$key];
	}

	public function filterQueryKeyExists($key) {
		return (array_key_exists($key,$this->aFilterQuery)) ? TRUE : FALSE;
	}
	
	public function filterQueryValueExists($key,$value) {
		if (array_key_exists($key,$this->aFilterQuery)) {
			return ($value == preg_replace("/\"/","",$this->aFilterQuery[$key])) ? TRUE : FALSE;
		}
	}

	public function filterQueryValueExistsFuzzy($key,$value) {
		if (array_key_exists($key,$this->aFilterQuery)) {
			$fqValue = preg_replace("/\"/","",$this->aFilterQuery[$key]);
			return (substr_count($fqValue, $value) >= 1) ? TRUE : FALSE;
		}
	}	

	public function getFacetField() {
		return $this->aFacetField;
	} 

	public function addFacetField($aQuery) {
		$this->aFacetField[] = $aQuery;
	}
	
	public function getFacetFieldResult($key) {
		return $this->aFacetFieldResult[$key];
	}
	
	public function getFacetFieldResults() {
		return $this->aFacetFieldResult;
	}

	public function addFacetFieldResult($facetField, $aResult) {
		$this->aFacetFieldResult[$facetField] = $aResult;
	}

	public function setFacetFieldResult($filter = TRUE,$aFilter = array('country','activity')) {
				
		if (count($this->getFacetField()) >= 1 ) {
			
			
			foreach($this->getFacetField() as $facetField) {
				foreach($facetField as $key => $field) {
					$facet = $this->resultset->getFacetSet()->getFacet($key);

					/*
					 * Filter the returned facets 
					 * 
					 */
					if ($filter) {
						if ($key == "country" && $this->filterQueryKeyExists("continent_id") && in_array('country',$aFilter)) {
							$facet = $this->filterCountryByContinentId($facet,$this->getFilterQueryByKey('continent_id'));
						} elseif ($key == "country" && $this->filterQueryKeyExists("continent")  && in_array('country',$aFilter)) {
							$facet = $this->filterCountryByContinentName($facet,$this->getFilterQueryByKey('continent'));
						} elseif ($key == "activity"  && in_array('activity',$aFilter)) {
							//$facet = $this->filterActivityByWebsiteId($facet);
							if (isset($this->aFilterQuery['activity_id'])) {
								$facet = $this->filterActivityFacetList($facet,$this->getFilterQueryByKey('activity_id'));
							} elseif (isset($this->aFilterQuery['category_id']) && is_numeric($this->aFilterQuery['category_id'])) {
								$facet = $this->filterActivityFacetList($facet,$this->getFilterQueryByKey('category_id'),"CATEGORY");
							} elseif (isset($this->aFilterQuery['category'])) {
								$facet = $this->filterActivityFacetList($facet,$this->getFilterQueryByKey('category'),"CATEGORY");
							}
						}
					}
					
					$aResult = array();
					foreach($facet as $value => $count) {
						$checked = ($this->filterQueryValueExists($key,$value)) ? TRUE : FALSE;
						$aResult[] = array('facet' => $value, 'count' => $count, 'checked' => $checked);
					}

					$this->addFacetFieldResult($key,$aResult);
				}
			}
		}
	}
	
	public function setFacetFieldFilterQueryExclude($aExclude) {
		$this->aFacetFieldFilterQueryExclude = $aExclude;
	}

	public function addFacetFieldFilterQueryExclude($key,$value) {
		$this->aFacetFieldFilterQueryExclude[$key] = $value;
	}
		
	public function getFacetQuery() {
		return $this->aFacetQuery;
	}
	
	public function addFacetQuery($key,$aFacetQuerySet) {
		$this->aFacetQuery[$key] = $aFacetQuerySet;
	}
	
	public function getFacetQueryResult($key) {
		return $this->aFacetQueryResult[$key];
	}
	
	public function addFacetQueryResult($key, $aResult) {
		$this->aFacetQueryResult[$key] = $aResult;
	}
	
	public function setFacetQueryResult() {

		if (count($this->getFacetQuery()) >= 1 ) {
			foreach($this->getFacetQuery() as $key => $facetQuery) {
				$facet = $this->resultset->getFacetSet()->getFacet($key);
				/*
				if ($key == "duration") {
					$facet = $this->mapDurationLabels($facet);
				}
				if ($key == "price") {
					$facet = $this->mapPriceLabels($facet);
				}
				*/
				$aResult = array();

				foreach($facet as $value => $count) {
					$checked = ($this->filterQueryValueExists($key,$value)) ? TRUE : FALSE;
					if ($key == "duration") {
						$tmp = explode("_",$value);
						$from =str_replace('0', '*', $tmp[1]);
						$to =str_replace('0', '*', $tmp[2]);
						$checked = ($this->filterQueryValueExistsFuzzy("duration_from","[".$from." TO ".$to."]")) ? TRUE : FALSE;
					}
					if ($key == "price") {
						$tmp = explode("_",$value);
						$from = $tmp[1];
						$to =$tmp[2];
						$checked = ($this->filterQueryValueExistsFuzzy("price_from","[".$from)) ? TRUE : FALSE;
					}
					$aResult[] = array('facet' => $value, 'count' => $count, 'checked' => $checked);
				}
				$this->addFacetQueryResult($key,$aResult);				
			}
			
		}
	}
	
	public function setBoostQuery($str)
	{
	    $this->boostQuery = $str;
	}

	public function getBoostQuery()
	{
        return $this->boostQuery;	    
	}

	public function ping() {
		
		// create a ping query
		$ping = $this->client->createPing();
		
		// execute the ping query
		try{
			$result = $this->client->ping($ping);
		}catch(Solarium\Exception $e){
			throw $e;
		}
		
	} 

	public function search($query, $fq = array(), $sort = array(),$fields = array()) {
	
		$this->ping();
	
		$this->setFilterQuery($fq);
	
		if (count($fields) < 1) $fields = array('profile_id','company_id','profile_type','duration_from','duration_to');
		
		if (strpos($query,":") === FALSE) {
			$query = 'text:'.$query. ' title:'.$query.'^4.0 desc_short: '.$query.'^2.0';
		}		


		$query = array(
				'query'         => $query,
				'start'         => $this->getStart(),
				'rows'          => $this->getRows(),
				'fields'        => $fields
		);
		
		if (array_key_exists("profile_type", $fq)) {
			$query['filterquery']['profile_type'] = $fq['profile_type'];
		} else {
			$query['filterquery']['profile_type'] = 1;
		}
		
		if (array_key_exists("category_id", $fq)) {
			$category_id_str = is_array($fq['category_id']) ? "(".implode("OR ",$fq['category_id']).")" : $fq['category_id'];
			$query['filterquery']['category_id'] = array('query' => 'category_id:'.$category_id_str);
			unset($fq["category_id"]);
		}
		if (array_key_exists("activity_id",$fq)) {
			$query['filterquery']['activity_id'] = array('query' => 'activity_id:'.trim($fq['activity_id']), 'tag' => 'act');
			unset($fq["activity_id"]);
		}
		if (array_key_exists("country_id",$fq)) {
			$query['filterquery']['country_id'] = array('query' => 'country_id:'.trim($fq['country_id']));
			unset($fq["country_id"]);
		}
		if (array_key_exists("continent_id",$fq)) {
			$query['filterquery']['continent_id'] = array('query' => 'continent_id:'.trim($fq['continent_id']));
			unset($fq["continent_id"]);
		}
	
		// handle caller specified filter query
		foreach($fq as $k => $v) {
			if (array_key_exists($k,$this->aFacetFieldFilterQueryExclude)) {
				$options = array('query' => $k.':'.trim($v),'tag' => $k);
			} else {
				$options = array('query' => $k.':'.trim($v));
			}
			$query['filterquery'][$k] = $options;
		}
		
		// add sort
		$query['sort'] = $sort;
	
		$this->select($query);
	
	}

	public function select($params) {
				
		// get a select query instance based on the config
		$query = $this->client->createSelect($params);

		if (strlen($this->getBoostQuery()) > 1)
		{
		  $edismax = $query->getEDisMax();
		  $edismax->setBoostQuery($this->getBoostQuery());
		}

		if (count($this->getFacetField()) >= 1 ) {
			$facetSet = $query->getFacetSet();

			foreach($this->getFacetField() as $facetField) {
				foreach($facetField as $name => $field) {
					if (array_key_exists($field,$this->aFacetFieldFilterQueryExclude)) {
						$options = array('key' => $name,'exclude' => $this->aFacetFieldFilterQueryExclude[$field]);
					} else {
						$options = $name;
					}
					$facetSet->createFacetField($options)->setField($field);						
					$facetSet->getFacet($name)->setMinCount(1);
				}
			}
		}

		if (count($this->getFacetQuery()) >= 1 ) {
			$facetSet = $query->getFacetSet();
						
			foreach($this->getFacetQuery() as $key => $facetQuerySet) {
				$facet = $facetSet->createFacetMultiQuery($key);								
				foreach($facetQuerySet as $facetQuery) {
					foreach($facetQuery as $name => $value) {
						
						$facet->createQuery($name, $value);

					}	
				}
			}
				
		}
				
		$request = $this->client->createRequest($query);
		$requestInfo = (string)$request;

		
		Logger::DB(2,"API SOLR Query: ".$requestInfo);

		try {
			// this executes the query and returns the result
			$this->resultset = $this->client->select($query);
		} catch(Exception $e) {
			throw $e;
		}		
	}
	

	public function processResult() {
			    
		$aResult = array();

		if ($this->getNumFound() >= 1) {
			$this->resultset = $this->getResultSet();
				
			foreach($this->resultset as $doc) {
				$this->aId[] = $doc->profile_id;
			}
	
		}
	}
	
	public function getResultSet() {
		return $this->resultset;
	}
	
	public function getNumFound() {
		return $this->resultset->getNumFound();
	}
	
	public function getResultByProfileId($id) {
		foreach($this->resultset as $doc) {
			if ($doc->profile_id == $id) return $doc;
		}
	}
	
	public function filterCountryByContinentId($facet,$continent_id) {
	
		global $db;
	
		if(!is_numeric($continent_id)) return $facet;
	
		$oCountry = new Country($db);
		$aCountry = $oCountry->GetByContinentId($continent_id);
		foreach($aCountry as $c) {
			$aCountries[trim($c['name'])] = $c['id'];
		}
		$aFilteredCountries = array();
	
		foreach($facet as $key => $val) {
			if (array_key_exists($key,$aCountries)) {
				$aFilteredCountries[$key] = $val;
			}
		}
		return $aFilteredCountries;
	}
	
	public function filterCountryByContinentName($facet,$continent_name) {
	
		global $db;

		if (preg_match("/Australasia/",$continent_name)) $continent_name = "Australasia / Pacific";

		$oCountry = new Country($db);
		$aCountry = $oCountry->GetByContinentName(preg_replace("/\"/","",$continent_name));

		if (!is_array($aCountry)) return array();

		foreach($aCountry as $c) {
			$aCountries[trim($c['name'])] = $c['id'];
		}
		$aFilteredCountries = array();
	
		foreach($facet as $key => $val) {
			if (is_array($aCountries) && array_key_exists($key,$aCountries)) {
				$aFilteredCountries[$key] = $val;
			}
		}
		return $aFilteredCountries;
	}

	/*
	public function mapDurationLabels($facet) {
	
		$mapping = array(
				'duration_1' => 'upto 1 week',
				'duration_2' => '1 to 2 weeks',
				'duration_3' => '2 to 4 weeks',
				'duration_4' => '4 to 8 weeks',
				'duration_5' => '8 weeks+',
		);
	
		$a = array();
	
		foreach($facet as $key => $count) {
			$a[$mapping[$key]] = $count;
		}
	
		return $a;
	
	}
	
	public function mapPriceLabels($facet) {
	
		$mapping = array(
				'price_from_1' => '$0 to $500',
				'price_from_2' => '$500 to $750',
				'price_from_3' => '$750 to $1250',
				'price_from_4' => '$1250 to $2000',
				'price_from_5' => '$2000+'
		);
	
		$a = array();
	
		foreach($facet as $key => $count) {
			$a[$mapping[$key]] = $count;
		}
	
		return $a;
	
	}
	*/
	
	public function filterActivityByWebsiteId($facet) {

		$oActivity = new Activity();
		$aActivity = $oActivity->GetActivityByWebsiteId($this->getSiteId());

		$aFiltered = array();
		
		foreach($facet as $name => $count) {
			if (in_array($name,$aActivity)) {
				$aFiltered[$name] = $count;
			}
		}

		return $aFiltered;
	}

	/**
	 * Filter activity facet list by category id
	 * @param unknown $facet
	 * @param unknown $id
	 * @param string $key
	 * @return boolean|unknown|unknown[]
	 */
	public function filterActivityFacetList($facet,$id,$key = "ACTIVITY") {
	
	    if (!is_numeric($id)) return false;

	    global $db;

	    // get activity names by category
	    $aActivityByCategory = array();
	    
	    $sql = "select m.category_id,a.id,a.name from activity a, cat_act_map m where m.activity_id = a.id;";
        $db->query($sql);
        $aResult = $db->getRows();
        foreach($aResult as $row)
        {
            $aActivityByCategory[$row['category_id']][] = preg_replace("/&/","",$row['name']);
        }
 		
        // resolve category id
		foreach($aActivityByCategory as $catId => $aActivityId) {
				
			if ($key == "ACTIVITY") {
			    $sql = "select category_id from cat_act_map where activity_id = ".$id;
			    $category_id = $db->getFirstCell($sql);
			} elseif ($key == "CATEGORY") {
				
				if (is_numeric($id)) {
					$category_id = $id;
				} else {
					$id = preg_replace("/\"/","",$id);
					$o = Category::GetByName($id);
					if (is_numeric($o->id)) {
						$category_id = $o->id;
					}
				}
			}
		}
				
		if (!is_numeric($category_id)) return $facet; // display all facets
				
		// now filter the facets based on the activity set
		$aFiltered = array();
	
		
		foreach($facet as $name => $count) 
		{
			if (is_array($aActivityByCategory[$category_id]))
			{
				if (in_array($name,$aActivityByCategory[$category_id])) {
					$aFiltered[$name] = $count;
				}
			}
		}

		return $aFiltered;
	}
	
	
	public static function removeWords($aWords, $query) {
		$wordlist = $aWords;
		
		foreach ($wordlist as &$word) {
			$word = '/\b' . preg_quote($word, '/') . '\b/';
		}
		
		return trim(preg_replace($wordlist, '', $query));
	}
}

