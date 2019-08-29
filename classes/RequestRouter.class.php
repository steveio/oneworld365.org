<?php




class RequestRouter {

	protected $aRequestUri;
	protected $strRequestPageType; // { ARTICLE, ACTIVITY, CATEGORY, SEARCH etc }

	public function __Construct() {}
	
	public function SetRequestUri($aRequestUri)
	{
		if (is_array($aRequestUri)) {
			$this->aRequestUri = $aRequestUri;
		
		} else {
			redirect();
		}

	}
	
	public function Route()
	{
		global $db, $_CONFIG,$aBreadCrumb;

		/* route request */
		switch ($this->aRequestUri[1]) {
		
		
			case "" : /* homepage */
			case "homepage-new" :
				require_once("home.php");
				break;

			case "article.php":
				redirect();
				break;
	
			case "contact-us" :
			case "contact" :
				require_once("contact.php");
				break;
		
			case "enquiry-report" :
				require_once("enquiry_report.php");
				break;
		

			case "enquiry" :
				require_once("enquiry.php");
				break;

			case "review" :
				require_once("review.php");
				break;
		
			case "login" :
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: http://admin.oneworld365.org/login" );
				die();
				break;
		
			case "logout" :
				require_once("logout.php");
				break;
		
			case "password" :
				require_once("password.php");
				break;
		
			case "send-to-friend" :
				require_once("send_to_friend.php");
				break;
		
			case "user" : /* user */
				require_once("user.php");
				break;
		
			case "approve" :
				require_once("approve.php");
				break;
		
			case "search" :
				if ($this->aRequestUri[2] != "") {
		
					$this->validateUriNamespaceIdentifier($this->aRequestUri[2]);
					$_REQUEST['page_title'] = $this->aRequestUri[2];
					$_REQUEST['cat_name'] = $this->aRequestUri[2];
					$_REQUEST['cat'] = "search";
					require_once("./search_result.php");
				} else {
					require_once("./header_html.php");
					require_once("./search_panel.php");
					require("./footer.php");
				}
				break;
		
			case "search-dispatch" :
				require_once("./search_panel_dispatch.php");
				break;
		
		
			case "country" :
			case "continent" :
			case "travel" :
				if ($this->aRequestUri[2] != "") {
					if (strtolower($this->aRequestUri[1]) == "travel")
						$this->aRequestUri[1] = "country";

					$this->validateUriNamespaceIdentifier($this->aRequestUri[2]);
					$this->validateUriNamespaceIdentifier($this->aRequestUri[1]);
					$sql = "SELECT id,name,url_name FROM ".$this->aRequestUri[1]." WHERE url_name = '".$this->aRequestUri[2]."'";
					$db->query($sql);
					if ($db->getNumRows() == 1) {
						$aRes = $db->getRow();		
		
						$_REQUEST['cat'] = $this->aRequestUri[1];
						$_REQUEST['cat_name'] = $aRes['name'];
						$_REQUEST['cat_url_name'] = $aRes['url_name'];
						$_REQUEST['sub_cat'] = isset($this->aRequestUri[3]) ? $this->aRequestUri[3] : "";
						$_REQUEST['id'] = $aRes['id'];
						$_REQUEST['clean_url'] = $_CONFIG['url']."/".$this->aRequestUri[1]."/".$this->aRequestUri[2];
						if (strlen($this->aRequestUri[3]) > 1) { /* append /<country>/[travel-tour||volunteer] etc  */
							$_REQUEST['clean_url'] = $_REQUEST['clean_url']."/".$this->aRequestUri[3];
						}
						$_REQUEST['page_title'] = $_CONFIG['txt_pattern_generic'] . " in " . trim($aRes['name']);
						$_REQUEST['page_meta_description'] = $_CONFIG['page_description']." ".$_CONFIG['site_title']." listings for '".$_CONFIG['txt_pattern_generic'] ." in ". trim($aRes['name'])."'. ".$_CONFIG['txt_pattern_generic'] . " in " . trim($aRes['name']).". " . $aRes['name']." ".trim($_CONFIG['txt_pattern_generic']) .".  Find " . $_CONFIG['txt_pattern_generic'] ." in ". $aRes['name'] .".";

						$this->strRequestPageType = "DESTINATION";
				        return $this->ProcessArticlePageRequest();
	
						break;
					} else {
						redirect();
					}
				} else {
                    require_once("./header_html.php");
                    require_once("./search_panel.php");
                    require("./footer.php");
                    break;
				}
				break;
		
		
			case "company" :
		
				// view placement
				if (($this->aRequestUri[3] != "") && ($this->aRequestUri[3] != "placements") && ($this->aRequestUri[3] != "edit") && ($this->aRequestUri[2] != "a-z")) {
						
					$this->validateUriNamespaceIdentifier($this->aRequestUri[2]);
					$this->validateUriNamespaceIdentifier($this->aRequestUri[3]);
					$sql = "SELECT p.id,p.title, p.desc_short FROM ".$_CONFIG['placement_table']." p, ".$_CONFIG['company_table']." c WHERE p.url_name = '".$this->aRequestUri[3]."' AND c.url_name = '".$this->aRequestUri[2]."' AND p.company_id = c.id";
					$db->query($sql);
					if ($db->getNumRows() == 1) {
							
						if ($this->aRequestUri[4] == "edit") {
							$url = preg_replace("/www/","admin",$_CONFIG['url']);
							header("Location: ".$url.$_SERVER['REQUEST_URI']);
							die();
						}
							
							
						$aRes = $db->getRow();
		
						// get company info
						$sql = "SELECT id,title FROM ".$_CONFIG['company_table']." WHERE url_name = '".$this->aRequestUri[2]."'";
						$db->query($sql);
						if ($db->getNumRows() == 1) {
							$aResComp = $db->getRow();
						}
						$_REQUEST['id'] = $aRes['id'];
						$_REQUEST['m'] = 'view';
						$_REQUEST['page_title'] = stripslashes($aRes['title']) ." with " . trim(stripslashes($aResComp['title']));
						$_REQUEST['page_meta_description'] = cleanMetaDesc($aRes['desc_short']);
						$_REQUEST['page_keywords'] = trim($aRes['title']). ",". $aResComp['title'];
		
						require_once("./placement.php");
		
						break;
					} else {
						redirect();
					}
				}
					
					
		
				// view all placements
				if ($this->aRequestUri[3] == "placements") {
		
					$this->validateUriNamespaceIdentifier($this->aRequestUri[2]);
					$sql = "SELECT id,title FROM ".$_CONFIG['company_table']." WHERE url_name = '".$this->aRequestUri[2]."'";
					$db->query($sql);
					if ($db->getNumRows() == 1) {
						$aRes = $db->getRow();
						$_REQUEST['id'] = $aRes['id'];
						$_REQUEST['m'] = 'view';
		
						$_REQUEST['page_title'] = $_CONFIG['txt_pattern_generic'] . " with " . stripslashes($aRes['title']);
						$_REQUEST['page_keywords'] = trim($aRes['title']). " " . $_CONFIG['txt_pattern_generic'] .",". $_CONFIG['txt_pattern_generic']." with ". $aRes['title'].",". $aRes['title'] .",".$_CONFIG['page_keywords'];
						$_REQUEST['page_meta_description'] = $_CONFIG['page_description']." ". $_CONFIG['txt_pattern_generic'] . " : " . stripslashes($aRes['title']) ." with " . stripslashes($aResComp['title']);
		
						require_once("./placement_all.php");
		
						break;
		
					} else {
						redirect();
					}
						
				}
		
				if (($this->aRequestUri[2] != "") && ($this->aRequestUri[2] != "a-z")) { // view company
		
				$this->validateUriNamespaceIdentifier($this->aRequestUri[2]);
				$sql = "SELECT id,title, desc_short FROM ".$_CONFIG['company_table']." WHERE url_name = '".$this->aRequestUri[2]."'";
				$db->query($sql);
				if ($db->getNumRows() == 1) {
						
					if ($this->aRequestUri[3] == "edit") {
						$url = preg_replace("/www/","admin",$_CONFIG['url']);
						header("Location: ".$url.$_SERVER['REQUEST_URI']);
						die();
					}
		
					$aRes = $db->getRow();
					$_REQUEST['id'] = $aRes['id'];
					$_REQUEST['m'] = 'view';
		
					$_REQUEST['page_title'] = $aRes['title'];
					$_REQUEST['page_meta_description'] = cleanMetaDesc($aRes['desc_short']);
					$_REQUEST['page_keywords'] = "";
		
					require_once("./company.php");
				} else {
					redirect();
				}
				break;
				}
		
				// display company list
				// are we retrieving an a-z list?
		
					if ($this->aRequestUri[2] == "a-z" && $this->aRequestUri[3] != "") {
						$this->isLowerCaseLetter($this->aRequestUri[3]);
						$_REQUEST['letter'] = $this->aRequestUri[3];
					} else {
						$_REQUEST['letter'] = "a";
					}
					require_once("./company_list.php");
		
				break;
		
		
			default :
				/**
				 * Activity, Category or Article Page 
				 */
				$this->GetPageTypeFromUri();

				die();
		
		}
		
	}
	
	public static function isCategory($strUrlName)
	{
		global $db;

		$sql = "SELECT 1 FROM category WHERE url_name = '".$strUrlName."'";
		$db->query($sql);
		
		return ($db->getNumRows() == 1) ? true : false; 
	}

	public static function isActivity($strUrlName)
	{
		global $db;
	
		$sql = "SELECT 1 FROM activity WHERE url_name = '".$strUrlName."'";
		$db->query($sql);
	
		return ($db->getNumRows() == 1) ? true : false;
	}

	public function GetPageTypeFromUri()
	{
		global $db;

		$this->validateUriNamespaceIdentifier($this->aRequestUri[1]);
		
		if (RequestRouter::isCategory($this->aRequestUri[1]))
		{
			$sql = "SELECT id,name FROM category WHERE url_name = '".$this->aRequestUri[1]."'";
			$db->query($sql);
			if ($db->getNumRows() == 1) {
				$this->strRequestPageType = "CATEGORY";
				$aRes = $db->getRow();
				$this->ProcessCategoryPageRequest($aRes);
				return true;
			}
		} elseif (RequestRouter::isActivity($this->aRequestUri[1])) {
		
			$sql = "SELECT id,name FROM activity WHERE url_name = '".$this->aRequestUri[1]."'";
			$db->query($sql);
			if ($db->getNumRows() == 1) {
				$this->strRequestPageType = "ACTIVITY";
				$aRes = $db->getRow();
				$this->ProcessActivityPageRequest($aRes);
				return true;
			}

		} else {
			$this->ProcessArticlePageRequest();
		}
	}

	protected function ProcessActivityPageRequest($aRes)
	{
		global $_CONFIG;

		$_REQUEST['cat'] = "activity";
		$_REQUEST['cat_name'] = $aRes['name'];
		$_REQUEST['id'] = $aRes['id'];
		$_REQUEST['clean_url'] = $_CONFIG['url']."/".$this->aRequestUri[1];
		
		$_REQUEST['page_meta_description'] = trim($_CONFIG['txt_pattern_generic']) ." ". $aRes['name']. ". " .$aRes['description'];
		
		return $this->ProcessArticlePageRequest();

	}

	protected function ProcessCategoryPageRequest($aRes)
	{
		global $db, $_CONFIG;

		$_REQUEST['cat'] = "category";
		$_REQUEST['cat_name'] = $aRes['name'];
		$_REQUEST['id'] = $aRes['id'];
		$_REQUEST['clean_url'] = $_CONFIG['url'].$this->aRequestUri[1];

		return $this->ProcessArticlePageRequest();		
	}

	protected function ProcessArticlePageRequest()
	{
		global $_CONFIG;

		$sUri = $section_uri = $this->aRequestUri[0];
		
		if (!isset($this->strRequestPageType) )
			$this->strRequestPageType = 'ARTICLE';

		if (preg_match("/\/article\//",$sUri)) {
			$sUri = preg_replace("/\/article/","",$sUri);
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: ".$_CONFIG['url'].$sUri );
			die();
		}
		$oArticle = new Article;
		if (in_array($section_uri,array("/news","/features","/blog"))) $oArticle->SetFetchMode(FETCHMODE__SUMMARY);

	        /*
        	 * Setup template display options for search panel and UI elements
         	*/
        	if (!in_array($this->strRequestPageType, array("ACTIVITY", "CATEGORY","DESTINATION")))
            		$oArticle->SetDefaultMappingOption(false);
		
		if (!$oArticle->Get($_CONFIG['site_id'],$section_uri)) {
		    // Some URIs eg /<activity>/<country> may not have CMS article created yet
		    // if all URI segments map to valid namespace identifiers 
		    // display in generic search result template
		    if ($this->isNamespaceMatchedURL())
		    {
		        $_REQUEST['cat_name'] = $this->aRequestUri[1]. " " .$this->aRequestUri[2];
		        $_REQUEST['cat'] = "search";
		        require_once("./search_result.php");
                die();
		    } else { // 404 not found
                redirect();
                die();
		    }
		}

		$_REQUEST['page_title'] = $oArticle->GetTitle();
		$_REQUEST['page_url'] = $_CONFIG['url'] .$sUri;
	
		if (strlen($oArticle->GetMetaDesc()) > 1) {
			$_REQUEST['page_meta_description'] = $oArticle->GetMetaDesc();
		} else {
			$_REQUEST['page_meta_description'] = $oArticle->GetDescShort();
		}

		$_REQUEST['page_meta_description'] = trim($oArticle->cleanText($_REQUEST['page_meta_description']));
		
		if (strlen($oArticle->GetMetaKeywords()) > 1) {
			$_REQUEST['page_keywords'] = $oArticle->GetMetaKeywords();
		} else {
			$_REQUEST['page_keywords'] = "";
		}

		// get and set primary image for display as og:image property
		$_REQUEST['page_imageUrl'] = $oArticle->GetDisplayImageUrl();	
		
		$arrDisplayRelatedFilter = array(
		    '/blog',
		    '/advertising',
		    '/about-us',
		    '/company',
		    '/contact',
		    '/reviews',
	        '/privacy',
		    '/one-world-365-job-opportunities',	
	        '/blog/become-a-travel-writer-for-one-world-365',
		    '/responsible-travel',
		    '/social-media-pages',
		    '/advertising-volunteer',
		    '/advertising',
		    '/advertising-2018',
		    '/advertising-recruitment-2018',
		    '/advertising-options-travel-tours-2018',
		    '/advertising-study-abroad-2018',
		    '/advertising-options-volunteer-2018',
		    '/advertising-internships-2018',
		    '/advertising-tefl-2018',
		    '/advertising-options-language-schools-2018',
		    '/advertising-options-scuba-diving-2018',
		    '/advertising-camp-directors-2018',
		    '/advertising-summer-camp-jobs-2018',
		    '/advertising-tours-2018',
		    '/advertising-volunteering-2018',
		    '/advertising-accommodation-2018',
		    '/advertising-social-media'
		);

    	if(!in_array($sUri, $arrDisplayRelatedFilter))
    	{

            global $solr_config;
            /* SOLR Search Engine; */
            require_once(ROOT_PATH."/classes/SolrSearch.php");
            require_once(ROOT_PATH."/classes/SolrMoreLikeSearch.php");
            require_once(ROOT_PATH."/classes/BalancedDistributor.php");

    		/**
    		 * Get Related Profiles
    		 * @var Ambiguous $aProfile
    		 */
    		$aProfile = $oArticle->GetAttachedProfile();
    
    
    		if (is_array($aProfile) && (count($aProfile) < 1) ) {
    
    			// get some related placements
    			$oSolrMoreLikeSearch = new SolrMoreLikeSearch($solr_config);
    			$oSolrMoreLikeSearch->getPlacementsByArticleBalanced($oArticle->GetId());
    			$oSolrMoreLikeSearch->setRows(10);
    			$aProfileId = $oSolrMoreLikeSearch->getId();
    			
    			if (count($aProfileId) >= 1) {
    				$oArticle->SetAttachedProfile($fetch = FALSE,$aProfileId);
    			}
    		}
        
        	/**
    		 * Get Related Articles
    		 */
	    	$oSolrMoreLikeSearch = new SolrMoreLikeSearch($solr_config);	    
	    	$aFilterQuery = array();
	    	$aFilterQuery['-title'] = "365";
	    	$aFilterQuery['-desc_short'] = "365";
	    	$aFilterQuery['-title'] = "enquiry";
	    	$aFilterQuery['website_id'] = 0;

	    	$oSolrMoreLikeSearch->setRows(10);
	    	$aRelatedArticle = $oSolrMoreLikeSearch->getRelatedArticle($oArticle->GetId(),$aFilterQuery);
	    	$oArticle->GetArticleCollection()->Reset();
	    	
	    	foreach($aRelatedArticle as $oRelatedArticle)
	    	{
	        	$bFilter = false;

	        	foreach($oRelatedArticle->GetMapping() as $oMapping)
	        	{
	            		if (in_array($oMapping->GetSectionUri(),$arrDisplayRelatedFilter))
	                		$bFilter = true;
	        	}
	        	if (!$bFilter)
	            		$oArticle->GetArticleCollection()->Add($oRelatedArticle);
   	       }

        } // end get related	

        if(!in_array($sUri, $arrDisplayRelatedFilter))
        {
            require_once($_CONFIG['root_path']."/classes/review.class.php");
            $oReviews = new Review();
            $aReview = $oReviews->Get($oArticle->GetId(),'ARTICLE',1);
            $bHasReviewRating = false;
            $iReviewRating = 0;
            if (is_array($aReview) && count($aReview) >= 3)
            {
                $bHasReviewRating = true;
                foreach($aReview as $oReview)
                {
                    $iReviewRating += $oReview->GetRating();
                }
                $iReviewRating = floor($iReviewRating / count($aReview));
            }
            $oReviewTemplate = new Template();
            $oReviewTemplate->Set('LINK_TO', 'ARTICLE');
            $oReviewTemplate->Set('LINK_ID', $oArticle->GetId());
            $oReviewTemplate->Set('LINK_NAME', " ");
            $oReviewTemplate->Set('REVIEWS',$aReview);
            $oReviewTemplate->Set('REVIEWRATING',$iReviewRating);
            $oReviewTemplate->Set('HASREVIEWRATING',$bHasReviewRating);
            //$bHasReview = (is_array($aReview) && count($aReview) >= 1) ? true : false;
            $oReviewTemplate->Set('HAS_REVIEW',true);
            $oReviewTemplate->LoadTemplate("/comment.php");

        }

    	switch($sUri) {
    		case "/news" :
    			$template = "news_section_01.php";
    			break;
    		case "/features" :
    			$template = "features_section_01.php";
    			break;
    		case "/blog" :
    			$template = "blog.php";
    			break;
    		default :
    			$template = "article_01.php";
    	}


    	$aPageOptions = array();
    	
    	if (is_object($oArticle)) {
    	    // overide default content features if options were explicitly set during article publication
    	    if (is_object($oArticle->GetMappingBySectionUri($sUri))) {
    	        $aPageOptions = $oArticle->GetMappingBySectionUri($sUri)->GetOptions();
    	    }
    	}

	if ($aPageOptions[ARTICLE_DISPLAY_OPT_PLACEMENT] != 'f' || $aPageOptions[ARTICLE_DISPLAY_OPT_ORG] != 'f' )
	{
		$oSearchResultPanel = new Layout();
		$oSearchResultPanel->Set("URI",$sUri);
		$oSearchResultPanel->Set('ARTICLE_DISPLAY_OPT_PTITLE',$aPageOptions[ARTICLE_DISPLAY_OPT_PTITLE]);
		$oSearchResultPanel->Set('ARTICLE_DISPLAY_OPT_OTITLE',$aPageOptions[ARTICLE_DISPLAY_OPT_OTITLE]);
		$oSearchResultPanel->Set('ARTICLE_DISPLAY_OPT_PINTRO',$aPageOptions[ARTICLE_DISPLAY_OPT_PINTRO]);
		$oSearchResultPanel->Set('ARTICLE_DISPLAY_OPT_OINTRO',$aPageOptions[ARTICLE_DISPLAY_OPT_OINTRO]);

		$oSearchResultPanel->Set('ARTICLE_DISPLAY_OPT_PLACEMENT',$aPageOptions[ARTICLE_DISPLAY_OPT_PLACEMENT]);
		$oSearchResultPanel->Set('ARTICLE_DISPLAY_OPT_ORG',$aPageOptions[ARTICLE_DISPLAY_OPT_ORG]);
		$oSearchResultPanel->Set('ARTICLE_DISPLAY_OPT_SEARCH_KEYWORD',trim($aPageOptions[ARTICLE_DISPLAY_OPT_SEARCH_KEYWORD]));

		//$oSearchResultPanel->Set('HIDE_FILTERS',TRUE);
		$oSearchResultPanel->LoadTemplate('search_result.php');
		$oArticle->initTemplate();
		$oArticle->oTemplate->Set('oSearchResult',$oSearchResultPanel);
	} else {
		$oArticle->initTemplate();
	}

    	$oArticle->oTemplate->Set('oReview',$oReviewTemplate);
    	$oArticle->oTemplate->Set('aPageOptions',$aPageOptions);
 
    	$oArticle->LoadTemplate($template);
    	include("./header_html.php");
    	print $oArticle->Render();
    	include("./footer.php");
	
	}

	/**
	 * Check if all URI segments map to valid namespace identifiers  
	 * @return boolean
	 */
	public function isNamespaceMatchedURL()
	{
	    $aRequestUri = $this->aRequestUri;
	    array_shift($aRequestUri);

	    foreach($aRequestUri as $strKeyword)
	    {
	        if (strlen($strKeyword) < 1) continue;
	        
	        // in order of match probability
	        $arrIdentifier = array("activity","country","continent","category");

	        $bValid = false;

	        foreach($arrIdentifier as $strIdentifierKeyword)
	        {
	            try {
	                
	                $result = NameService::lookupNameSpaceIdentifier($strIdentifierKeyword,$strKeyword);

	                if (!isset($result['id']) || !is_numeric($result['id']))
	                {
	                    throw new ErrorException("Unmatched URI segment");
	                } else {
	                    $bValid = true;
	                }
	                
	            } catch (Exception $e) {}
	        }
	        if (!$bValid) return false;
	    }
	    return true;
	}

	public function isLowerCaseLetter($str)
	{
		if (!preg_match('/^[a-z]/',$str))
			redirect();
	}

	public function validateUriNamespaceIdentifier($str)
	{
		if (!preg_match('/[a-zA-Z0-9_\-\/]+/',$str) || strlen($str) > 120 )
			redirect();
	}
	
	
}


?>
