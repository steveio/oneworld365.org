<?



/*
 * A container object to represent an HTML layout
 *
 */


class Layout implements TemplateInterface {

   private $sId;
   private $iCols;
   private $bPad;
   private $bBorder;
   private $iTitle;   
   private $sContent;
   protected $oTemplate;

   public function __Construct() {
   	  $this->oTemplate = new Template();
	  $this->SetCols(4);
	  $this->bPad = FALSE;
	  $this->bBorder = FALSE;
   }

	public function SetId($sId) {
		$this->sId = $sId;
	}

	public function GetId() {
		return $this->sId;
	}

	public function SetTitle($sTitle) {
		$this->sTitle = $sTitle;
	}

	public function GetTitle() {
		return $this->sTitle;
	}
	
	public function SetPad($bPad) {
		$this->bPad = $bPad;
	}

	public function GetPad() {
		return ($this->bPad) ? "pad" : "";
	}

	public function SetBorder($bBorder) {
		$this->bBorder = $bBorder;
	}

	public function GetBorder() {
		return ($this->bBorder) ? "border" : "";
	}
	
	
   public function GetCols() {
		$a = array(1=>"one",2=>"two",3=>"three",4=>"four","five");
		return $a[$this->iCols];
   }

   public function SetCols($iCols) {
      $this->iCols = $iCols;
   }

   public function SetContent($content) {
	   if (is_object($content)) {
	      $this->sContent .= $content->Render();    /* content must conform to TemplateInterface */
	   } else {
	      $this->sContent .= $content;
	   }

   }

   public function GetContent() {
      return $this->sContent;
   }

   
    /* allows template to get named values */
	public function Get($k) {
		return $this->oTemplate->Get($k);
	}
	
	/* allows caller to set named values to be passed to template */
	public function  Set($k,$v) {
		$this->oTemplate->Set($k,$v);	
	}
   
   public function LoadTemplate($sFilename) {
		$this->oTemplate->SetFromArray(array(
										"ID" => $this->GetId(),
										"COLS" => $this->GetCols(),
										"PAD" => $this->GetPad(),
										"TITLE" => $this->GetTitle(),
										"CONTENT" => $this->GetContent()
			
										));
		$this->oTemplate->LoadTemplate($sFilename);
	}

	public function Render() {
		return $this->oTemplate->Render();
	}


}






?>
