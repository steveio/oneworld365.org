<?php

$aLink = $this->Get('LINK_ARRAY');
//Logger::Msg($aLink);
if (is_array($aLink)) {
	print "<div id='link_list'>";
	foreach($aLink as $oLink) {
		print "<ul>";
		print "<li><a href='".$oLink->GetUrl()."' title='".$oLink->GetTitle()."'>".$oLink->GetTitle()."</a>  <a class='p_small' title='Remove Link' href=\"javascript: void(null);\" onclick=\"javascript: RemoveLink('".$this->Get('WEBSITE_ID')."',".$oLink->GetId().",".$this->Get('LINK_TO_ID').")\">[X]</a></li>";
		print "</ul>";	
	}
	print "</div><!-- end link list -->";

}
?>