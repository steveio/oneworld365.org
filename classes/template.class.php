<?php


/*
 * Simple Template System
 * 
 * A container data object for passing data to HTML templates
 * 
 * Also provides functionality to render common site elements (profiles,articles)  
 * using a set of pre-defined parameterised templates
 * 
 * Data structured as key => value pairs is injected into the template scope
 * via setter methdods Set() SetFromArray() SetFromObject()
 * 
 * Template is loaded by supplying a filename to LoadTemplate()
 * Parameterised template placeholders eg ::KEY:: are parsed and substituted 
 * with corresponding values from data scope
 * 
 * 
 * Useage -
 * 		$template->SetFromArray($aParams);
 * 		$template->LoadTemplate($sFilename = "article01.php");
 * 		$template->Render();
 * 
 * 
 */


class Template {

	private $sTemplatePath; /* the HTML template to be used in rendering the article */
	private $sTemplate; /* parameterised template */
	private $sTemplateHTML; /* the finished HTML template */

	private $data; /* a collection of Key => Value data to be displayed */
	
	public function __Construct() {
		
		$this->data = new TemplateData();
		
	}

	public function Get($k) {
		return $this->data->$k;
	}
	

	public function  Set($k,$v) {
		$this->data->$k = $v;	
	}


	public function SetFromArray($a) {
		foreach($a as $k => $v) {
			$this->data->$k = $v;
		}		
	}

	/*
	 * Object must implement GetVisible() method
	 * 
	 */
	public function SetFromObject($o) {
		
		foreach($o->GetVisible() as $k => $v) {
			$this->data->$k = $v;
		}		
	}

	
	public function GetTemplatePath() {
		return $this->sTemplatePath;
	}
	
	public function SetTemplatePath($sFilename) {
		
		global $_CONFIG;
		
		$this->sTemplatePath =  ROOT_PATH .  $_CONFIG['template_home'] ."/".$sFilename;
	}

	
	public function LoadTemplate($sFilename) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		unset($this->sTemplate);
		unset($this->sTemplateHTML);
	
		$this->SetTemplatePath($sFilename);
		
		$this->sTemplateHTML = $this->LoadTemplateFromFile();
		
		/*
		 * This is the old way, using simple paramterised html fragments 
		 */ 
		/*
		$this->sTemplate = file_get_contents($this->GetTemplatePath());
		$this->sTemplateHTML = $this->sTemplate;
		foreach($this->data as $k => $v) {
			$this->sTemplateHTML = preg_replace("/::$k::/",$v,$this->sTemplateHTML);
		}
		*/
				
	}
	
	
	public function LoadTemplateFromFile() {

		ob_start();
		include($this->GetTemplatePath());
		return ob_get_clean();
		
	}

	
	
	public function Render() {
		return $this->sTemplateHTML;
	}
	
	
}


/*
 * All components that require rendering via templates should implement this interface
 * 
 * LoadTemplate() and Render() act as wrappers to Template->LoadTemplate() and Template->Render()
 * 
 */

interface TemplateInterface {

	public function LoadTemplate($sFilename);
	
	public function Render();

}



/*
 * A data object used internally by Template
 * 
 */
class TemplateData {

}