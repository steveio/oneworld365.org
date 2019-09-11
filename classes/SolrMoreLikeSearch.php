<?php



class SolrMoreLikeSearch extends SolrSearch {


	function __construct($solr_config) {

		parent::__construct($solr_config);

	}

	function getCompanyByArticle($id) {


		// Solarium_Query_MoreLikeThis

	    
		// get a select query instance
		$query = $this->client->createMoreLikeThis();


		// add a query and morelikethis settings (using fluent interface)
		$query->setQuery('id:'.$id)
		->getMoreLikeThis()
		->setFields('text')
		->setMinimumDocumentFrequency(1)
		->setMinimumTermFrequency(2);

		$query->setFields(array('profile_id'));
		$query->setStart(0);
		$query->setRows($this->getRows());
		$query->createFilterQuery('profile_type')->setQuery('profile_type:0');
		//$query->createFilterQuery('active')->setQuery('active: 1');

		// this executes the query and returns the result#
		$request = $this->client->createRequest($query);
		$requestInfo = (string)$request;

		Logger::DB(2,"API SOLR Query: ".$requestInfo);

		try {
			// this executes the query and returns the result
			$resultset = $this->client->select($query);
		} catch(Exception $e) {
			throw $e;
		}


		$aResult = array();

		if ($resultset->getNumFound() >= 1) {
			foreach ($resultset as $document) {
				$this->aId[] = $document->profile_id;
			}
		}
	}

	function getPlacementsByArticle($id) {


		// Solarium_Query_MoreLikeThis

		// get a select query instance
		$query = $this->client->createMoreLikeThis();


		// add a query and morelikethis settings (using fluent interface)
		$query->setQuery('id:'.$id)
		->getMoreLikeThis()
		->setFields('text')
		->setMinimumDocumentFrequency(1)
		->setMinimumTermFrequency(2);

		$query->setFields(array('profile_id'));
		$query->setStart(0);
		$query->setRows($this->getRows());
		$query->createFilterQuery('profile_type')->setQuery('profile_type:1');
		$query->createFilterQuery('active')->setQuery('active: 1');

		// this executes the query and returns the result#
		$request = $this->client->createRequest($query);
		$requestInfo = (string)$request;

		Logger::DB(2,"API SOLR Query: ".$requestInfo);

		try {
			// this executes the query and returns the result
			$resultset = $this->client->select($query);
		} catch(Exception $e) {
			throw $e;
		}


		$aResult = array();

		if ($resultset->getNumFound() >= 1) {
			foreach ($resultset as $document) {
				$this->aId[] = array('profile_id' => $document->profile_id,'profile_type' => 1);
			}
		}

	}

	function getPlacementsByArticleBalanced($id, $field = 'text') {


		// Solarium_Query_MoreLikeThis

		// get a select query instance
		$query = $this->client->createMoreLikeThis();


		// add a query and morelikethis settings (using fluent interface)
		$query->setQuery('id:'.$id)
		->getMoreLikeThis()
		->setFields($field)
		->setMinimumDocumentFrequency(1)
		->setMinimumTermFrequency(1);

		$query->setFields(array('profile_id','profile_type','company_id'));
		$query->setStart(0);
		$query->setRows($this->getRows());
		$query->createFilterQuery('profile_type')->setQuery('profile_type:1');
		$query->createFilterQuery('active')->setQuery('active: 1');

		// this executes the query and returns the result#
		$request = $this->client->createRequest($query);
		$requestInfo = (string)$request;

		Logger::DB(2,"API SOLR Query: ".$requestInfo);

		try {
			// this executes the query and returns the result
			$resultset = $this->client->select($query);
		} catch(Exception $e) {
			throw $e;
		}

		$aResult = array();

		/*
		if ($resultset->getNumFound() >= 1) {
			foreach ($resultset as $document) {
				$this->aId[] = array('profile_id' => $document->profile_id,'profile_type' => 1);
			}
		}
		*/


		foreach($resultset as $doc) {
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

		$aId = $oBalancedDistributor->Fetch($this->getRows());

		foreach($aId as $id) {
			$this->aId[] = array('profile_id' => $id,'profile_type' => 1);
		}
	}

	// generalised to return any profile type
	function getPlacementsByPlacement($oid,$company_id,$profile_type = "1", $arrFilterQuery = array()) {

		if (!is_numeric($oid)) return FALSE;

		// Solarium_Query_MoreLikeThis

		// get a select query instance
		$query = $this->client->createMoreLikeThis();


		// add a query and morelikethis settings (using fluent interface)
		$query->setQuery('id:'.$oid)
		->getMoreLikeThis()
		->setFields('text')
		->setMinimumDocumentFrequency(1)
		->setMinimumTermFrequency(2);

		$query->setFields(array('profile_id'));
		$query->setStart(0);
		$query->setRows($this->getRows());
		$query->createFilterQuery('profile_type')->setQuery('profile_type:'.$profile_type);
		$query->createFilterQuery('active')->setQuery('active: 1');

		// exclude placements associated with company being viewed
		//$query->createFilterQuery('company_id')->setQuery('-company_id:'.$company_id);
		if (is_array($arrFilterQuery))
		{
		    foreach($arrFilterQuery as $key => $value)
		    {
		        $query->createFilterQuery($key)->setQuery($key.":".$value);
		    }
		}

		// this executes the query and returns the result#
		$request = $this->client->createRequest($query);
		$requestInfo = (string)$request;

		Logger::DB(2,"API SOLR Query: ".$requestInfo);

		try {
			// this executes the query and returns the result
			$resultset = $this->client->select($query);
		} catch(Exception $e) {
			throw $e;
		}

		$aResult = array();

		if ($resultset->getNumFound() >= 1) {
			foreach ($resultset as $document) {
				$profile_type = PlacementProfile::GetTypeById($document->profile_id);
				$this->aId[] = array('profile_id' => $document->profile_id,'profile_type' => $profile_type);
			}
		}

	}

	/**
	 * Article - find related articles
	 *
	 * @param int article id
	 * @param array $arrFilterQuery
	 * @throws Exception
	 * @return boolean
	 */
	function getRelatedArticle($intArticleId, $arrFilterQuery = array()) {

	    if (!is_numeric($intArticleId)) return FALSE;

	    // Solarium_Query_MoreLikeThis

	    // get a select query instance
	    $query = $this->client->createMoreLikeThis();


	    // add a query and morelikethis settings (using fluent interface)
	    $query->setQuery('id:'.$intArticleId)
	    ->getMoreLikeThis()
	    ->setFields('text')
	    ->setMinimumDocumentFrequency(1)
	    ->setMinimumTermFrequency(2);

	    $query->setFields(array('profile_id', 'title'));
	    $query->setStart(0);
	    $query->setRows(20);
	    $query->createFilterQuery('profile_type')->setQuery('profile_type: 2');
	    $query->createFilterQuery('active')->setQuery('active: 1');

	    if (is_array($arrFilterQuery))
	    {
	        foreach($arrFilterQuery as $key => $value)
	        {
	            $query->createFilterQuery($key)->setQuery($key.":".$value);
	        }
	    }

	    // this executes the query and returns the result
	    $request = $this->client->createRequest($query);
	    $requestInfo = (string)$request;

	    Logger::DB(2,"API SOLR Query: ".$requestInfo);

	    try {
	        // this executes the query and returns the result
	        $resultset = $this->client->select($query);
	    } catch(Exception $e) {
	        throw $e;
	    }

	    $aResult = array();

	    if ($resultset->getNumFound() >= 1) {
	        foreach ($resultset as $document) {
	            $this->aId[] = $document->profile_id;
	        }
	    }
	    if (!is_array($this->aId)) return array();

	    $this->aId = array_unique($this->aId);

	    $arrResult = array();
        foreach($this->aId as $iArticleId)
        {
            $oArticle = new Article();
            $oArticle->SetFetchMode(FETCHMODE__SUMMARY);
            $oArticle->GetById($iArticleId);
            if (strlen($oArticle->GetDescShort()) < 60) continue;
            if (!array_key_exists($oArticle->GetId(),$arrResult))
                $arrResult[$oArticle->GetId()] = $oArticle;
        }

        return $arrResult;
	}


}



?>
