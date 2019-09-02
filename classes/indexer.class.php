<?


/*
* Indexer is used to create keyword indices from profile text
* 
* It is called by indexer_batch.php which in turn is invoked by cron
*
* Can be run in two modes ($this->mode) : 0 = keyword index, 1 = tag cloud, 2 = both
* 
*  - keyword index mode generates a stemmed keyword index which principle search runs off 
*  - tag cloud mode @depreciated generates an unstemmed index for a tag cloud 
*
*/



define("WEIGHT_LOW",1);
define("WEIGHT_MED",2);
define("WEIGHT_HIGH",4);
define("WEIGHT_MAX",6);


class Indexer {

    public function __construct(&$db)
    {
        $this->Indexer($db);
    }

	public function Indexer(&$db) {
		$this->db = $db;
		$this->debug = false;
		$this->mode = 0;

		$this->aKeywordExclude = array();
		
	}

	public function UpdateIndex($id,$type,$aWords,$index) {

		foreach($aWords as $word => $freq) {
			$sql = "INSERT INTO $index VALUES ($id,$type,'".addslashes($word)."',$freq);";
			$this->db->query($sql);
		}
	}

	public function PreProcess() {
		if (($this->mode == 1) || ($this->mode == 2)) {
			$this->db->query("DELETE FROM keyword_idx_1");
		}
		if (($this->mode == 0) || ($this->mode == 2)) {
			$this->db->query("DELETE FROM keyword_idx_2");
		}
	}

	public function PostProcess() {

		if (($this->mode == 1) || ($this->mode == 2)) {
			$this->db->query("update keyword_idx_1 set word = 'united states', count = 6 where word = 'united';");
			$this->db->query("delete from keyword_idx_1 where word = 'states';");
			$this->db->query("update keyword_idx_1 set word = 'united kingdom', count = 6 where word = 'kingdon';");
			$this->db->query("update keyword_idx_1 set count = 12 where word in ('camp america','ccusa');");
			$this->db->query("update keyword_idx_1 set count = 4 where word in ('bunac');");
			$this->db->query("delete from keyword_idx_1 where word = 'camps';");
			$this->db->query("delete from keyword_idx_1 where word = 'girl';");
			$this->db->query("update keyword_idx_1 set word = 'uk' where word = 'kingdom';");
		}
	}

	public function getWords($phrase,$weight,$split = true, $stemmed = false,$count = true,$filter = true)  {

		$phrase = strtolower($phrase);
		if ($split == true) {
			$phrase .= str_repeat(' '.$phrase, $weight);
			$words = str_word_count($phrase, 1);
			$words = $this->removeStopWordsFromArray($words);
		} else { // process whole phrase
			$phrase .= str_repeat('::'.$phrase, $weight);
			$words = explode("::",$phrase);
		}

		if ($stemmed == true) {
			$words = $this->stemPhrase($words);
		}
				
		if ($count == true) {
			$words = array_count_values($words);
		}		
		
		if (!$filter) return $words;

		$words = $this->FilterExcludedKeywords($words);
		
		return $words;

	}

	public function removeStopWordsFromArray($words)  {

		$stop_words = array(
			'-','i', 'me', 'my', 'myself', 'we', 'our', 'ours', 'ourselves', 'you', 'your', 'yours', 
			'yourself', 'yourselves', 'he', 'him', 'his', 'himself', 'she', 'her', 'hers', 
			'herself', 'it', 'its', 'itself', 'they', 'them', 'their', 'theirs', 'themselves', 
			'what', 'which', 'who', 'whom', 'this', 'that', 'these', 'those', 'am', 'is', 'are', 
			'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'having', 'do', 'does', 
			'did', 'doing', 'a', 'an', 'the', 'and', 'but', 'if', 'or', 'because', 'as', 'until', 
			'while', 'of', 'at', 'by', 'for', 'with', 'about', 'against', 'between', 'into', 
			'through', 'during', 'before', 'after', 'above', 'below', 'to', 'from', 'up', 'down', 
			'in', 'out', 'on', 'off', 'over', 'under', 'again', 'further', 'then', 'once', 'here', 
			'there', 'when', 'where', 'why', 'how', 'all', 'any', 'both', 'each', 'few', 'more', 
			'most', 'other', 'some', 'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so', 
			'than', 'too', 'very','p','pe','include','new','throughout','will','s','st','across','br','would',
			'we\'ve','you\'d','thing','you\'ll','i\'ve','can','you\'re'
		);

		return array_diff($words, $stop_words);
	}

	public function stemPhrase($words)
	{

		// stem words
		$stemmed_words = array();
		foreach ($words as $word)
		{
		  // ignore 1 and 2 letter words
		  if (strlen($word) <= 2)
		  {
			continue;
		  }

		  $stemmed_words[] = PorterStemmer::stem($word, true);
		}

		return $stemmed_words;
	}

	private function GetKeywordExcludeArray() {
		return $this->aKeywordExclude;
	}
	
	private function SetKeywordExcludeArray($a) {
		if (!is_array($a)) $a = array();
		$this->aKeywordExclude = $a;		
	}
	
	private function FilterExcludedKeywords($aWords) {
		
		if (!is_array($aWords)) return array();
		
		//Logger::Msg("input");
		//Logger::Msg($aWords);
		
		$a = array();

		$aExclude = $this->GetKeywordExcludeArray();
		
		//Logger::Msg("exclude");
		//Logger::Msg($aExclude);
		
		
		foreach($aWords as $sWord => $iFreq) {
			if(!in_array($sWord,$aExclude)) {
				$a[$sWord] = $iFreq;
			}
		}
		
		//Logger::Msg("output");
		//Logger::Msg($a);
		
		return $a;
	}
	

	function indexPlacement($oProfile,$sIndex = "keyword_idx_2") {

		/* handle keywords excluded from index */
		$this->SetKeywordExcludeArray(array());		
		if (strlen($oProfile->GetKeywordExclude()) > 1) {
			$this->SetKeywordExcludeArray($this->getWords($oProfile->GetKeywordExclude(),0,$split = true, $stemmed = true,$count = false,$filter = false));
		}

		// title
		$aWords = $this->getWords($oProfile->GetTitle(),WEIGHT_HIGH,$split = true, $stemmed = true);			
		$this->UpdateIndex($oProfile->GetId(),2,$aWords,$sIndex);

		//Logger::Msg($aWords,'plaintext');

		// short desc
		$aWords = $this->getWords($oProfile->GetDescShort(),WEIGHT_MED,$split = true, $stemmed = true);
		$this->UpdateIndex($oProfile->GetId(),2,$aWords,$sIndex);

		//Logger::Msg($aWords,'plaintext');

		// full desc
		$aWords = $this->getWords($oProfile->GetDescLong(),WEIGHT_LOW,$split = true, $stemmed = true);
		$this->UpdateIndex($oProfile->GetId(),2,$aWords,$sIndex);

		//Logger::Msg($aWords,'plaintext');

		// category, activity, country, continent 
		$str = $oProfile->GetActivityTxt() . " " .$oProfile->GetCategoryTxt() . " " .$oProfile->GetCountryTxt() . " " . $oProfile->GetContinentTxt() . " " . $oProfile->GetLocation();
		$aWords = $this->getWords($str,WEIGHT_MED,$split = true, $stemmed = true);
		$this->UpdateIndex($oProfile->GetId(),2,$aWords,$sIndex);		

		//Logger::Msg($aWords,'plaintext');

		// set the last_indexed date
		$this->db->query("UPDATE profile_hdr SET last_indexed = now()::timestamp WHERE id = ".$oProfile->GetId()."");

		unset($aWords,$str,$oProfile,$sIndex);

	}


	function indexCompany($oCProfile,$sIndex = "keyword_idx_2") {


		if (!is_numeric($oCProfile->GetId())) return;

		$this->SetKeywordExcludeArray(array());		
		if (strlen($oCProfile->GetKeywordExclude()) > 1) {
			$this->SetKeywordExcludeArray($this->getWords($oCProfile->GetKeywordExclude(),0,$split = true, $stemmed = true,$count = false,$filter = false));
		}
		
		
		// STEMMED - KEYWORD SEARCH INDEX

		// title (as words)
		$aWords = $this->getWords($oCProfile->GetTitle(),WEIGHT_HIGH,$split = true, $stemmed = true);
		$this->UpdateIndex($oCProfile->GetId(),1,$aWords,$sIndex);
		
		// company title (as phrase)
		$aWords = $this->getWords($oCProfile->GetTitle(),WEIGHT_HIGH,$split = false, $stemmed = false);
		$this->UpdateIndex($oCProfile->GetId(),1,$aWords,$sIndex);
		
		// short desc
		$aWords = $this->getWords($oCProfile->GetDescShort(),WEIGHT_MED,$split = true, $stemmed = true);
		$this->UpdateIndex($oCProfile->GetId(),1,$aWords,$sIndex);
		
		// desc long
		$aWords = $this->getWords($oCProfile->GetDescLong(),WEIGHT_LOW,$split = true, $stemmed = true);
		$this->UpdateIndex($oCProfile->GetId(),1,$aWords,$sIndex);
		
		// job info
		$aWords = $this->getWords($oCProfile->GetPlacementInfo(),WEIGHT_LOW,$split = true, $stemmed = true);
		$this->UpdateIndex($oCProfile->GetId(),1,$aWords,$sIndex);
		
		$extra = $oCProfile->GetActivityTxt() . " " .$oCProfile->GetCategoryTxt() . " " .$oCProfile->GetCountryTxt() . " " . $oCProfile->GetContinentTxt();

		// associated info (activity, category, country)
		$aWords = $this->getWords($extra,WEIGHT_HIGH,$split = true, $stemmed = true);
		$this->UpdateIndex($oCProfile->GetId(),1,$aWords,$sIndex);

		
		// set the last_indexed date
		$this->db->query("UPDATE company SET last_indexed = now()::timestamp WHERE id = ".$oCProfile->GetId()."");

		unset($title,$desc,$desc_long,$job_info,$extra,$aWords);

	}



	function deleteEntryFromIndex($id,$type,$index_tbl = 'keyword_idx_2') {

		if (!is_numeric($id)) return;

		if ($type == "company") {
			$sql = "DELETE FROM ".$index_tbl." WHERE type = 1 AND id = $id";
			if ($this->debug) print "SQL : ".$sql ." \n"; 
			$this->db->query($sql);
		} elseif ($type == "placement") {
			if ($this->debug) print "SQL : ".$sql ." \n"; 
			$this->db->query("DELETE FROM ".$index_tbl." WHERE type = 2 AND id = $id");
		}

	}

}


?>
