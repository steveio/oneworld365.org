<?php



define("CONTENT__ARTICLE",0);
define("CONTENT__SECTION",1);

/* allows fetching of full article or summary only */
define("FETCHMODE__FULL",0);
define("FETCHMODE__SUMMARY",1);

define("CK_EDITOR_INTRO_DT","2012-07-06 10:00:00");

// content type flags to signal what to display in template
define("ARTICLE_DISPLAY_OPT_PLACEMENT",0); // search results
define("ARTICLE_DISPLAY_OPT_ORG",1); // search result (depreciated)
define("ARTICLE_DISPLAY_OPT_ARTICLE",2);
define("ARTICLE_DISPLAY_OPT_PROFILE",14);
define("ARTICLE_DISPLAY_OPT_REVIEW",15);
define("ARTICLE_DISPLAY_OPT_SOCIAL",16);
define("ARTICLE_DISPLAY_OPT_ADS",17);
define("ARTICLE_DISPLAY_OPT_GADS",18);
define("ARTICLE_DISPLAY_OPT_IMG",19);

// keywords to drive search results
define("ARTICLE_DISPLAY_OPT_SEARCH_KEYWORD",3);
// user define titles for search results, news panels 
define("ARTICLE_DISPLAY_OPT_PTITLE",4);
define("ARTICLE_DISPLAY_OPT_OTITLE",5);
define("ARTICLE_DISPLAY_OPT_NTITLE",6);
define("ARTICLE_DISPLAY_OPT_PARENT_TABS",7);
define("ARTICLE_DISPLAY_OPT_PINTRO",8);
define("ARTICLE_DISPLAY_OPT_OINTRO",9);
// whether to show animated featured project
define("ARTICLE_DISPLAY_OPT_FEATURED_PROJECT",10);

// alignment of article body text { header | middle | footer }
define("ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_HEADER",11);
define("ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_BODY",12);
define("ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_FOOTER",13);



define ("DB__ARTICLE_TBL","article");
define ("DB__ARTICLE_MAP_TBL","article_map"); /* locations where article is published to */
define ("DB__ARTICLE_PROFILE_MAP_TBL","article_profile_map"); /* profiles associated with article */
define ("DB__ARTICLE_LINK_TBL","article_link"); /* other articles that are associated with this article */
define ("DB__ARTICLE_MAP_OPTS","article_map_opts");



/*
 * Abstract Base class for all content elements eg. articles, sections
 * 
 * Presently articles and sections are quite similar so most functionality exists in base class
 * 
 * However, we are likely to introduce more divergent content types in future
 *
 * 
 */

class Content  implements TemplateInterface {

	private $type; /* type of content being represented eg article, section */
	private $sTypeLabel;

	private $id;
	private $title;
	private $short_desc;
	private $full_desc;
	private $meta_desc;
	private $meta_keywords;
	private $created_by;
	private $created_date;
	private $published_status;
	private $published_date;
	public  $last_updated;
	public  $last_indexed_solr;
	private $url;

	private $aMapping; /* locations to which this content has been published */
	public  $bDefaultMappingOption = 't';
	private $aProfile; /* profiles (company || placements) that have been attached */
	public  $aImage; /* array of image objects that have been attached */ 
	private $aArticle; /* array of attached article id's */
	public 	$oArticleCollection; /* a collection of associated article objects */
	public  $oLinkGroup; /* a collection of links associated with article */

	public $fetch_mode; /* FETCHMODE__FULL || FETCHMODE__SUMMARY */
	public $fetch_children; // bool whether to fetch attached articles
	public $fetch_child_mode; /* FETCHMODE__FULL || FETCHMODE__SUMMARY */
	public $fetch_current_mapping_only; /* fetch just mapping info associated with URL being viewed */
	public $fetch_mapped_profiles; // bool whether to fetch attached profiles
	private $iAttachedArticleFetchLimit;  // int how many attached articles to fetch
	protected $iAttachedArticleTotal = 0; // total number of attached articles
	public $oTemplate; /* a template instance used to render the content */

	private $bFetchAttachedTo; // bool whether to fetch associations with other articles
	private $aAttachedTo; // array details of articles this article is attached to
	
	public function __Construct() {
		
		$this->aMapping = array();
		$this->aProfile = array();
		$this->aImage = array();
		$this->aArticleId = array();
		$this->oArticleCollection = new ArticleCollection();
		$this->oLinkGroup = new LinkGroup();
		$this->SetFetchMode(FETCHMODE__FULL);
		$this->fetch_children = TRUE;
		$this->fetch_mapped_profiles = TRUE;
		$this->fetch_child_mode = FETCHMODE__SUMMARY;
		$this->fetch_current_mapping_only = FALSE;
		$this->bFetchAttachedTo = FALSE;
		$this->aAttachedTo = array();
	}

	
	public static function convertCkEditorFont2Html($text,$title) {
		//$text = preg_replace('/(?<=<div.*?)(?<!=\t*?"?\t*?)(class|style)=".*?"/', "<table>$1</table>", $text);
		$text = preg_replace('/<p>[ \t\r\n]+<span style="font-size:[ ]?([20|22|24].*?)".*?>(.*?)<\/span><\/p>/si', '<'.$title.'>${2}</'.$title.'>', $text);
		$text = preg_replace('/<span style="font-size:[ ]?([20|22|24].*?)".*?>(.*?)<\/span>/si', '<'.$title.'>${2}</'.$title.'>', $text);
		$text = preg_replace('/<span style="font-size:[ ]?(14.*?)".*?>(.*?)<\/span>/si', '${2}', $text);
		return $text = preg_replace('/<table .*?>/si', '<table>', $text);
		
		//return preg_replace('/\<[\/]?(table)([^\>]*)\>/i', '', $text);

	}
	
	public function SetFetchMode($mode) {
		$this->fetch_mode = $mode;
	}

	public function GetFetchMode() {
		return $this->fetch_mode;
	}
	
	public function SetChildFetchMode($mode) {
		$this->fetch_child_mode = $mode;
	}
	
	public function GetChildFetchMode() {
		return $this->fetch_child_mode;
	}
	
	public function GetFetchAttachedTo() {
		return $this->bFetchAttachedTo;
	}
	
	public function SetFetchChildren($bool) {
		$this->fetch_children = $bool;
	}
	
	public function GetFetchChildren() {
		return $this->fetch_children;	
	}
	
	public function SetFetchProfiles($bool) { 
		$this->fetch_mapped_profiles = $bool;
	}
	
	public function GetFetchProfiles() {
		return $this->fetch_mapped_profiles;
	}
	
	public function GetFetchCurrentMappingOnly() { 
		return $this->fetch_current_mapping_only;
	}
	
	public function SetFetchCurrentMappingOnly($bool) {
		$this->fetch_current_mapping_only = $bool;
	}
	
	public function SetFetchAttachedTo($bVal) {
		$this->bFetchAttachedTo = $bVal;
	}

	public function SetDefaultMappingOption($bVal)
	{
        	$this->bDefaultMappingOption = ($bVal) ? "t" : "f";	    
	}

	public function CountArticleCollection() {
		return $this->oArticleCollection->Count();
	}
	
	
	public function GetArticleCollection() {
		return $this->oArticleCollection;
	}
	
	public function GetType() {
		return $this->iType;
	}	

	public function SetType($iType) {
		$this->iType = $iType;
		if (strlen($this->GetTypeLabel()) < 1) {
			switch($this->iType) {
				case CONTENT__ARTICLE :
					$this->SetTypeLabel("Article");
					break;
				case CONTENT__SECTION :
					$this->SetTypeLabel("Section");
					break;
			}
		}
	}
	
	public function GetTypeLabel() {
		return $this->sTypeLabel;
	}	

	public function SetTypeLabel($sTypeLabel) {
		$this->sTypeLabel = $sTypeLabel;
	}
	
	public function GetId() {
		return $this->id;
	}	

	public function SetId($id) {
		$this->id = $id;
	}

	public function GetTitle() {
		return $this->title;
	}
	
	public function SetTitle($sTitle) {
		$this->title = $sTitle;
	}

	public function GetMetaDesc() {
		return $this->meta_desc;
	}
	
	public function SetMetaDesc($sMetaDesc) {
		$this->meta_desc = $sMetaDesc;
	}
	
	public function GetMetaKeywords() {
		return $this->meta_keywords;
	}
	
	public function SetMetaKeywords($sMetaKeywords) {
		$this->meta_keywords = $sMetaKeywords;
	}

  	public function GetDescShort($trunc = 0) {
  		
  		if ($trunc >= 1) {
  			$s = $this->short_desc;
			if (strlen($s) > $trunc) {
				$s = $s." ";
				$s = substr($s,0,$trunc);
				$s = substr($s,0,strrpos($s,' '));
				$s = $s."...";
				$s = strip_tags($s); // in case we left an open <b> tag
			}
			return $s;	
  		} else {
 			return $this->short_desc;
  		}
  	}
	
	public function SetDescShort($sDesc) {
		$this->short_desc = $sDesc;
	}
	
	public function GetDescFull() {
		return $this->full_desc;
	}

	public function SetDescFull($sDesc) {
		$this->full_desc = $sDesc;
	}


        public function GetImgSize() {
                return $this->img_size;
        }

	
        public function SetImgSize($size) {
                $this->img_size = $size;
        }       

        public function GetImgDisplay() {
                return $this->img_display;
        }

        public function SetImgDisplay($flag) {
                $this->img_display = $flag;
        }

        public function GetImgAlign() {
                return $this->img_align;
        }
 
        public function SetImgAlign($align) {
                $this->img_align = $align;
        }

	
	public function GetCreatedBy() {
		return $this->created_by;
	}
	
	public function GetCreatedDate() {
		return $this->created_date;
	}
	
	public function GetPublishedStatus() {
		return $this->published_status;
	}

	public function GetPublishedStatusLabel() {
		
		switch($this->GetPublishedStatus()) {
			case 0 :
				return "DRAFT";
				break;
			case 1 :
				return "PUBLISHED"; 
				break;
		}
	}
	
	public function GetPublishedDate() {
		return $this->published_date;
	}

	public function GetLastUpdated() {
		return $this->last_updated;
	}
	
	public function GetLastIndexedSolr() {
		return $this->last_indexed_solr;
	}
	
	/*
	 * Return relative to the website being viewed
	 * 
	 */
	public function GetUrl() {
		
		if (strlen($this->url) < 1) $this->SetUrl();
		
		return $this->url;
				
	}
	
	public function SetUrl() {
		
		global $_CONFIG;
		
		if (!is_numeric($_CONFIG['site_id'])) $_CONFIG['site_id'] = 0;
		
		/*
		 * if the article is unpublished
		 * or not published to the site being viewed
		 * return a default URL for viewing
		*/
		$defaultUrl = $_CONFIG['url'] . "/article.php?&id=".$this->GetId();
		
		$aMapping = $this->GetMappingBySiteId($_CONFIG['site_id']);
		if (count($aMapping) < 1) return $this->url = $defaultUrl;
		
		$oMapping = $aMapping[0]; /* pick the first publish mapping associated with the site being viewed */ 
		
		if (!is_object($oMapping)) return $this->url = $defaultUrl;
		
		$this->url = $oMapping->GetUrl();
		
	}
	
	
	
	public function GetAll($aFilter = array(),$fields = '',$fetch = TRUE) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;
		
		$sFrom = "";
		$sWhere = "";
		
		if (strlen($fields) < 1) {
			$fields = "	a.*
						,m.website_id
						,to_char(a.created_date,'DD/MM/YYYY') as created_date
						,to_char(a.last_updated,'DD/MM/YYYY') as last_updated
						,to_char(a.last_indexed_solr,'DD/MM/YYYY') as last_indexed_solr
						,to_char(a.published_date,'DD/MM/YYYY') as published_date
					";
		}
		
		
		if (count($aFilter) >= 1) {
			foreach($aFilter as $k => $v) {
				switch($k) {
					case "URI" :
						$sFrom .= " ,".DB__ARTICLE_MAP_TBL." m";
						$sWhere .= " AND a.id = m.article_id AND m.section_uri LIKE '".$v."%'"; 
						break;
					case "WEBSITE_ID" :
						$sWhere .= " AND m.website_id = ".$v;
						break;
					case "LAST_INDEXED" :
						$sWhere .= " AND a.last_updated > a.last_indexed_solr ";
						break;
				}
			}
		}
		
		$sql = "SELECT
						$fields 
					FROM 
						".DB__ARTICLE_TBL." a
						".$sFrom."
					WHERE
						1=1 
						".$sWhere."
					ORDER BY 
						a.title ASC;";

		
		$db->query($sql);
		
		if ($db->getNumRows() < 1) return false;
		
		$aRes = $db->getRows();

		if (!$fetch) return $aRes;
		
		$aArticle = array(); 
		
		foreach($aRes as $a) {
			$oArticle = new Article();	
			$oArticle->SetFromArray($a);
			$oArticle->SetMapping();
			$aArticle[$oArticle->GetId()] = $oArticle;
		}
				
		return $aArticle;

	}
	
	
	/*
	 * Retrieve content by id
	 *  
	 * @param int website id
	 * @param string website section uri (eg /continent/africa, /country/brazil/volunteer) 
	 * @return mixed content object or false if not found
	 */
	public function GetById($id) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."() id=".$id);

		global $db;
		
		if (!is_numeric($id)) return false;
	
		$field_sql = ($this->GetFetchMode() == FETCHMODE__FULL) ? "a.*" : "a.id, a.title, a.meta_desc, a.meta_keywords, a.short_desc, a.full_desc, a.published_status, a.published_date";	
	
		$sql = "SELECT 
						$field_sql  
						,to_char(a.created_date,'DD/MM/YYYY') as created_date
						,to_char(a.last_updated,'DD/MM/YYYY') as last_updated
						,to_char(a.last_indexed_solr,'DD/MM/YYYY') as last_indexed_solr
						,to_char(a.published_date,'DD/MM/YYYY') as published_date
					FROM 
						".DB__ARTICLE_TBL." a 
					WHERE
						a.id = ".$id." 
					";
		
		$db->query($sql);
		
		if ($db->getNumRows() != 1) return false;
		
		$aRes = $db->getRow();

		$this->SetFromArray($aRes);
		
		/* get associated publish mappings, attached profiles, attached images */ 
		$this->SetMapping();
		$this->SetAttachedImage();
		if ($this->GetFetchMode() == FETCHMODE__FULL) {
			if ($this->GetFetchProfiles()) {
				$this->SetAttachedProfile();
			}
			if ($this->GetFetchChildren()) {
				$this->SetAttachedArticle();
			}
			$this->SetAttachedLink();
			if ($this->GetFetchAttachedTo()) {
				$this->SetAttachedTo();
			}
		}
		
		return true;
		
	}
	
	public function SetAttachedTo() {
		
		
		global $db;
		
		// POstgres array_agg not available in 7.3
		// array_to_string(array_agg(published_url), '<br />') as published_to
		
		$sql = "
					select 
						id,
						title,
						published_url
					from (
							select 
								l.a1 as id, 
								a.title, 
								w.name||m.section_uri as published_url 
							from 
								article_link l, 
								article a, 
								article_map m, 
								website w 
							where 
								l.a2 = ".$this->GetId()." and 
								l.a1 = a.id and 
								a.id = m.article_id and 
								m.website_id = w.id 
							order by 
								l.a1 asc
						) as q1 
					group by id,title, published_url order by id asc;
		";
		
		$db->query($sql);
		
		if ($db->getNumRows() < 1) return false;

		$result = $db->getRows();
		
		//Logger::Msg($result);
		
		$output = array();
				
		foreach($result as $row) {

			if (!array_key_exists($row['id'], $output)) { 
				$output[$row['id']] = $row;
			} else {
				$output[$row['id']]['published_url'] .= "<br />".$row['published_url'];
			}
				
		}
		
		$this->aAttachedTo = $output;

	}
	
	public function GetAttachedTo() {
		return $this->aAttachedTo;
	}
	
	
	/*
	 * Retrieve content objects associated with a website section
	 *  
	 * @param int website id
	 * @param string website section uri (eg /continent/africa, /country/brazil/volunteer) 
	 * @return mixed content object or false if not found
	 */
	public function Get($iWebsiteId,$sSectionUri,$iLimit = -1,$exact = true) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;
		
		if (!is_numeric($iWebsiteId)) return false;
		if (strlen($sSectionUri) < 1) return false;
		
		/* @todo - validate URI */
	
		$sLimit = ($iLimit >= 1) ? "LIMIT ".$iLimit : "";
	
		if (!$exact) {
			$match = " LIKE ";
			$wildcard = "%";
		} else {
			$match = " = ";
			$wildcard = "";
		}

		$field_sql = ($this->GetFetchMode() == FETCHMODE__FULL) ? "a.*" : "a.id, a.title, a.short_desc, a.full_desc, a.published_status, a.published_date";
		
		$sql = "SELECT 
						$field_sql 
						,to_char(a.published_date,'DD/MM/YYYY') as published_date
						,to_char(a.created_date,'DD/MM/YYYY') as created_date
						,to_char(a.last_updated,'DD/MM/YYYY') as last_updated
						,to_char(a.last_indexed_solr,'DD/MM/YYYY') as last_indexed_solr
					FROM 
						".DB__ARTICLE_TBL." a
						,".DB__ARTICLE_MAP_TBL." m 
					WHERE 
						m.website_id = ".$iWebsiteId."
						AND m.section_uri ".$match." '".$sSectionUri."".$wildcard."'
						AND m.article_id = a.id
						ORDER BY a.published_date DESC
						".$sLimit."
						;
					";
				
		$db->query($sql);
		
		if ($db->getNumRows() != 1) return false;
		
		$aRes = $db->getRow(PGSQL_ASSOC);

		$this->SetFromArray($aRes);
		
		/* get associated publish mappings, attached profiles, attached images */ 
		$this->SetMapping($sSectionUri);
		$this->SetAttachedProfile();
		$this->SetAttachedImage();
		$this->SetAttachedArticle();
		$this->SetAttachedLink();

		return true;
		
	}

	
	
	public function GetMappingLabel() {
		
		$s = "<ul>";
		
		foreach($this->aMapping as $oMapping) {
			$s .= "<li><a class='p_small' href='http://www.".$oMapping->GetLabel()."' title='View ".$this->GetTypeLabel()."' target='_new'> " .$oMapping->GetLabel()."</a></li>";
		}
		
		$s .= "<ul>";
		
		return $s;
		
	}
	
	public function GetMappingBySiteId($iSiteId) {
	
		$a = array();
		
		foreach($this->aMapping as $oMapping) {
			if ($oMapping->GetWebsiteId() == $iSiteId) $a[] = $oMapping;
		}
		
		return $a;
	}
	
	public function GetMappingBySectionUri($section_uri) {

		foreach($this->aMapping as $oMapping) {
			if ($oMapping->GetSectionUri() == $section_uri) return $oMapping;
		}
		
	}
	
	public function GetMapping() {
		return $this->aMapping;
	}

	public function SetMapping($section_uri = "") {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db; 
		
		if ($this->GetFetchCurrentMappingOnly() && strlen($section_uri) > 1) {
			$sWhere = " AND m.section_uri = '".$section_uri."'";
		}
		
		$sql = "SELECT 
						m.oid,
						m.website_id,
						m.section_uri,
						o.* 
					FROM 
						".DB__ARTICLE_MAP_TBL." m  
						LEFT OUTER JOIN ".DB__ARTICLE_MAP_OPTS." o ON m.oid = o.article_map_oid  
					WHERE 
						article_id = ".$this->GetId() ."
						$sWhere
						";
					

		$db->query($sql);

		if ($db->getNumRows() < 1) return false;
		
		$aRes = $db->getRows();

		foreach($aRes as $aRow) {
			$oContentMapping = new ContentMapping($aRow['oid'],$aRow['website_id'],$aRow['section_uri']);
			
			$opts = array();
			$opts[ARTICLE_DISPLAY_OPT_PLACEMENT] = ($aRow['opt_placement'] == NULL) ? $this->bDefaultMappingOption : $aRow['opt_placement'];
			$opts[ARTICLE_DISPLAY_OPT_ORG] = ($aRow['opt_org'] == NULL) ? $this->bDefaultMappingOption : $aRow['opt_org'];
			$opts[ARTICLE_DISPLAY_OPT_ARTICLE] = ($aRow['opt_article'] == NULL) ? "t" : $aRow['opt_article'];
			$opts[ARTICLE_DISPLAY_OPT_PROFILE] = ($aRow['opt_profile'] == NULL) ? "t" : $aRow['opt_profile'];
			$opts[ARTICLE_DISPLAY_OPT_REVIEW] = ($aRow['opt_review'] == NULL) ? "t" : $aRow['opt_review'];
			$opts[ARTICLE_DISPLAY_OPT_SOCIAL] = ($aRow['opt_social'] == NULL) ? "t" : $aRow['opt_social'];
			$opts[ARTICLE_DISPLAY_OPT_ADS] = ($aRow['opt_ads'] == NULL) ? "t" : $aRow['opt_ads'];
			$opts[ARTICLE_DISPLAY_OPT_IMG] = ($aRow['opt_img'] == NULL) ? "t" : $aRow['opt_img'];

			/* @deprecated */
			$opts[ARTICLE_DISPLAY_OPT_PARENT_TABS] = ($aRow['opt_ptab'] == 't') ? 't' : 'f';
			$opts[ARTICLE_DISPLAY_OPT_FEATURED_PROJECT] = ($aRow['opt_fproject'] == 't') ? 't' : 'f';
			$opts[ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_HEADER] = ($aRow['opt_txtalignh'] == 't') ? 't' : 'f';
			$opts[ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_BODY] = ($aRow['opt_txtalignb'] == 't') ? 't' : 'f';
			$opts[ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_FOOTER] = ($aRow['opt_txtalignf'] == 't') ? 't' : 'f';
																			
			
			$opts[ARTICLE_DISPLAY_OPT_SEARCH_KEYWORD] = stripslashes($aRow['search_keywords']);
			$opts[ARTICLE_DISPLAY_OPT_PTITLE] = stripslashes($aRow['p_title']);
			$opts[ARTICLE_DISPLAY_OPT_OTITLE] = stripslashes($aRow['o_title']);
			$opts[ARTICLE_DISPLAY_OPT_NTITLE] = stripslashes($aRow['n_title']);
			$opts[ARTICLE_DISPLAY_OPT_PINTRO] = stripslashes($aRow['p_intro']);
			$opts[ARTICLE_DISPLAY_OPT_OINTRO] = stripslashes($aRow['o_intro']);
			
			$oContentMapping->SetOptionsFromArray($opts);
			$this->aMapping[] = $oContentMapping; 
		}
		
		//Logger::Msg($this->aMapping);
	}
	
	
	public function GetNextArticleSeq() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		return $db->getFirstCell("SELECT nextval('article_seq')");
	}
	
	
	
	
	/*
	 * INSERT / UPDATE Article
	 * 
	 * Acts as a wrapper for Add() / Update()
	 * 
	 */
	public function Save(&$response) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		if (!$this->Validate($response)) return false;
		
		
		$this->Sanitize();			
		
		if (!is_numeric($this->GetId())) {
			$this->SetId($this->GetNextArticleSeq());
			
			if(!$this->Add($response)) return false;
		} else {
			if (!$this->Update($response)) return false;
		}
		
		/* is the article published? optionally trigger cache update */
		$this->SetMapping();
		$aMapping = $this->GetMapping();
		if (count($aMapping) < 1) return true; /* not published, we are done */ 
		
		foreach($aMapping as $oMapping) {
			$oMapping->SetCacheUpdate(); /* re-generate the cached version of the page */
		}			
		
		return true;
	}
	
	
	public function Validate(&$aResponse) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		if (strlen($this->GetTitle()) < 1) {
			$aResponse['title'] = "Title must be supplied";
		}
		
		if (strlen($this->GetTitle()) > 255) {
			$aResponse['title'] = "Title must be less than 254 characters";
		}

		// disabled to allow creation of unpublished article "stubs"
		//if (strlen($this->GetDescShort()) < 1) {
		//	$aResponse['desc_short'] = "Short Description must be supplied";
		//}
		
		if (strlen($this->GetDescShort()) > 1999) {
			$aResponse['desc_short'] = "Short Description must be less than 254 characters";
		}
		
		
		if (count($aResponse) >= 1) return false;
		
		return true;
	}

	
	public function Add(&$response) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;
		
		$db->query("INSERT INTO ".DB__ARTICLE_TBL." (
									id
									,title
									,short_desc
									,full_desc
									,created_by
									,created_date
									,last_updated
									,last_indexed_solr
									,published_status
									,published_date
								) VALUES (
									".$this->GetId()."
									,'".addslashes($this->GetTitle())."'
									,'".addslashes($this->GetDescShort())."'		
									,'".addslashes($this->GetDescFull())."'
									,".$this->GetCreatedBy()."
									,now()::timestamp
									,now()::timestamp
									,now() - interval '1 hour'
									,".$this->GetPublishedStatus()."
									,now()::timestamp
								);
					");
		
		if (!$db->getAffectedRows() == 1) {
			$response['save_error'] = "There was a problem adding the ".$this->GetTypeLabel().".";
			return false;
		}
		
		return true;
		
	}

	
	public function  Update(&$response) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		
		$db->query("UPDATE ".DB__ARTICLE_TBL." 
						SET
							title = '".addslashes($this->GetTitle())."' 
							,short_desc = '".addslashes($this->GetDescShort())."' 
							,full_desc = '".addslashes($this->GetDescFull())."' 
							,last_updated = now()::timestamp
							,last_indexed_solr = now() - interval '1 hour'
						WHERE id = ".$this->GetId().";
					");
		
		
		if (!$db->getAffectedRows() == 1) {
			$response['save_error'] = "There was a problem adding the ".$this->GetTypeLabel().".";
			return false;
		}
		
		return true;
	}

	public function GetAttachedImage() {
		return $this->aImage;	
	}

	public function SetAttachedImage() {	
		$this->SetAttachedImages();
	}
	
	public function AddAttachedImage($oImage) {
		$this->aImage[] = $oImage;
	}

	public function GetDisplayImageUrl()
	{
	    try {
 
    	    if (!is_array($this->aImage) || count($this->aImage) < 1)
	           $this->SetAttachedImage();
	           $oImage = $this->GetImage(0);
	           if (is_object($oImage))
	               return $oImage->GetUrl("");

                return $this->GetDisplayImageUrlFromContent();

	    } catch (Exception $e) {
	        return false;
	    }
	    
	}

	/**
	 * Many articles have no image(s) attached directly, although images are present 
	 * embedded in content (full_desc field) via CKEditor
	 * Parse HTML body to grab 1st image URL if one is present
	 */
	public function GetDisplayImageUrlFromContent()
	{
	    $html = $this->GetDescFull();
	    $arrImgUrl = array();
	    preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i',$html, $arrImgUrl );
	    if (count($arrImgUrl[1]) >= 1)
	        return $arrImgUrl[1][0];
	}

	public function GetAttachedProfile() {
		return $this->aProfile;
	}
	
	public function AddAttachedProfile($oProfile) {
		$this->aProfile[] = $oProfile;
	}

	// allow caller to inject their own list of profiles, overriding any the publisher added
	public function SetAttachedProfileFromArray($aProfile) {
		$this->aProfile = $aProfile;
	}
	
	public function SetAttachedProfile($aProfileId = array()) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		try {

		    if (!is_array($aProfileId) || count($aProfileId) < 1)
		    {
		        // fetch profiles statically mapped to article
                $sql = "SELECT m.profile_id FROM ".DB__ARTICLE_PROFILE_MAP_TBL." m WHERE m.article_id = ".$this->GetId();
    
        		$db->query($sql);
    
        		if ($db->GetNumRows() < 1) return false;
    
        		$aRes = $db->GetRows();
    
        		$aProfileId = array();
        		foreach($aRes as $aRow) {
        		    $aProfileId = $aRow['profile_id'];
        		}
		    }

		    $aProfile = PlacementProfile::Get("ID_LIST_SEARCH_RESULT",$aProfileId);

		    $this->aProfile = (is_array($aProfile)) ? $aProfile : array();
		    
		} catch (Exception $e) {
		    $this->aProfile = array();
		    return false;
		}

		return true;		
	}
	
	
	public function RemoveProfile($aRequest,&$response) {
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		//Logger::Msg("remove_profile");
		//Logger::Msg($aRequest);

		if (!is_numeric($aRequest['profile_id'])) {
			$response['website_id'] = "ERROR : You must select a profile to remove ";
			return false;
		}

		$type = ($aRequest['profile_type'] == 0) ? 0 : 1; /* 0=company profile, 1=placement */
	
		//print $sql = "DELETE FROM ".DB__ARTICLE_PROFILE_MAP_TBL." WHERE article_id = ".$aRequest['id']." AND profile_type=".$type." AND profile_id=".$aRequest['profile_id'];	
		
		$db->query("DELETE FROM ".DB__ARTICLE_PROFILE_MAP_TBL." WHERE article_id = ".$aRequest['id']." AND profile_type=".$type." AND profile_id=".$aRequest['profile_id']);
		
		return true;
		
	}
	
	
	public function AttachProfile($aRequest,&$response) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		$aRequest['profile_type'] = PROFILE_COMPANY;
		$aRequest['profile_id'] = $aRequest['company_id'];
		
		if (!is_numeric($aRequest['company_id'])) {
			$response['profle_id'] = "ERROR : Please select a valid profile to attach";
			return false;
		}
		
		if (is_numeric($aRequest['placement_id'])) {
			$aRequest['profile_type'] = PROFILE_PLACEMENT;
			$aRequest['profile_id'] = $aRequest['placement_id'];
		}
		
		$db->query("SELECT 
						article_id 
					FROM 
						".DB__ARTICLE_PROFILE_MAP_TBL." 
					WHERE 
						article_id = ".$this->GetId()." 
						AND profile_type = ".$aRequest['profile_type']."
						AND profile_id = ".$aRequest['profile_id']."
					");
		
		if ($db->getNumRows() == 1) {
			$response['duplicate_check'] = "ERROR : This profile is already attached";
			return false;
		}

		//$aRequest['placement_id']
		
		$db->query("INSERT INTO ".DB__ARTICLE_PROFILE_MAP_TBL." 
						(article_id,profile_type,profile_id) 
					VALUES 
						(".$this->GetId().",".$aRequest['profile_type'].",".$aRequest['profile_id'].");");
		
		if ($db->getAffectedRows() != 1) {
			$response['attach_err'] = "ERROR : A problem occured and profile was not attached";
			return true;
			
		} else {
			$response['msg'] = "SUCCESS : Attached profile";
			return true;
		}
	}
	
	
	

	public function Publish($aRequest,&$response) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$aWebsiteId = Mapping::GetIdByKey($aRequest,"web_");
		$aDisplayOptions = Mapping::GetIdByKey($aRequest,"opt_");
		
		
		if (count($aWebsiteId) < 1) {
			$response['website_id'] = "ERROR : You must select one or more websites";
			return false;
		}
		
		$sSectionUri = trim($aRequest['section_uri']);
		if (!preg_match("/^[\/]{1}/",$sSectionUri)) $sSectionUri = "/".$sSectionUri;
		
		if (strlen(trim($sSectionUri)) < 1) {
			$response['section_uri'] = "ERROR : You must specify a valid section uri (eg /volunteer/animals)";
			return false;
		}
		
		//if (DEBUG) Logger::Msg($aWebsiteId);
		//if (DEBUG) Logger::Msg($sSectionUri);
		
		$aMapping = array();
		
		foreach($aWebsiteId as $id) {
			$aMapping[] = array($id => $sSectionUri);
		}
				
		if (!$this->Map($aMapping,$bDeleteExisting = false,$response)) return false;
		
		
		/* update cached pages for all pages on which this article is being published */
		
		
		$this->SetMapping();
		$aMapping = $this->GetMapping();
		
		if (count($aMapping) < 1) return true; /* not published, we are done */ 
		
		foreach($aMapping as $oMapping) {
			$oMapping->SetCacheUpdate(); /* re-generate the cached version of the page */
		}

		unset($this->aMapping);
		
		return true;
	}
	
		
	/*
	 * Attach 1 or more articles to this article
	 * 
	 * Allows creating of "section" pages which contain collections of other articles
	 * 
	 */
	public function AttachArticleId($aId) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		if ((!is_array($aId)) || (count($aId) <1)) return false;
		
		foreach($aId as $id) {
			
			if(!is_numeric($id) || ($id == $this->GetId())) continue;
						
			$db->query("SELECT 1 FROM ".DB__ARTICLE_LINK_TBL." WHERE a1 = ".$this->GetId()." AND a2 = ".$id);

			if ($db->GetNumRows() < 1) {
				$db->query("INSERT INTO ".DB__ARTICLE_LINK_TBL." (a1,a2) VALUES (".$this->GetId().",".$id.");");
			}
		}
		
		return true;		
	}

	
	public function RemoveAttachedArticle($aId) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		if ((!is_array($aId)) || (count($aId) <1)) return false;

		$error = FALSE;
		
		foreach($aId as $id) {
			
			if(!is_numeric($id)) continue;
						
			$db->query("SELECT 1 FROM ".DB__ARTICLE_LINK_TBL." WHERE a1 = ".$this->GetId()." AND a2 = ".$id);

			if ($db->GetNumRows() == 1) {
				$db->query("DELETE FROM ".DB__ARTICLE_LINK_TBL." WHERE a1 = ".$this->GetId()." AND a2 = ".$id);
			} else {
				$error = TRUE;
			}
		}
		
		if (!$error) return true;
	}
	
	/*
	 * Get attached articles objects and add to article collection  
	 * 
	 */
	public function  SetAttachedArticle($fetch = TRUE) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$iPage = isset($_REQUEST['Page']) ? $_REQUEST['Page'] : 0;
		$iPageSize = 30;
		$iStart = ($iPage > 1) ? (($iPage -1) * $iPageSize) : 0;

		if ($fetch) $this->SetAttachedArticleId();
		
		if (is_numeric($iStart) && is_numeric($iPageSize))
		{
            $arrId = $this->GetAttachedArticleId();
            $iStart = ($iStart > count($arrId)) ? 0 : $iStart;
            $iLength = (count($arrId) < $iStart + $iPageSize) ? (count($arrId) - $iStart) : $iPageSize;
            $arrId = array_slice($arrId, $iStart, $iLength);
            $this->SetAttachedArticleIdFromArray($arrId);
		}
		
		foreach($this->GetAttachedArticleId() as $id) {
			$oArticle = new Article();
			$oArticle->SetFetchMode($this->GetChildFetchMode());
			$oArticle->SetFetchChildren(FALSE);
			$oArticle->SetFetchProfiles(FALSE);
			if ($oArticle->GetById($id)) {
				$this->oArticleCollection->Add($oArticle);
			}
			
		}
		
	}
	
	public function  SetAttachedArticleIdFromArray($arrId) {
	   if (is_array($arrId))
	   {
	       $this->aArticleId = $arrId;
	   }
	}

	public function  SetAttachedArticleId() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$limitSql = ($this->GetAttachedArticleFetchLimit() >= 1) ? " LIMIT ".$this->GetAttachedArticleFetchLimit() : "";   
				
		$db->query("SELECT m.a2 as id FROM ".DB__ARTICLE_LINK_TBL." m, ".DB__ARTICLE_TBL." a WHERE m.a1 = ".$this->GetId(). " AND m.a2 = a.id ORDER BY a.published_date DESC ". $limitSql);
	
		if ($db->GetNumRows() >= 1) {
			$result = $db->getRows();
			foreach($result as $row) {
				$this->aArticleId[] = $row['id'];
			}
		}
		
		$this->iAttachedArticleTotal = count($this->aArticleId);
	}
	
	public function GetAttachedArticleTotal()
	{
	    return $this->iAttachedArticleTotal;
	}

	public function SetAttachedArticleFetchLimit($iLimit) {
		$this->iAttachedArticleFetchLimit = $iLimit;
	}
	
	private function GetAttachedArticleFetchLimit() {
		return $this->iAttachedArticleFetchLimit;
	}

	public function GetAttachedArticleId() {
		return $this->aArticleId;
	}
		
	
	
	public function Delete() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		if (!is_numeric($this->GetId())) return false;
		
		$this->MapDelete();
		
		$db->query("DELETE FROM ".DB__ARTICLE_TBL." WHERE id = ".$this->GetId());
		
		return true;
		
	}

	
	/*
	 * Associate content with a given website section
	 * 
	 * @todo - rules to be revised to enable 1..n mappings to uri's
	 * 
	 * 	Rules :
	 * 		article 1..n website
	 * 		article 1..n section
	 * 		section 1..1 article
	 * 
	 * @param array "website_id (int)" => "section uri (string)"
	 * @return bool true / false
	 * 
	 */
	public function Map($aMapping,$bDeleteExisting = true,&$aResponse) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;

		if ($bDeleteExisting) {
			$this->MapDelete();
		}

		$bError = false;
		
		for($i=0;$i<count($aMapping); $i++) {
			foreach($aMapping[$i] as $iWebsiteId => $sSectionUri) {

				if (!$this->MapExists($iWebsiteId,$sSectionUri,$aResponse)) {
					$db->query("INSERT INTO ".DB__ARTICLE_MAP_TBL." (article_id,website_id,section_uri) VALUES (".$this->GetId().",".$iWebsiteId.",'".$sSectionUri."');");
				} else {
					$bError = true;
				}
			}
		}
		
		if ($bError) return false;
		
		return true;
		
	}

	public function MapExists($iWebsiteId,$sSectionUri,&$aResponse) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$db->query("SELECT a.title FROM ".DB__ARTICLE_MAP_TBL." m, ".DB__ARTICLE_TBL." a WHERE m.article_id = a.id AND m.website_id = ".$iWebsiteId." AND m.section_uri = '".$sSectionUri."'");
		
		if ($db->getNumRows() >= 1) {
			$i = 0;
			$aRes = $db->getRows();
			foreach($aRes as $aRow) {
				$aResponse['map_lock_'.$i++] = "ERROR : Article (".$aRow['title'].") is already published to this location.";
			}
			return true;
		}
		
	}
	
	public function MapDelete() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$db->query("DELETE FROM ".DB__ARTICLE_MAP_TBL." WHERE article_id = ".$this->GetId());
		
	}
	
	public function MapDeleteById($id) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db;
		
		$db->query("DELETE FROM ".DB__ARTICLE_MAP_TBL." WHERE oid = ".$id);
		
	}
		
	
	
	/*
	 * Santize input and escape non-db safe chars
	 * 
	 */
	private function Sanitize() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		foreach($this as $k => $v) {
			if (is_string($v)) {				
				//Validation::Sanitize($this->$k);
				Validation::AddSlashes($this->$k);
			}
		}

	}
	

	/*
	 * Image Implementation
	 * 
	 * 
	 */
	public function SetAttachedImages($iType = PROFILE_IMAGE) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $db,$_CONFIG;
		
		$db->query("SELECT i.*,m.type FROM image_map m, image i WHERE m.img_id = i.id AND m.link_to = 'ARTICLE' AND m.link_id = ".$this->GetId()." ORDER BY i.id ASC");

		if ($db->getNumRows() >= 1) {
			$aObj = $db->getObjects();
			foreach($aObj as $o) {
				$oImage = new Image($o->id,$o->type,$o->ext,$o->dimensions,$o->width,$o->height,$o->aspect);
				$this->SetImage($oImage);					
			}
		}

		//Logger::Msg($this->aImage);

	}

	private function SetImage($oImage) {
		$this->aImage[] = $oImage;
	}

	public function GetImage($idx = 0) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."() img_id=".$idx);
		
		if (is_object($this->aImage[$idx])) {
			return $this->aImage[$idx];
		}
	}
	
	public function GetImages() {
		return $this->aImage;
	}
	
	public function GetImageCount() {
		return count($this->aImage);
	}
	
	public function RemoveAttachedImage($img_id) {
		
		global $db;

		if (!is_numeric($img_id)) return false;
		
		$db->query("SELECT 1 FROM image_map WHERE img_id = ".$img_id ." AND link_to = 'ARTICLE' AND link_id = '".$this->GetId()."'");
		
		if ($db->GetNumRows() == 1) {
			$db->query("DELETE FROM image_map WHERE img_id = ".$img_id ." AND link_to = 'ARTICLE' AND link_id = '".$this->GetId()."'");
		}
	}

	
	public function SetAttachedLink($template = "link_list_01.php") {
		
		global $_CONFIG;
		
		$this->oLinkGroup->GetByAssociation("ARTICLE",$this->GetId());
		$this->oLinkGroup->oTemplate->Set("LINK_TO_ID",$this->GetId());
		$this->oLinkGroup->oTemplate->Set("WEBSITE_URL",$_CONFIG['url']);
		$this->oLinkGroup->LoadTemplate($template);
		
	}
	
	public function GetAttachedLink() {
		return $this->oLinkGroup->GetItems();
	}
	
	
	/*
	 * Template Concrete Implementation
	 * 
	 * 
	 */
	
	public function initTemplate() {
		$this->oTemplate = new Template();
	}
	
	public function LoadTemplate($sFilename,$aOptions = array()) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		if (!is_object($this->oTemplate))
			$this->oTemplate = new Template(); 
		
		$aDefaultOptions = array(
				"TITLE" => $this->GetTitle(),
				"DESC_SHORT" => nl2br($this->GetDescShort()),
				"DESC_SHORT_160" => nl2br($this->GetDescShort(160)),
				"FULL_DESC" => nl2br($this->GetDescFull()),
				"URL" => $this->GetUrl(),
				"PUBLISHED_DATE" => $this->GetPublishedDate(),
				"IMG_SIZE" => $this->GetImgSize(),
				"IMG_DISPLAY" => $this->GetImgDisplay(),
				"IMG_ALIGN" => $this->GetImgAlign(),
				"ARTICLE_OBJECT" => $this
		);
		
		$aOptions = array_merge($aOptions,$aDefaultOptions);
		
		$this->oTemplate->SetFromArray($aOptions);
		
		/*
		 * Render optional template features 
		 * 	eg.
		 * 		- Main Large Right-Aligned Featured Profile
		 * 		- Profile List
		 * 		- Images
		 * 		- Affiliate Code
		 * 
		 * 
		 */
				
		$this->oTemplate->LoadTemplate($sFilename);
		
	}
	
	public function Render() {

		return $this->oTemplate->Render();
		
	}

	
	/* @todo - migrate to a base methid */
	public function SetFromArray($a,$m = "GET", $escape_chars = TRUE) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."() mode: ".$m);
		
		//print "SetFromArray() m=".$m.", escape_chars=".$escape_chars."<br />";
		
		foreach($a as $k => $v) {
			if ($escape_chars) {
				if ($m == "GET") {
					$this->$k = (is_string($v)) ? stripslashes($v) : $v;	
				} elseif ($m == "SET") {
					$this->$k = (is_string($v)) ? addslashes($v) : $v;
				}
			} else {
				$this->$k = $v;
			}
		}
	}	


	public function toJSON() {
		
		$fields = array(
				"id" => $this->GetId(),
				"title" => $this->GetTitle(),
				"desc_short" => trim($this->cleanText($this->GetDescShort())),
				"full_desc" => trim($this->cleanText($this->GetDescFull())),
				//"url" => ($this->GetLastUpdated() == null) ? '' : $this->GetUrlLastUpdated(),
				"view_url" => $this->GetUrl(),
				"edit_url" => 'http://admin.oneworld365.org/article-editor?&id='.$this->GetId(),
				"publish_url" => 'http://admin.oneworld365.org/article-publisher?&id='.$this->GetId(),
				"published_date" => $this->GetPublishedDate(),
				"last_updated_date" => ($this->GetLastUpdated() == null) ? '' : $this->GetLastUpdated(), 
		);
		
		return $fields;
	}
	
	public function cleanText($str){
		$str = html_entity_decode(strip_tags($str), ENT_QUOTES, 'utf-8');
		return strip_tags(preg_replace("/&#?[a-z0-9]+;/i"," ",$str));
	}
	

	public function SetPageContentOptions($sUri) {
	
		$this->aPageContentOptions = array();
			
	
		// overide default content features if options were explicitly set during article publication
		if (is_object($this->GetMappingBySectionUri($sUri))) {
			$this->aPageContentOptions = $this->GetMappingBySectionUri($sUri)->GetOptions();
		}
	
	}
	
	public function GetPageContentOption($key) {
				
		if (array_key_exists($key,$this->aPageContentOptions)) {
			return $this->aPageContentOptions[$key];
		}
	}
	
	public function GetPageContentOptions() {
		return $this->aPageContentOptions;
	}
	
}




class Article extends Content {
		
	public function __Construct() {
		
		$this->SetType(CONTENT__ARTICLE);
		$this->SetTypeLabel("Article");
		
		parent::__Construct();
		
	}
}



class Section extends Content {
		
	public function __Construct() {
		
		$this->SetType(CONTENT__SECTION);
		$this->SetTypeLabel("Section");
		
		parent::__Construct();
		
	}
}




class ContentMapping {

	private $oid;
	private $website_id;
	private $section_uri;
	
	private $opts_array; // array of bool values signal 2 template what content to display
	
	public function __Construct($oid,$website_id,$section_uri) {
		
		$this->oid = $oid;
		$this->website_id = $website_id;
		$this->section_uri = $section_uri;
		
		$this->opts_array = array();
	}
	
	public function GetById() {
		
		global $db;
		
		$sql = "SELECT m.oid,m.website_id,m.section_uri FROM ".DB__ARTICLE_MAP_TBL." m WHERE oid = ".$this->GetId();
		
		$db->query($sql);
		
		if ($db->getNumRows() == 1) {
			$result = $db->getRow();
			$this->website_id = $result['website_id'];
			$this->section_uri = $result['section_uri'];
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	public function GetId() {
		return $this->oid;
	}
	
	public function GetWebsiteId() {
		return $this->website_id;	
	}

	public function GetSectionUri() {
		return $this->section_uri;
	}

	
	/*
	 * @return mapping location as display label 
	 * 
	 */
	public function GetLabel() {

        	 global $_CONFIG;
                return $_CONFIG['sites'][$this->GetWebsiteId()] . $this->GetSectionUri();

		
	}
	
	public function GetUrl() {
		global $_CONFIG;
                return $_CONFIG['host_prefix'] . $this->GetLabel();

	}
	
	
	public function SetCacheUpdate() {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		Cache::Generate("http://www.".$this->GetLabel(),$this->GetSectionUri(),$this->GetWebsiteId(),$sleep = false);	
		
	}


	/* update content mapping url matching specified criterea */
	public static function UpdateUrl($url_from,$url_to) {

		global $db;

		if ((strlen($url_from) < 1) || (strlen($url_to) < 1)) return FALSE;

		$db->query("SELECT * from ".DB__ARTICLE_MAP_TBL." WHERE section_uri = '".$url_from."'");
		if ($db->getNumRows() >= 1) {
			$db->query("UPDATE ".DB__ARTICLE_MAP_TBL." SET section_uri = '".$url_to."' WHERE section_uri = '".$url_from."'");
			return TRUE;
		}
	}
	
	public function GetOptionById($opt_id) {
		
		if (isset($this->opts_array[$opt_id])) {
			return $this->opts_array[$opt_id];
		}
		 
	}
	
	public function GetOptions() {
		return $this->opts_array;
	}
	
	public function SetOptionsFromArray($opts_array) {
		
		if (!is_array($opts_array)) return FALSE;
		
		$this->opts_array = $opts_array;
	}
	
	/* set signals to instruct template to toggle display of content */
	public function SetOptions($mid, $opts_array, $aTextFieldOpts) {
	    
	    if (!is_array($opts_array) || !is_numeric($mid)) return FALSE;
	    
	    global $db;
	    
	    $sql = "DELETE FROM ".DB__ARTICLE_MAP_OPTS." WHERE article_map_oid = ".$mid;
	    
	    $db->query($sql);
	    
	    $search_keywords = addslashes($aTextFieldOpts['search_keywords']);
	    $p_title = addslashes($aTextFieldOpts['p_title']);
	    $o_title = addslashes($aTextFieldOpts['o_title']);
	    $n_title = addslashes($aTextFieldOpts['n_title']);
	    $p_intro = addslashes($aTextFieldOpts['p_intro']);
	    $o_intro = addslashes($aTextFieldOpts['o_intro']);
	    
	    $opts_array[ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_HEADER] = ($opts_array[ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_HEADER] == 'T') ? 'T' : 'F';
	    $opts_array[ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_BODY] = ($opts_array[ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_BODY] == 'T') ? 'T' : 'F';
	    $opts_array[ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_FOOTER] = ($opts_array[ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_FOOTER] == 'T') ? 'T' : 'F';
	    
	    $sql = "INSERT INTO ".DB__ARTICLE_MAP_OPTS." (	article_map_oid,
												opt_placement,
												opt_article,
												opt_ptab,
												search_keywords,
												p_title,
												o_title,
												n_title,
												p_intro,
												o_intro,
												opt_fproject,
												opt_txtalignh,
												opt_txtalignb,
												opt_txtalignf
											 ) VALUES (
												".$mid.",
												'".$opts_array[ARTICLE_DISPLAY_OPT_PLACEMENT]."',
												'".$opts_array[ARTICLE_DISPLAY_OPT_ARTICLE]."',
												'".$opts_array[ARTICLE_DISPLAY_OPT_PARENT_TABS]."',
												'".$search_keywords."',
												'".$p_title."',
												'".$o_title."',
												'".$n_title."',
												'".$p_intro."',
												'".$o_intro."',
												'".$opts_array[ARTICLE_DISPLAY_OPT_FEATURED_PROJECT]."',
												'".$opts_array[ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_HEADER]."',
												'".$opts_array[ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_BODY]."',
												'".$opts_array[ARTICLE_DISPLAY_OPT_BODY_TEXT_ALIGNMENT_FOOTER]."'
											 );";

	    $db->query($sql);
	    
	    if ($db->getNumRows() == 1) return TRUE;
	}

}

/* when retrieving articles, use exact "=" or fuzzy "like" pattern matching */
define("ARTICLE_SEARCH_MODE_FUZZY",0);
define("ARTICLE_SEARCH_MODE_EXACT",1);

/*
 * A collection of articles, with various retrieval methods 
 * 
 * 
 */
class ArticleCollection implements TemplateInterface  {
	
	private $aArticle;	
	private $iSearchMode;
	
	public function __Construct() {
		$this->aArticle = array();

		$this->SetSearchMode(ARTICLE_SEARCH_MODE_FUZZY);
	}

	public function SetSearchMode($mode) {
		$this->iSearchMode = $mode;
	}

	private function GetSearchMode() {
		return $this->iSearchMode;
	}

	public function Count() {
		return count($this->aArticle);
	}
	
	public function Get() {
		return $this->aArticle;
	}

	public function GetArticles() {
		return $this->aArticle;
	}

	
	public function Reset() {
	    $this->aArticle = array();
	}

	public function AddFromArray($aArticle)
	{
        if(!is_array($aArticle)) return false;
        foreach($aArticle as $oArticle)
        {
            $this->Add($oArticle);
        }
	}

	public function Add($oArticle) {
		
		if ((is_object($oArticle)) && ($oArticle instanceof Article)) {
			$this->aArticle[] = $oArticle;
		}
	}
	
	/*
	 * Get all articles associated with a section uri
	 * 	eg.  uri = "/news"
	 * 	returns :
	 * 		/news/2010/january/camp-america-events
	 * 		/news/2010/january/bunac-special-offers
	 * 		...
	 *  By default articles are ordered by date DESC (ie most recent)
	 * 
	 */	
	public function GetBySectionId($website_id,$sSectionUri,$getAttachedObj = true,$bUnPublished = false) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;
		
		if (strlen($sSectionUri) < 1) return false;
		
		if (is_numeric($website_id)) { 
			$sWhere = "AND m.website_id = ".$website_id;	
		} elseif (is_array($website_id) && count($website_id) >= 1) {
			$sWhere = "AND m.website_id IN (".implode(",",$website_id) .")";
		}
		
		if ($this->GetSearchMode() == ARTICLE_SEARCH_MODE_FUZZY) {
			$scope_sql = "AND m.section_uri LIKE '".$sSectionUri."%'";
		} elseif ($this->GetSearchMode() == ARTICLE_SEARCH_MODE_EXACT) {
			$scope_sql = "AND m.section_uri = '".$sSectionUri."'";
		}

		if ($bUnPublished) {
			
			$db->query("select 
							a.* 
						from 
							".DB__ARTICLE_TBL." a 
						where not exists (
							select 1 from ".DB__ARTICLE_MAP_TBL." m where a.id = m.article_id) 
						ORDER BY a.published_date ASC;");			

		} else {
		
			$sql = "SELECT 
						a.* 
						,to_char(a.created_date,'DD/MM/YYYY') as created_date
					    ,to_char(a.last_updated,'DD/MM/YYYY') as last_updated
						,to_char(a.last_indexed_solr,'DD/MM/YYYY') as last_indexed_solr
					FROM 
						".DB__ARTICLE_TBL." a
						,".DB__ARTICLE_MAP_TBL." m 
					WHERE 
						1=1
						".$sWhere."
					        ".$scope_sql." 	
						AND m.article_id = a.id
					ORDER BY 
						a.published_date ASC;";
		
			
			$db->query($sql);
		
		}

		if (DEBUG) Logger::Msg("Found ".$db->getNumRows()." article(s).");
		$db->query($sql);
		
		if ($db->getNumRows() < 1) return array();
		
		foreach ($db->getRows() as $a) {
		
			$oArticle = new Article();	
			$oArticle->SetFromArray($a);
			$oArticle->SetMapping();
			$oArticle->SetUrl();
			if ($getAttachedObj) {
				$oArticle->SetAttachedProfile();
				$oArticle->SetAttachedImage();
				$oArticle->SetAttachedArticle();
			}
			
			$this->aArticle[$oArticle->GetId()] = $oArticle;
			 			
		}
				
		return $this->aArticle;

	}
	
	public function GetSubSectionArticleDetails($iWebsiteId, $sSectionUri) {

		global $db;
		
		//if ((strlen($sSectionUri) < 1) || (!is_numeric($iWebsiteId))) return false;
		
		$sql = "select
				a.id,
				a.title,
				a.short_desc,
				m.section_uri
				from
				article_map m LEFT JOIN article a ON m.article_id = a.id
				where
				m.website_id = ".$iWebsiteId." AND 
				m.section_uri like '".$sSectionUri."/%'";
		
		$db->query($sql);
		
		if ($db->getNumRows() < 1) return array();
		
		return $db->getRows();
	}

	
	public function LoadTemplate($sFilename,$iWebSiteId = null) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		$this->oTemplate = new Template(); 
		
		$this->oTemplate->Set("ARTICLE_ARRAY",$this->aArticle);

		$this->oTemplate->LoadTemplate($sFilename);		
		
	}
	
	
	
	public function Render() {

		return $this->oTemplate->Render();
		
	}
	
}

?>
