<?


class CanonicalTag {

	private $primary_url;

	public function __Construct() {}

	public function GetByUri($request_uri, $site_id) {
		
		global $db;
		
		if (strlen($request_uri) < 1) return FALSE; 
		
		$sql = "SELECT w.name as hostname, m.site_id,m.request_uri_to FROM cross_domain_map m, website w WHERE m.site_id = w.id AND m.request_uri_from = '".$request_uri."'";
		
		$db->query($sql);
		
		if ($db->getNumRows() == 1) {
			$row = $db->getRow();
			
			if ($site_id != $row['site_id']) {
				$this->primary_url = "http://www.".$row['hostname'].$row['request_uri_to'];
			}
		}
		
	}

	public function Render() {
		
		if (strlen($this->primary_url) > 1) {
			return "<link rel=\"canonical\" href=\"".$this->primary_url."\" />";
		}
	}

}


?>