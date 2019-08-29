<?

class TagCloud {

    public function __construct()
    {
        $this->TagCloud();    
    }

	function TagCloud(&$db) {
		$this->db = $db;
	}

	function GetCompanyTagCloud() {

		global $_CONFIG;

		$this->db->query("select word, count from (select word,sum(count) as count from ".$_CONFIG['tagcloud_table']." group by word order by sum(count) desc limit 55) as foo order by word asc;");
		$aKeywords = $this->db->getRows();


		/*
		$iMax = 0;
		foreach($aKeywords as $k) {
			$iMax = ($k['count'] > $iMax) ? $k['count'] : $iMax;
		}
		*/

		$iMax = 900; // hardcoded to balance the word distribution

		$iSlice = round(($iMax / 3) / 6,0);


		$i = 0;
		foreach($aKeywords as $k) {
			switch(true) {
				case $k['count'] <= $iSlice :
					$aKeywords[$i]['css'] = "not-popular";
					break;
				case (($k['count'] <= ($iSlice * 2)) && ($k['count'] > ($iSlice))) :
					$aKeywords[$i]['css'] = "not-very-popular";
					break;
				case (($k['count'] <= ($iSlice * 3)) && ($k['count'] > ($iSlice * 2))) :
					$aKeywords[$i]['css'] = "somewhat-popular";
					break;
				case (($k['count'] <= ($iSlice * 4)) && ($k['count'] > ($iSlice * 3))) :
					$aKeywords[$i]['css'] = "popular";
					break;
				case (($k['count'] <= ($iSlice * 5)) && ($k['count'] > ($iSlice * 4))) :
					$aKeywords[$i]['css'] = "very-popular";
					break;
				case ($k['count'] > ($iSlice * 5)) :
					$aKeywords[$i]['css'] = "ultra-popular";


					break;
			}
			$i++;
		}
		return $aKeywords;
	}

	function renderTagCloudMarkup($aTags) {
		$s = "<ol class='tag-cloud' style='padding: 0px 0px 0px 0px;'>\n";
		foreach($aTags as $k) {
			$s.= "<li class='". $k['css'] ."'><a href='javascript: void(null)' onclick=\"javascript: doSearch('tag','". $k['word']."',0);\" class='tag'>". $k['word']."</a></li>\n";
		}
		return $s .= "</ol>\n";
	}

}


?>