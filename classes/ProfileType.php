<?php



class ProfileType {
	
	private $aProfileType;
	
	
	public function __Construct($profile_id_filter, $profile_type_filter) {
		
		$this->SetFromDb($profile_id_filter, $profile_type_filter);
		
	}

	public function Count() {
		return count($this->aProfileType);
	}
	
	public function Get() {
		return $this->aProfileType;
	}
	
	public function Add($profile_type) {
		if (!in_array($profile_type, $this->aProfileType)) {
			$this->aProfileType[] = $profile_type; 
		}
	}
	
	public function SetProfileType($aProfileType) {
		$this->aProfileType = $aProfileType;
	}
	
	/*
	 * Get all profile types, optionally filtering by type ( 0 = company profile, 1 = placement )
	 * 
	 */
	public function SetFromDb($id_filter, $type_filter) {
		
		global $db;
		
		$where_sql = 'WHERE 1=1 ';
		if (is_array($id_filter) && count($id_filter) >= 1) $where_sql .= "AND id IN (".implode(",",$id_filter).")";	
		if (is_numeric($type_filter)) $where_sql .= " AND type = ".$type_filter;
		
		$sql = "SELECT id,name,description FROM profile_types ".$where_sql." ORDER BY id ASC";

		$db->query($sql);

		$result = array();
		
		if ($db->getNumRows() >= 1) {

			foreach($db->getRows() as $row) {
				$result[$row['id']]['name'] = $row['name'];	
				$result[$row['id']]['description'] = $row['description'];
			}
		}
		
		$this->SetProfileType($result);
				
	}
	
	
	public function GetDDlist($selected_id,$id = '', $name = '', $css_class = '') {
		
		$aValues = array();
		foreach($this->Get() as $vid => $a) {
			$aValues[$vid] = $a['description'];
		}
		
		$oSelect = new Select($id,$name,$css_class,$aValues,$bKeysSameAsValues = false,$selected_id);
		$oSelect->SetNoDefault();
		
		return $oSelect->GetHtml();
		
	}
	
}


?>