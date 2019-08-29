<?php

/*
 * Option encapsulates lists of profile options 
 * 
 * 
 */



class Option {

	private $id;
	private $name;
	private $type;

	public function __Construct($id,$name,$type) {
		
		$this->id = $id;
		$this->name = $name;
		$this->type = $type;
		
	}
	
	
	public function GetId() {
		return $this->id;
	}

	public function GetName() {
		return $this->name;
	}

	public function GetType() {
		return $this->type;
	}
	
	public function GetCheckboxHtml($prefix = "opt_",$checked = false,$disabled_flag = false) {

		$key = $prefix.$this->GetId();
		
		$checked = ($checked) ? "checked" : "";
		$disabled = ($disabled_flag) ? "disabled" : "" ;
		
		return "<input type=\"checkbox\" class=\"option_checkbox\" name=\"".$key."\" ".$disabled."  ".$checked." ><label for=\"".$key."\" class=\"checkbox_label\">".$this->GetName()."</label>";
		
	}
}


class OptionGroup {
	
	private $aOption;
	
	public function GetAll() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db;
		
		$sql = "SELECT id,name,type FROM option ORDER BY type,sort_order ASC";
		
		$db->query($sql);
		
		$aTmp = array();
		
		if ($db->getNumRows() >= 1) {
			$aTmp = $db->getObjects();				
		}
		
		foreach($aTmp as $o) {
			$this->aOption[$o->type][$o->id] = new Option($o->id,$o->name,$o->type); 
		}
				
	}
	
	public function GetByPlacementId($id,$prefix) {

		global $db;
		
		if (!is_numeric($id)) return array();

		$sql = "SELECT option_id as id FROM prod_opt_map WHERE prod_id =".$id;

		$db->query($sql);

		$aId = array();
		if ($db->getNumRows() >= 1) {
			$aTmp = $db->getRows();
			foreach($aTmp as $a) {
				$aId[$prefix.$a['id']] = "on";
			}
		}

		return $aId;
	}
	
	
	public function GetHtml($aChecked,$disabled = false) {
		
		$aHtml = array();

		$aGroup = array("ACCOM" => "Accomodation","MEALS" => "Meals","TRAVEL" => "Travel");
		
		foreach($aGroup as $code => $label) {
			
			$one_checked = FALSE;
			$sHtml .= "<h3>".$label."</h3>";
						
			foreach($this->aOption[$code] as $oOption) {

				$sHtml .= "<ul class='unstyled option-group'>\n";
				
				$prefix = "opt_";
				$key = $prefix.$oOption->GetId();
				
				$checked = array_key_exists($key,$aChecked) ? "checked" : "";
				if ($checked) {
					$one_checked = TRUE;
					$sHtml .= "<li>".$oOption->GetCheckboxHtml($prefix,$checked, $disabled)."</li>";
				}
				
				$sHtml .= "</ul>";
			}
			if ($one_checked) {
				$out .= $sHtml;
			}
			
		}
			
		return $out;
	}

	
}

?>