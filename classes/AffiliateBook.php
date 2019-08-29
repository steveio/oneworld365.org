<?php


define("AFFILIATE_BOOK_TBL","affiliate_book");
define("AFFILIATE_BOOK_MAP_TBL","affiliate_book_map");



class AffiliateBook {
	
	private $id;
	private $name;
	private $link_html;
	
	public function SetId($id) {
		$this->id = $id;
	}
	
	public function GetId() {
		return $this->id;
	}

	public function SetName($name) {
		$this->name = $name;
	}
	
	public function GetName() {
		return $this->name;
	}

	public function SetLinkHtml($link_html) {
		$this->link_html = $link_html;
	}
	
	public function GetLinkHtml() {
		return $this->link_html;
	}
	
	
	// return an array of affiliate books mapped to section id of website_id
	public function GetBySectionUri($website_id,$section_uri) {

		global $db;
		
		$sql = "SELECT b.id,b.name,b.affiliate_html 
				FROM ".AFFILIATE_BOOK_MAP_TBL." m,
				".AFFILIATE_BOOK_TBL." b
				WHERE m.website_id = ".$website_id." AND 
				m.section_uri = '".$section_uri."' AND 
				m.book_id = b.id
				";
		
		
		$db->query($sql);
		
		if ($db->getNumRows() < 1) return array();
		
		$aRes = $db->getRows();

		$aBookLink = array(); 
		
		foreach($aRes as $a) {
			$oLink = new AffiliateBook();
			$oLink->SetId($a['id']);	
			$oLink->SetName(stripslashes($a['name']));
			$oLink->SetLinkHtml(stripslashes($a['affiliate_html']));
			
			$aBookLink[] = $oLink; 
		}

		return $aBookLink;
		
	}
	
	
}
