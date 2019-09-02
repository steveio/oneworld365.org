<?php



class SQLBuilder {
	
	
	/*
	 * Generate and SQL INSERT statement 
	 * 
	 * @param object (GetX() method must be present & naming convention must conform to $aFields map
	 * @param array fields mapping array('object->methodname' => 'sql_fieldname');
	 * @param string sql table name
	 * @param array fields that should use a sequence
	 * @param array fields that should use current timestamp 
	 */
	public static function CreateInsert($oObj,$aFields,$sTblName,$aSequence = array(),$aTimestamp = array()) {

		foreach($aFields as $k => $v) {
			if (array_key_exists($v,$aSequence)) {
				$aValues[$v] = "nextval('".$aSequence[$v]."')";
			} elseif (array_key_exists($v,$aTimestamp)) {
				$aValues[$v] = "now()::timestamp";
			} else {
				$fname = "Get".ucfirst($k);
				$aValues[$v] = (is_int($oObj->$fname())) ? $oObj->$fname() : "'".addslashes($oObj->$fname())."'" ;
			}			
		}
		
		return $sql = "INSERT INTO ".$sTblName." (".implode($aFields,",").") VALUES (".implode($aValues,",").");";
		
	}

	/*
	 * Generate and SQL UPDATE statement 
	 * 
	 * @param object (GetX() method must be present & naming convention must conform to $aFields map
	 * @param array fields mapping array('object->methodname' => 'sql_fieldname');
	 * @param string sql table name
	 */
	public static function CreateUpdate($oObj,$aFields,$sTbl) {

		foreach($this->aFields as $k => $v) {
			$fname = "Get".ucfirst($k);
			$aValues[$v] = (is_int($oObj->$fname())) ? $this->$fname() : "'".addslashes($oObj->$fname())."'" ;			
		}
		
		return $sql = "INSERT INTO ".$sTblName." (".implode($aFields,",").") VALUES (".implode($aValues,",").")";
		
	}
	
	
}


?>