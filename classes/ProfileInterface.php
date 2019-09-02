<?php


/*
 * 
 * All profile sub types should inherit this interface
 * 
 * 
 */

interface ProfileInterface {

	public function GetById($id);
	
	public function UpdateSubTypeRecord($p);
	
	public function AddSubTypeRecord($p);


}



?>