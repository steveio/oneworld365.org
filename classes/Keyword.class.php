<?php


class Keyword {
	
	
	public function Get($iSiteId,$sLinkTo,$iLinkId) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		global $_CONFIG;
		
		$db->query("SELECT 
						keyword 
					FROM 
						seo_keyword k,
						seo_keyword_map m
					WHERE 
						m.site_id = ".$iSiteId."
						AND m.link_to = ".$sLinkTo."
						AND m.link_id = ".$iLinkId."
						AND m.keyword_id = k.id
					");
		
	}
}

?>