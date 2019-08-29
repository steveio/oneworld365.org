<?php

define("ERROR_COMPANY_PROFILE_NOT_FOUND", "ERROR_COMPANY_PROFILE_NOT_FOUND");

class SolrIndexer {
	
	public $aId;

	public $debug = false;
	
	private $bExtraFacetDataEnabled;
	private $aExtraFacetData;
	

	// refdata id to currency type, w/ approx exchange to USD (correct as of July 2013)
	private $aCurrencyTypeMapping = array(
			290 => array("STERLING", 1.5),
			291 => array("EURO", 1.3),
			292 => array("DOLLAR", 1)
	);

	// refdata duration id to number of days
	private $aDuration2DaysMapping = array(
			116 => 0, // < 1 week
			117 => 7, // 1 week
			118 => 14, // 2 weeks
			119 => 21, // 3 weeks
			120 => 28, // 4 weeks
			121 => 42, // 6 weeks
			122 => 60, // 2 months
			123 => 91,// 3 months
			124 => 121, // 4 months
			125 => 182, // 6 months
			126 => 365, // 1 year
			127 => 366 // > 1 year
	);


	// refdata duration id to number of weeks
	private $aDuration2WeeksMapping = array(
			116 => 0, // < 1 week
			117 => 1, // 1 week
			118 => 2, // 2 weeks
			119 => 3, // 3 weeks
			120 => 4, // 4 weeks
			121 => 6, // 6 weeks
			122 => 8, // 2 months
			123 => 12,// 3 months
			124 => 16, // 4 months
			125 => 36, // 6 months
			126 => 52, // 1 year
			127 => 5200 // > 1 year
	);
	
	
	// refdata objects
	private $oPrice;
	private $oDuration;
	
	
	
	public function __construct() {
		
		$this->aId = array();
		
		$this->bExtraFacetDataEnabled = FALSE;
		$this->aExtraFacetData = array();
		
		$this->initRefdata();
	}
	
	public function initRefdata() {
		// refdata for price to/from labels
		$this->oPrice = new Refdata(REFDATA_APPROX_COST);
		$this->oPrice->SetOrderBySql(' sort_order ASC');
		$this->oPrice->GetByType();
		
		// refdata for currency
		$this->oCurrency = new Refdata(REFDATA_CURRENCY);
		$this->oCurrency->GetByType();
		
	}
	
	public function setId($aId) {
		$this->aId = $aId;
	}
	
	public function getId() {
		return $this->aId;
	}
	
	public function indexCompany() {

		global $db, $solr_config;
		
		// get company id's for indexing
		$aCompanyId = $this->getId();
		
		$total_company = count($aCompanyId);
		
		if (LOG) Logger::DB(2,JOBNAME,'FOUND '.count($aCompanyId)." COMPANY PROFILES TO INDEX");
		
		// create a SOLR client instance
		$client = new Solarium\Client($solr_config);
		$client->getEndpoint('localhost')->setCore('collection1');
		$update = $client->createUpdate();
		
		$idx = 0;
		$commitEvery = 20;
		$commitIdx = 0;
		
		$aIdBatch = array();
		
		if (count($aCompanyId) >= 1) {
			foreach ($aCompanyId as $a) {
		
				$idx++;
				
				if (LOG) Logger::DB(2,JOBNAME,"PROCESSING COMPANY ID : ".$a['id'] ." (".$idx." of ".$total_company.")");
		
				// get the company profile
				$profile_type = CompanyProfile::GetTypeById($a['id']);
				$oCProfile = ProfileFactory::Get($profile_type);
				if (!is_object($oCProfile))
				{
					if (LOG) Logger::DB(1,JOBNAME,"ERROR: FAILED TO RESOLVE COMPANY TYPE id : ".$a['id']." type: ".$profile_type);
					continue;
				}
				$oCProfile->GetProfileById($a['id'],"PROFILE");
					
				if (!is_numeric($oCProfile->GetId())) {
					if (LOG) Logger::DB(1,JOBNAME,"ERROR: FAILED TO FETCH COMPANY  id : ".$a['id']);
					continue;
				}
		
				$aIdBatch[] = $a['id'];
					
				$oCProfile->GetCategoryInfo();
				$oCProfile->GetCountryInfo();
				$oCProfile->GetActivityInfo();
		
					
				$oSolrDocument = $update->createDocument();
					
				// INDEX GENERIC FIELDS COMMON TO ALL PROFILES
				$oSolrDocument->id = $oCProfile->GetOid();
				$oSolrDocument->profile_id = $oCProfile->GetId();
				$oSolrDocument->profile_type = $oCProfile->GetGeneralType(); // only interested in 0 = COMPANY PROFILE, 1 = PLACEMENT
				$oSolrDocument->prod_type = $oCProfile->GetProdType();
				$oSolrDocument->company_id = $oCProfile->GetId();
				$oSolrDocument->company_name = $oCProfile->GetCompanyName();
				$oSolrDocument->title = SolrIndexer::cleanText($oCProfile->GetTitle());
				$oSolrDocument->desc_short = SolrIndexer::cleanText($oCProfile->GetDescShort());
				$oSolrDocument->desc_long = SolrIndexer::cleanText($oCProfile->GetDescLong());
		
				$oSolrDocument->category_id = $oCProfile->GetCategoryArray();
				$oSolrDocument->activity_id = $oCProfile->GetActivityArray();
				$oSolrDocument->country_id = $oCProfile->GetCountryArray();
				$oSolrDocument->continent_id = $oCProfile->GetContinentArray();
		
		
				$oSolrDocument->category = $oCProfile->GetCategoryTxtArray();
				$oSolrDocument->activity = $oCProfile->GetActivityTxtArray();
				$oSolrDocument->country =  $oCProfile->GetCountryTxtArray();
				$oSolrDocument->continent =  $oCProfile->GetContinentTxtArray();
				$oSolrDocument->region = $oCProfile->GetLocation();
				$oSolrDocument->state = $oCProfile->GetStateName();
					
				$oSolrDocument->text = '';
					
				// NOW INDEX PROFILE SPECIFIC FIELDS
				if ((
						($oCProfile instanceof SummerCampProfile) ||
						($oCProfile instanceof SeasonalJobEmployerProfile) ||
						($oCProfile instanceof VolunteerTravelProjectProfile)
				)  && (is_numeric($oCProfile->GetDurationFromId())))
				{
					// index durations in weeks
					$oSolrDocument->duration_from = $this->aDuration2DaysMapping[$oCProfile->GetDurationFromId()];
					$oSolrDocument->duration_to = $this->aDuration2DaysMapping[$oCProfile->GetDurationToId()];
						
				}
		
				if ((
						($oCProfile instanceof SummerCampProfile) ||
						($oCProfile instanceof VolunteerTravelProjectProfile)
				)  && (is_numeric($oCProfile->GetCurrencyId())))
				{
		
					$oCProfile->SetCostsRefdataObject($this->oPrice);
					$oCProfile->SetCurrencyRefdataObject($this->oCurrency);
						
					$ratetousd = $this->aCurrencyTypeMapping[$oCProfile->GetCurrencyId()][1];
		
					// index price in USD only, strip + < symbols to allow range lookups
					$fromPrice = preg_replace("/[^0-9]/", "", $oCProfile->GetCostsFromLabel());
					$toPrice = preg_replace("/[^0-9]/", "", $oCProfile->GetCostsToLabel());

					if (is_numeric($fromPrice) && is_numeric($toPrice))
					{
					   $oSolrDocument->price_from = $fromPrice * $ratetousd;
					   $oSolrDocument->price_to = $toPrice * $ratetousd;
					}
						
				}
					
					
				if ($profile_type == PROFILE_VOLUNTEER_PROJECT) {
						
					$oOrgType = new Refdata(REFDATA_ORG_PROJECT_TYPE);
					$oOrgType->GetByType();
					$oCProfile->SetOrgTypeRefdataObject($oOrgType);
						
					$oSolrDocument->org_type = $oCProfile->GetOrgTypeLabel();
					$oSolrDocument->species = $oCProfile->GetSpeciesLabels();
					$oSolrDocument->habitats = $oCProfile->GetHabitatsLabels();
		
					$oSolrDocument->text .= SolrIndexer::cleanText($oCProfile->GetSupport())." ";
					$oSolrDocument->text .= SolrIndexer::cleanText($oCProfile->GetSafety())." ";
					$oSolrDocument->text .= SolrIndexer::cleanText($oCProfile->GetAwards())." ";
		
				}
		
				if ($profile_type == PROFILE_SUMMERCAMP) {
		
		
					$oSolrDocument->camp_type = $oCProfile->GetCampTypeLabels();
					$oSolrDocument->camp_gender = $oCProfile->GetCampGenderLabel();
					$oSolrDocument->camper_age_from = $oCProfile->GetCamperAgeFromLabel("INT");
					$oSolrDocument->camper_age_to = $oCProfile->GetCamperAgeToLabel("INT");
					$oSolrDocument->camp_religion = $oCProfile->GetCampReligionLabel();
					$oSolrDocument->camp_activities = $oCProfile->GetCampActivityLabels();
					$oSolrDocument->camp_job_types = $oCProfile->GetCampJobTypeLabels();
		
						
				}
					
				if ($profile_type == PROFILE_TEACHING) {
		
					$oSolrDocument->text .= $oCProfile->GetSalary()." ";
					$oSolrDocument->text .= $oCProfile->GetBenefits()." ";
					$oSolrDocument->text .= $oCProfile->GetQualifications()." ";
					$oSolrDocument->text .= $oCProfile->GetRequirements()." ";
					$oSolrDocument->text .= $oCProfile->GetHowToApply()." ";
				}
					
				if ($profile_type == PROFILE_COMPANY) {
						
					$oSolrDocument->text .= SolrIndexer::cleanText($oCProfile->GetDuration())." ";
					$oSolrDocument->text .= SolrIndexer::cleanText($oCProfile->GetCosts())." ";
					$oSolrDocument->text .= SolrIndexer::cleanText($oCProfile->GetPlacementInfo())." ";
		
				}

				if ($this->debug) var_dump($oSolrDocument);

				try {	
					$update->addDocument($oSolrDocument);
		
					if (($commitIdx == $commitEvery) || ($idx == $total_company)) {
						$update->addCommit();
						$result = $client->update($update);
						$commitIdx = 0;
		
						// set the last_indexed date
						$db->query("UPDATE company SET last_indexed_solr = now()::timestamp WHERE id IN (".implode(",",$aIdBatch).")");
						
		
						if (LOG) Logger::DB(2,JOBNAME,'SOLR QUERY EXECUTED OK status: '.$result->getStatus() .", time: ".$result->getQueryTime());
		
						unset($update);
						$update = $client->createUpdate();
						$aIdBatch = array();
							
		
					} else {
						$commitIdx++;
					}
                                } catch (Exception $e) {
                                        if (LOG) Logger::DB(1,JOBNAME,'EXCEPTION: '.$e->getMessage());
                                        if (LOG) Logger::DB(1,JOBNAME,Logger::var_dump_ret($oSolrDocument));
                                }

		
				unset($aProfile);
				unset($oCProfile);
	
			}
		}
		
		if (LOG) Logger::DB(2,JOBNAME,'FINISHED PROCESSING COMPANY PROFILES ('.$idx.' of '.$total_company.')');
		
	}
	
	public function indexPlacement() {
		
	    global $db,$solr_config;
		
		$aId = $this->getId();
		
		$total_placements = count($aId);
		
		if (LOG) Logger::DB(2,JOBNAME,"FOUND ".$total_placements." PLACEMENTS TO INDEX");
		
		
		// create a SOLR client instance
		$client = new Solarium\Client($solr_config);
		$client->getEndpoint('localhost')->setCore('collection1');
		$update = $client->createUpdate();
		
		$idx = 0;
		$commitEvery = 20;
		$commitIdx = 0;
		
		$aIdBatch = array();
		
		
		if (count($aId) >= 1) {
			foreach ($aId as $a) {
		
				$idx++;
				
				$oProfile = ProfileFactory::Get($a['type']);
				$oProfile->GetById($a['id']);
		
				if (LOG) Logger::DB(3,JOBNAME,"PROCESSING PLACEMENT id: ".$a['id']." (".$idx." of ".$total_placements.")");
		
		
				if (!is_numeric($oProfile->GetId())) {
					if (LOG) Logger::DB(1,JOBNAME,"ERROR: FAILED TO FETCH PLACEMENT id : ".$a['id']);
					continue;
				}
					
				$aIdBatch[] = $a['id'];
					
		
				$oSolrDocument = $update->createDocument();
					
				// INDEX GENERIC FIELDS COMMON TO ALL PROFILES
				$oSolrDocument->id = $oProfile->GetOid();
				$oSolrDocument->profile_id = $oProfile->GetId();
				$oSolrDocument->profile_type = $oProfile->GetGeneralType(); // only interested in 0 = COMPANY PROFILE, 1 = PLACEMENT
				$oSolrDocument->company_id = $oProfile->GetCompanyId();
				$oSolrDocument->company_name = SolrIndexer::cleanText($oProfile->GetCompanyName());
				$oSolrDocument->title = SolrIndexer::cleanText($oProfile->GetTitle());
				$oSolrDocument->desc_short = SolrIndexer::cleanText($oProfile->GetDescShort());
				$oSolrDocument->desc_long = SolrIndexer::cleanText($oProfile->GetDescLong());

				$oSolrDocument->prod_type = 5; // set placements as > sponsored listing
				$oSolrDocument->active = ($oProfile->IsAdActiveDisplayInSearch()) ? 1 : 0;

				$oSolrDocument->category_id = $oProfile->GetCategoryArray();
				$oSolrDocument->activity_id = $oProfile->GetActivityArray();
				$oSolrDocument->country_id = $oProfile->GetCountryArray();
				$oSolrDocument->continent_id = $oProfile->GetContinentArray();
		
		
				$oSolrDocument->category = SolrIndexer::cleanArray($oProfile->GetCategoryTxtArray());
				$oSolrDocument->activity = SolrIndexer::cleanArray($oProfile->GetActivityTxtArray());
				$oSolrDocument->country =  SolrIndexer::cleanArray($oProfile->GetCountryTxtArray());
				$oSolrDocument->continent =  SolrIndexer::cleanArray($oProfile->GetContinentTxtArray());
				$oSolrDocument->region =  SolrIndexer::cleanArray($oProfile->GetLocation());
					
				//getFacetDataByProfileId($id,$type)
				
				$oSolrDocument->text = '';
		
				if (($oProfile instanceof TourProfile) || ($oProfile instanceof GeneralProfile)) {
					//$oProfile->SetDurationRefdataObject($this->oDuration);
					$oProfile->SetCostsRefdataObject($this->oPrice);
					$oProfile->SetCurrencyRefdataObject($this->oCurrency);
		
					// index durations in days
					$oSolrDocument->duration_from = $this->aDuration2DaysMapping[$oProfile->GetDurationFromId()];
					$oSolrDocument->duration_to = $this->aDuration2DaysMapping[$oProfile->GetDurationToId()];
						
					$ratetousd = $this->aCurrencyTypeMapping[$oProfile->GetCurrencyId()][1];
						
					// index price in USD only, strip + < symbols to allow range lookups
					$fromPrice = preg_replace("/[^0-9]/", "", $oProfile->GetPriceFromLabel());
					$toPrice = preg_replace("/[^0-9]/", "", $oProfile->GetPriceToLabel());
		
					$oSolrDocument->price_from = $fromPrice * $ratetousd;
					$oSolrDocument->price_to = $toPrice * $ratetousd;

					if ($this->bExtraFacetDataEnabled) {
						$oSolrDocument->species = $this->getFacetDataByProfileId($oProfile->GetId(),'SPECIES');
						$oSolrDocument->habitats = $this->getFacetDataByProfileId($oProfile->GetId(),'HABITATS');
					}						
				}
		
				if ($oProfile instanceof GeneralProfile) {
						
					// Start Dates
					$oSolrDocument->text .= $oProfile->GetStartDates()." ";
					// Costs / Salary / Benefits
					$oSolrDocument->text .= $oProfile->GetBenefits()." ";
					// Requirements
					$oSolrDocument->text .= $oProfile->GetRequirements()." ";
						
				}
					
				if ($oProfile instanceof TourProfile) {
						
					$oSolrDocument->text .=  $oProfile->GetDates();
					$oSolrDocument->text .=  SolrIndexer::cleanText($oProfile->GetItinery());
					$oSolrDocument->text .= $oProfile->GetPrice();
					$oSolrDocument->transport = $oProfile->GetTransportLabels();
					$oSolrDocument->accomodation = $oProfile->GetAccomodationLabels();
					$oSolrDocument->meals = $oProfile->GetMealsLabels();
					$oSolrDocument->text .= $oProfile->GetRequirements();
						
				}

				$oSolrDocument->text = SolrIndexer::cleanText($oSolrDocument->text);
					
				if ($oProfile instanceof JobProfile) {
						
				}

				if ($this->debug) var_dump($oSolrDocument);	

				try {			
					$update->addDocument($oSolrDocument);
					
					if (($commitIdx == $commitEvery) || ($idx == $total_placements)) {
						$update->addCommit();
						$result = $client->update($update);
						$commitIdx = 0;
						
						// set the last_indexed date
						$db->query("UPDATE profile_hdr SET last_indexed_solr = now()::timestamp WHERE id IN (".implode(",",$aIdBatch).")");
						
						if (LOG) Logger::DB(2,JOBNAME,'SOLR QUERY EXECUTED OK status: '.$result->getStatus() .", time: ".$result->getQueryTime());
						
						unset($update);
						$update = $client->createUpdate();
						$aIdBatch = array();
						
					} else {
						$commitIdx++;
					}
                                } catch (Exception $e) {
                                        if (LOG) Logger::DB(1,JOBNAME,'EXCEPTION: '.$e->getMessage());
                                        if (LOG) Logger::DB(1,JOBNAME,Logger::var_dump_ret($oSolrDocument));
                                }
	
	
				unset($aProfile);
			
					
			}
		}
		
		if (LOG) Logger::DB(2,JOBNAME,'FINISHED PROCESSING PLACEMENT PROFILES ('.$idx.' of '.$total_placements.')');
		
	}
	
	
	/*
	 * Implements a kind of post-index classifier
	 * 
	 * For a set of facets (species, habitats), return matching profiles
	 * Re-index these profiles adding facet(s) as facet fields
	 * 
	 */
	public function reindexPlacementWithExtras() {
		
		$oSpecies = new Refdata(REFDATA_SPECIES);
		$oSpecies->SetOrderBySql(' sort_order ASC');
		$oSpecies->GetByType();
		
		$query = "";
		$fq = array();
		$sort = array();
		
		$fq['profile_type'] = 1;
		
		$limit = 5;
		$i = 0;
		
		$aFacetSpecies = $this->getDocumentsMatchingFacet($oSpecies->GetValues(),$key = 'SPECIES', $fq, $sort);
		
		
		$oHabitats = new Refdata(REFDATA_HABITATS);
		$oHabitats->SetOrderBySql(' sort_order ASC');
		$oHabitats->GetByType();
		
		$aFacetHabitats = $this->getDocumentsMatchingFacet($oHabitats->GetValues(),$key = 'HABITATS', $fq, $sort);
		
		
		$aResult = array();
		
		// merge the results
		foreach($aFacetSpecies as $id => $a) {
			foreach($a as $k => $v) {
				$aResult[$id]['SPECIES'] = $v;
			}
		}
		foreach($aFacetHabitats as $id => $a) {
			foreach($a as $k => $v) {
				$aResult[$id]['HABITATS'] = $v;
			}
		}
		
		$this->aExtraFacetData = $aResult;
		
		$this->bExtraFacetDataEnabled = TRUE;
		
		$this->indexPlacement();
		
	}

	public function indexArticle() {
	
	    global $db,$solr_config;
	
		// get article id's for indexing
		$aId = $this->getId();
	
		$total_article = count($aId);
	
		if (LOG) Logger::DB(2,JOBNAME,'FOUND '.count($aId)." ARTICLES TO INDEX");
	
		// create a SOLR client instance
		$client = new Solarium\Client($solr_config);
		$client->getEndpoint('localhost')->setCore('collection1');
		$update = $client->createUpdate();
	
		$idx = 0;
		$commitEvery = 20;
		$commitIdx = 0;
	
		$aIdBatch = array();
	
		if (count($aId) >= 1) {
			foreach ($aId as $a) {
	
				$idx++;
				
				if (LOG) Logger::DB(2,JOBNAME,"PROCESSING ARTICLE ID : ".$a['id'] ." (".$idx." of ".$total_article.")");
	
				$oArticle = new Article();
				$oArticle->GetFetchMode() == FETCHMODE__FULL;
				$oArticle->GetById($a['id']);
							
				if (!is_numeric($oArticle->GetId())) {
					if (LOG) Logger::DB(1,JOBNAME,"ERROR: FAILED TO FETCH ARTICLE  id : ".$a['id']);
					continue;
				}
	
				$aIdBatch[] = $a['id'];
					
					
				$oSolrDocument = $update->createDocument();
					
				$oSolrDocument->id = $oArticle->GetId();
				$oSolrDocument->profile_id = $oArticle->GetId();
				$oSolrDocument->profile_type = 2; // 0 = COMPANY PROFILE, 1 = PLACEMENT, 2 = ARTICLE
				$oSolrDocument->title = SolrIndexer::cleanText($oArticle->GetTitle());
				$oSolrDocument->desc_short = SolrIndexer::cleanText($oArticle->GetDescShort());
				$oSolrDocument->desc_long = SolrIndexer::cleanText($oArticle->GetDescFull());
				
				$oSolrDocument->active = 1;

				$aMapping = $oArticle->GetMappingBySiteId(0);

				$url = $oArticle->GetUrl();
				$tags = array();
								
				if (count($aMapping) < 1 || strpos($url, "article.php") !== FALSE)
				    $oSolrDocument->active = 0;

				$uri = explode("/",$url);
    
				//$oSolrDocument->section_uri = $uri;

				$strHostName = $uri[2];

				// unset hostname
				unset($uri[0]); //  http:
				unset($uri[1]); // //
				unset($uri[2]); // www.onewold365.org

				
				foreach($uri as $str) {
					if (strpos($str, "-") != -1) {
						$words = explode("-",$str);
						$tags = array_merge($tags,$words);
					} else {
						$tags[] = $str;
					}	
				}
				$oIndexer = new Indexer($db);
				
				$oSolrDocument->tags = $oIndexer->removeStopWordsFromArray($tags);

				$oSolrDocument->website_id = ($strHostName == "www.oneworld365.org") ? 0 : 1;
				
				$oSolrDocument->last_updated = gmdate('Y-m-d\TH:i:s\Z', strtotime($oArticle->GetLastUpdated()));
						
				$oSolrDocument->text = SolrIndexer::cleanText(implode(" ",$oSolrDocument->tags) . ' ' . $oArticle->GetTitle(). " " . $oArticle->GetDescShort() . " " . $oArticle->GetDescFull());
			
				if (LOG) Logger::DB(2,JOBNAME,"PROCESSING ARTICLE : ".$oSolrDocument->title);

				if ($this->debug) var_dump($oSolrDocument);

				$update->addDocument($oSolrDocument);

				if (($commitIdx == $commitEvery) || ($idx == $total_article)) {
					$update->addCommit();
					$result = $client->update($update);
					$commitIdx = 0;
	
					// set the last_indexed date
					$db->query("UPDATE article SET last_indexed_solr = now()::timestamp WHERE id IN (".implode(",",$aIdBatch).")");
	
	
					if (LOG) Logger::DB(2,JOBNAME,'SOLR QUERY EXECUTED OK status: '.$result->getStatus() .", time: ".$result->getQueryTime());
	
					unset($update);
					$update = $client->createUpdate();
					$aIdBatch = array();
	
	
				} else {
					$commitIdx++;
				}
		
			}
		}
	
		if (LOG) Logger::DB(2,JOBNAME,'FINISHED PROCESSING ARTICLES ('.$idx.' of '.$total_article.')');
	
	}
	
	
	public function getDocumentsMatchingFacet($facet,$key, $fq, $sort) {
		
		global $solr_config;
		
		$aResult = array();
		
		foreach($facet as $value) {
		
			$query = SolrSearch::SolrQueryCharSafe(str_replace("-"," ",$value));
			$query = SolrSearch::removeWords(array('Species'),$query);

			$words = explode(" ",$query);
			$query = implode(" AND ", $words);
			
			$iRows = 1000;
			$iStart = 0;
		
			$oSolrSearch = new SolrSearch($solr_config);
			$oSolrSearch->setRows($iRows);
			$oSolrSearch->setStart($iStart);
			$oSolrSearch->search($query,$fq,$sort,$fields = array('profile_id'));
			$oSolrSearch->processResult();
		
			$aProfileId = $oSolrSearch->getId();
			
			if (is_array($aProfileId) && count($aProfileId) > 1) {
				foreach($aProfileId as $id) {
					if ($this->inIndexSet($id)) { // only interested in profiles in index set
						$aResult[$id][$key][] = $value;
					}
				}
			}
		}
		
		return $aResult;
	}
	
	private function getFacetDataByProfileId($id,$type) {
		if (array_key_exists($id,$this->aExtraFacetData)) {
			if (array_key_exists($type,$this->aExtraFacetData[$id])) {
				return $this->aExtraFacetData[$id][$type];
			}
		}
	}

	private function inIndexSet($id) {
		if (count($this->getId()) >= 1) {
			foreach ($this->getId() as $a) {
				if ($id == $a['id']) return TRUE;
			}
		}
	}
	
	public static function cleanText($str){
		
	    $str = html_entity_decode(strip_tags($str), ENT_QUOTES, 'utf-8');
		$str = str_replace(array("\n"), ' ', $str);
		$str = str_replace(array("\t", "\r"), '', $str);
		//$str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
		$str = preg_replace("/[^a-zA-Z0-9 ]/", " ", $str);
		return SolrIndexer::luceneEscape($str);
	}


	public static function luceneEscape($query) {
		$luceneReservedCharacters = preg_quote('+-&|!(){}[]^"~*?:\\');
		return preg_replace_callback(
		    '/([' . $luceneReservedCharacters . '])/',
		    function($matches) {
		        return '\\' . $matches[0];
		    },
		    $query);
	}

	public function cleanArray($arr) {

		if (!is_array($arr)) return SolrIndexer::cleanText($arr);

		$tmp = array();

		foreach($arr as $k => $v) {

			$tmp[] = SolrIndexer::cleanText($v);
		}

		return $tmp;

	}

	public function filter($str) {

                $str = html_entity_decode(strip_tags($str), ENT_QUOTES, 'utf-8');
                $str = strip_tags(preg_replace("/&#?[a-z0-9]+;/i"," ",$str));#
                $str = str_replace(array("\n"), ' ', $str);
                $str = str_replace(array("\t", "\r"), '', $str);
                return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');

	}

	
}
