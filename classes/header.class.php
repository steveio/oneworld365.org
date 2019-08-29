<?


/*
 * Header represents a page XHTML header
 */




class Header extends Layout {

   private $sTitle;
   private $sDesc;
   private $sKeywords;
   private $sVerifyTag;
   private $sCanonicalTag;
   
   private $aJsInclude; /* an array of JS include objects */
   private $aCssInclude; /* an array of CSS include objects */

   private $sJsOnload; /* a string containing JS onload funcs */

   private $aNav; /* an array to hold top / main navigation */

   private $sCurrentTemplate; /* holds template path supplied by initial caller, to enable reload */

   private $bDisplayBanner;
   private $aBanner; /* array of ad banner defs */ 
   private $aFlashBanner; /* array flash banners */
   private $aHTMLBanner; /* array html banners */   
   
   private $aBreadCrumb; /* navigation breadcrumb array(url => label) */
   
   public function __Construct() {

	  parent::__Construct();

	  $this->aJsInclude = array();
	  $this->aCssInclude = array();
	  $this->aBanner = array();
	  $this->aFlashBanner = array();
	  $this->aBreadCrumb = array();
	  $this->bDisplayBanner = TRUE;
   }

   public function SetTitle($sTitle) {
		$this->sTitle = $sTitle;
   }

   public function GetTitle() {
		return $this->sTitle;
   }

   public function SetDisplayBanner($bValue) {
      $this->bDisplayBanner = $bValue;
   }

   public function SetDesc($sDesc) {
		$this->sDesc = $sDesc;
   }

   public function GetDesc() {
		return $this->sDesc;
   }

   public function SetKeywords($sKeywords) {
		$this->sKeywords = $sKeywords;
   }

   public function GetKeywords() {
		return $this->sKeywords;
   }

   public function SetVerifyTag($sTag) {
		$this->sVerifyTag = $sVerifyTag;
   }

   public function GetVerifyTag() {
		return $this->sVerifyTag;
   }
   
   public function GetCanonicalTag() {
		return $this->sCanonicalTag;
   }
   
   public function SetCanonicalTag($tag) {
		$this->sCanonicalTag = $tag;
   }
   
   public function SetBanners($aBanner) {
   		$this->aBanner = $aBanner;
   }
   
   public function GetBanners() {
   		return $this->aBanner;
   }

   public function SetFlashBanners($aFlashBanner) {
   		$this->aFlashBanner = $aFlashBanner;
   }
   
   public function GetFlashBanners() {
   		return $this->aFlashBanner;
   }
   
   public function SetHTMLBanners($aHTMLBanner) {
		$this->aHTMLBanner = $aHTMLBanner;
   }
   
   public function GetHTMLBanners() {
   		return $this->aHTMLBanner;
   }
   
   public function GetJsInclude() {
		$out = "";
		foreach($this->aJsInclude as $oInclude) {
			$out .= $oInclude->Render()."\n";
		}
		return $out;
   }

   public function SetJsInclude($oInclude) {
		$this->aJsInclude[] = $oInclude;
   }

	public function SetJsOnload($js_string) {
		$this->sJsOnload .= $js_string."\n\n";
	}

	public function GetJsOnload() {
		return $this->sJsOnload;
	}

   public function GetCSSInclude($sBrowserCode) {
	  $out = "";
	  if (is_array($this->aCssInclude[$sBrowserCode])) {
		foreach($this->aCssInclude[$sBrowserCode] as $oCss) {
			$out .= $oCss->Render()."\n";
		}
	  }
      return $out;
   }

   public function SetCssInclude($sBrowserCode, $oInclude) {
      $this->aCssInclude[$sBrowserCode][] = $oInclude;
   }

   public function SetNav($sName, $oNav) {
		$this->aNav[$sName] = $oNav;
   }

   public function GetNav($sName) {
		return $this->aNav[$sName];
   }

   public function SetBreadCrumb($aBreadCrumb) {
		$this->aBreadCrumb = $aBreadCrumb;
   }

   public function GetBreadCrumb() {
		return $this->aBreadCrumb;
   }
   
   
   public function LoadTemplate($sFilename) {

		if (strlen($sFilename) > 1) {
			$this->SetCurrentTemplate($sFilename);	
		}

		$this->oTemplate->SetFromArray(array(
										"TITLE" => $this->GetTitle(),
										"DESCRIPTION" => $this->GetDesc(),
										"KEYWORDS" => $this->GetKeywords(),
										"AD_BANNERS" => $this->GetBanners(),
										"FLASH_BANNERS" => $this->GetFlashBanners(),
										"HTML_BANNERS" => $this->GetHTMLBanners(),
										"JS_INCLUDE" => $this->GetJsInclude(),
										"JS_ONLOAD" => $this->GetJsOnload(),
										"CSS_GENERIC" => $this->GetCSSInclude("CSS_GENERIC"),
										"CSS_NOT_IE" => $this->GetCSSInclude("CSS_NOT_IE"),
										"CSS_IE6" => $this->GetCSSInclude("CSS_IE6"),
										"CSS_IE7" => $this->GetCSSInclude("CSS_IE7"),
										"CSS_IE8" => $this->GetCSSInclude("CSS_IE8"),
										"CANONICAL_TAG" => $this->GetCanonicalTag(),
										"TOP_NAV" => $this->GetNav("TOP_NAV")->Render(),
										"MAIN_NAV" => $this->GetNav("MAIN_NAV")->Render(),
										"BREADCRUMB" => $this->GetBreadCrumb(),
										"DISPLAY_BANNER" => $this->bDisplayBanner

										));

		$this->oTemplate->LoadTemplate($this->GetCurrentTemplate());
	}
	
	/* reloads template w/ latest header data */
	public function Reload() {
		$this->LoadTemplate($this->GetCurrentTemplate());
	}

	private function SetCurrentTemplate($template) {
		$this->sCurrentTemplate = $template;
	}

	private function GetCurrentTemplate() {
		return $this->sCurrentTemplate;
	}
}



class CssInclude {

	private $rel;
	private $type;
	private $href;
	private $media;

	function __Construct() {

		$this->rel = "stylesheet";
		$this->type = "text/css";
		$this->href = "";
		$this->media = "screen";

	}

	public function SetHref($href) {
		$this->href = $href;
	}

	public function GetRel() {
		return $this->rel;
	}

	public function GetType() {
		return $this->type;
	}

	public function GetHref() {
		return $this->href;
	}

	public function GetMedia() {
		return $this->media;
	}

	public function SetMedia($media) {
		$this->media = $media;
	}

	public function Render() {
		return "<link rel=\"".$this->GetRel()."\" type=\"".$this->GetType()."\" href=\"".$this->GetHref()."\" media=\"".$this->GetMedia()."\" />";
	}

}


class JsInclude {

	private $type;
	private $src;

	function __Construct() {
		$this->src = "";
		$this->type = "text/javascript";
	}

	public function SetSrc($src) {
		$this->src = $src;
	}

	public function GetSrc() {
		return $this->src;
	}

	public function GetType() {
		return $this->type;
	}

	public function Render() {
		return "<script type=\"".$this->GetType()."\" src=\"".$this->GetSrc()."\"></script>";
	}

}



?>
