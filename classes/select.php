<?php


class Select {
	
	public function __construct($sId,$sName,$sClass,$aValues,$bKeysSameAsValues = false,$sSelected) {
			$this->sId = $sId;
			$this->sName = $sName;
			$this->sClass = $sClass;
			$this->aValues = $aValues;
			$this->bHasDefault = true;
			$this->sDefaultLabel = "";
			$this->sDefaultValue = "null";
			$this->bKeysSameAsValues = $bKeysSameAsValues;
			$this->sSelected = $sSelected;
	}
	
	public function GetHtml() {
		$sOutStr .= "<select id=\"".$this->sId."\" name=\"".$this->sName."\" class=\"".$this->sClass."\">";
		if ($this->bHasDefault) {
			$sOutStr .= "<option value=\"".$this->sDefaultValue."\">".$this->sDefaultLabel."</option>\n";
		}
		if (is_array($this->aValues)) {
			foreach($this->aValues as $sKey => $sVal) {
				$sKey = ($this->bKeysSameAsValues) ? $sVal : $sKey;				
				$sSelected = ($sKey == $this->sSelected) ? "selected" : ""; 
				$sOutStr .= "<option value=\"".$sKey."\" $sSelected>".$sVal."</option>\n";
			}
		}
		$sOutStr .= "</select>";
		
		return $sOutStr;
	}

}

?>