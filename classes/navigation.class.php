<?


/*
 * Classes to represent the primary navigation
 *
 */

class Nav implements TemplateInterface {

		private $oTemplate;
		private $aSections;

		public function __Construct() {
			$this->aSections = array();
		}


		public function GetSections() {
			return $this->aSections;
		}

		public function SetSection($oSection) {
			$this->aSections[] = $oSection;
		}


        public function LoadTemplate($sFilename) {

                $this->oTemplate = new Template();
                $this->oTemplate->SetFromArray(array("SECTIONS" => $this->GetSections() ));
                $this->oTemplate->LoadTemplate($sFilename);

        }


        public function Render() {
                return $this->oTemplate->Render();
        }
	

}




class NavSection {

	private $aSubSection;
	private $sTitle;
	private $sDesc;
	private $sLink;
	private $bActive; /* is this section selected? */

	public function __Construct() {
		$this->aSubSection = array();
		$this->bActive = FALSE;
	}

	public function SetTitle($sTitle) {
		$this->sTitle = $sTitle;
	}

	public function GetTitle() {
		return $this->sTitle;
	}

	public function SetDesc($sDesc) {
		$this->sDesc = $sDesc;
	}

	public function GetDesc() {
		return $this->sDesc;
	}

	public function SetLink($sLink) {
		$this->sLink = $sLink;
	}

	public function GetLink() {
		return $this->sLink;
	}

	public function SetActive() {
		$this->bActive = TRUE;
	}

	public function GetActive() {
		return ($this->bActive) ? "active" : "";
	}

	public function GetSubSections() {
		return $this->aSubSection;
	}

	public function SetSubSection($oSubSection) {
		$this->aSubSection[] = $oSubSection;
	}

}


class NavSubSection {

	private $sTitle;
	private $sLink;
	private $sClass;
	private $aSubSection;
	
	public function __Construct() {
	}

	public function SetTitle($sTitle) {
		$this->sTitle = $sTitle;
	}

	public function GetTitle() {
		return $this->sTitle;
	}

	public function SetLink($sLink) {
		$this->sLink = $sLink;
	}

	public function GetLink() {
		return $this->sLink;
	}

	public function SetClass($sClass) {
		$this->sClass = $sClass;
	}

	public function GetClass() {
		return $this->sClass;
	}

	public function GetSubSections() {
		return $this->aSubSection;
	}
	
	public function SetSubSection($oSubSection) {
		$this->aSubSection[] = $oSubSection;
	}
	
	public function HasSubSections() {
		return (is_array($this->aSubSection) && (count($this->aSubSection)) >= 1) ? TRUE : FALSE;
	}
	
}


?>
