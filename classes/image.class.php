<?php


define("IMG_PATH_IDENTIFY","/usr/bin/identify");
define("IMG_PATH_CONVERT","/usr/bin/convert");

define("LANDSCAPE","L");
define("PORTRAIT","P");
define("SQUARE","S");

define("PROFILE_IMAGE",0);
define("LOGO_IMAGE",1);

define("LOGO__DIMENSIONS_MAXWIDTH", 500);
define("LOGO__DIMENSIONS_MINWIDTH", 100);
define("LOGO__DIMENSIONS_MAXHEIGHT", 300);
define("LOGO__DIMENSIONS_MINHEIGHT", 40);


define("LOGO__DIMENSIONS_SMALL_WIDTH", 120); /* width of auto generated logo small version */
define("LOGO__DIMENSIONS_SMALL_HEIGHT", 60); /* width of auto generated logo small version */

define("IMG_HOST","http://www.oneworld365.org/");
define("IMG_BASE_URL",IMG_HOST ."img/");
define("IMG_BASE_PATH","/www/vhosts/oneworld365.org/htdocs/img/");
define("IMG_SEQ","image_seq");



/*
 * serves to define available image thumbnail sizes
 *
 *
 */

class ImageSize {


	/*
	 * Returns thumbnail dimension info
	 *
	 * @param string size { "_s", "_m" etc }
	 * @param string aspect PORTRAIT || LANDSCAPE
	 * @param string type { DIMENIONS <width>x<height> || Width || Height
	 *
	 */
	public static function Get($size = '',$aspect = '',$type = "DIMENSIONS") {

		$a = array(
													"_s" => array(
																													LANDSCAPE => "100x75",
																													PORTRAIT => "75x100",
																													),
													"_sf" => array( /* fixed aspect ratio small (stretched) */
																													LANDSCAPE => "100x75"
																													),
													"_m" => array(
																													LANDSCAPE => "240x180",
																													PORTRAIT => "180x240",
																													),
													"_mf" => array(
																													LANDSCAPE => "240x180"
																													),
													"_l" => array(
																													LANDSCAPE => "500x334",
																													PORTRAIT => "500x334",
																													),
													"_lf" => array(
																													LANDSCAPE => "500x334"
																													)
																					);


		$s = $a[$size][$aspect];

		if ($type == "DIMENSIONS") return $s;
		$b = explode("x",$s);
		if ($type == "WIDTH") return $b[0];
		if ($type == "HEIGHT") return $b[1];

		if ($type == "ALL") return $a;
	}
}



/*
 * An instance of an image
 *
 *
 */
class Image {

	private $id;
	private $type;
	private $ext;
	private $dimensions;
	private $width;
	private $height;
	private $aspect;

	public function __Construct($id = '',$type = '',$ext = '',$dimensions = '',$width = '',$height = '',$aspect = '') {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;

		$this->id = $id;
		$this->type = $type;
		$this->ext = $ext;
		$this->dimensions = $dimensions;
		$this->width = $width;
		$this->height = $height;
		$this->aspect = $aspect;

	}

	public function GetById($id)
	{
	    global $db;

	    if (!is_numeric($id)) return false;

	    $sql = "SELECT
					i.*
				FROM image i
				WHERE 
                    i.id = ".$id;

	    $db->query($sql);
	    
	    if ($db->getNumRows() != 1) return false;

	    $aRow = $db->getRow(PGSQL_ASSOC);

	    $this->id = $aRow['id'];
	    $this->type = $aRow['type'];
	    $this->dimensions = $aRow['dimensions'];
	    $this->ext = $aRow['ext'];
	    $this->width = $aRow['width'];
	    $this->height = $aRow['height'];
	    $this->aspect = $aRow['aspect'];
	}

	public function GetId() {
		return $this->id;
	}

	public function SetId($id) {
		$this->id = (int) $id;
	}

	public function GetType() {
		return $this->type;
	}

	public function SetType($sType) {
		$this->type = $sType;
	}

	public function GetExt() {
		return $this->ext;
	}

	public function SetExt($sExt) {
		$this->ext = $sExt;
	}

	public function GetDimensions() {
		return $this->dimensions;
	}

	public function SetDimensions($sDimensions) {
		$this->dimensions = $sDimensions;
	}


	public function GetWidth() {
		return (int) $this->width;
	}

	public function SetWidth($sWidth) {
		$this->width = $sWidth;
	}

	public function GetHeight() {
		return (int) $this->height;
	}

	public function SetHeight($sHeight) {
		$this->height = $sHeight;
	}

	public function GetAspect() {
		return $this->aspect;
	}

	public function SetAspect($sAspect) {
		$this->aspect = $sAspect;
	}

	public function GetHtml($size,$alt = '', $class = '', $noOutputSize = FALSE) {

		$width = ($size != "") ? ImageSize::Get($size,$this->GetAspect(),"WIDTH") : $this->GetWidth();
		$height = ($size != "") ? ImageSize::Get($size,$this->GetAspect(),"HEIGHT") : $this->GetHeight();

		$size_str = ($noOutputSize) ? "width='".$width."' height='".$height : "";

		if (file_exists($this->GetPath($size))) {
			return "<img class='img-responsive img-rounded ".$class."' src='".$this->GetUrl($size)."' ".$size_str." alt='".$alt."' border='0' />";
		} else {
			return FALSE;
		}
	}

	public function GetNextId() {

		global $db;

		$this->SetId($db->getFirstCell("SELECT nextval('".IMG_SEQ."');"));
	}

	public function Save() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."() id = ".$this->GetId());

		global $db,$_CONFIG;

		if (!is_numeric($this->GetId())) $this->SetId(-1);

		$db->query("SELECT id FROM ".$_CONFIG['image']." WHERE id = ".$this->GetId());

		if ($db->getNumRows() == 1) {
			if (!$this->Update()) return false;
		} elseif ($db->getNumRows() < 1) {
			if (!$this->Add()) return false;
		}

		return true;

	}


	public function Add() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db,$_CONFIG;


		if($this->GetId() == -1) {
			$this->SetId($db->getFirstCell("SELECT nextval('".IMG_SEQ."');"));
		}

		$sql = "INSERT INTO ".$_CONFIG['image']." (id
												,type
												,ext
												,dimensions
												,width
												,height
												,aspect
											) VALUES (
												".$this->GetId()."
												,'".$this->GetType()."'
												,'".$this->GetExt()."'
												,'".$this->GetDimensions()."'
												,'".$this->GetWidth()."'
												,'".$this->GetHeight()."'
												,'".$this->GetAspect()."'
											);";

		$db->query($sql);

		if ($db->getAffectedRows() == 1) {
			return true;
		} else {
			Logger::DB(1,get_class($this)."::".__FUNCTION__."()",$sql);
		}

	}

	public function Update() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db,$_CONFIG;

		$sql = "UPDATE ".$_CONFIG['image']."
									SET
										type = '".$this->GetType()."'
										,ext = '".$this->GetExt()."'
										,dimensions = '".$this->GetDimensions()."'
										,width = '".$this->GetWidth()."'
										,height = '".$this->GetHeight()."'
										,aspect = '".$this->GetAspect()."'
									WHERE id = ".$this->GetId()."
										";

		$db->query($sql);

		if ($db->getAffectedRows() == 1) {
			return true;
		} else {
			Logger::DB(1,get_class($this)."::".__FUNCTION__."()",$sql);
		}


	}

	public function GetUrl($sSize = "") {

		$sPath = IMG_BASE_URL . $this->GetPartition();
		return $sPath."/".$this->GetId().$sSize.$this->GetExt();
	}


	public function GetPath($sSize = "") {

		$sPath = IMG_BASE_PATH . $this->GetPartition();
		if (!file_exists($sPath)) {
			mkdir  ( $sPath, $mode= 0777, $recursive= true );
			chmod ( $sPath , 0777 );
		}
		return $sPath."/".$this->GetId().$sSize.$this->GetExt();
	}


	private function GetPartition() {

		$i = sprintf("%08d", $this->GetId());

		$a[] = array();
		$a[0] = substr($i,0,3);
		$a[1] = substr($i,3,2);

		return $a[0]."/".$a[1];

	}

	/*
	 * Delete an image (including all thumbnails)
	 *
	 */
	public function Delete() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db,$_CONFIG;

		if (!is_numeric($this->GetId())) return false;

		File::Delete($this->GetPath());

		foreach(ImageSize::Get("","","ALL") as $k => $v) {
			File::Delete($this->GetPath($k));
		}

		$db->query("DELETE FROM ".$_CONFIG['image_map']." WHERE img_id = ".$this->GetId());

		$db->query("DELETE FROM ".$_CONFIG['image']." WHERE id = ".$this->GetId());

	}


}

/*
 * Represents a placeholder or blank image to be displayed when no other valid image exists
 *
 */
class BlankImage extends Image {

    public function GetHtml($size,$title = '',$class = '',$noOutputSize = false) {
		global $_CONFIG;

		$width = ($size != "") ? ImageSize::Get($size,$this->GetAspect(),"WIDTH") : '100px';
		$height = ($size != "") ? ImageSize::Get($size,$this->GetAspect(),"HEIGHT") : '80px';

		return "<img src='".$_CONFIG['url']."/images/1pix.gif' alt='".$title."' width='$width' height='$height' border='0' />";
	}
}


/*
 * A utility for processing images (resizing, downloading)
 *
 *
 */
class ImageProcessor {

	private $sImgPrefix; /* an image filename prefix */
	private $aSize; /* an array of thumnnail definitions including dimensions */
	private $sHost; /* the base host from which images are served */
	private $sBaseUrl; /* url to image folder */
	private $sBasePath; /* unix path to image folder */
	private $sPath2Identify; /* path to imagemagick identify command */
	private $sPath2Convert; /* path to imagemagick convert command */

	private $bResize; /* bool - create proxy images? */
	private $sResizeProfile; /* resize profile to use if creating proxy images */

	private $aProcessedIds;

	public function __construct() {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $_CONFIG;

		$this->aProcessedIds = array();

		$this->SetResizeFl(TRUE);
		$this->SetResizeProfile(PROFILE_IMAGE);

		$this->SetFileNamePrefix();

		$this->aSize = $_CONFIG['img_size_array'];

		$this->sHost = IMG_HOST;
		$this->sBaseUrl = IMG_BASE_URL;
		$this->sBasePath = IMG_BASE_PATH;

		$this->sPath2Identify = IMG_PATH_IDENTIFY;
		$this->sPath2Convert = IMG_PATH_CONVERT;

	}

	public function SetResizeFl($bFlag) {
		$this->bResize = $bFlag;
	}

	public function GetResizeFl() {
		return $this->bResize;
	}

	public function SetResizeProfile($type) {
		$this->sResizeProfile = $type;
	}

	public function GetResizeProfile() {
		return $this->sResizeProfile;
	}


	public function GetProcessedIds() {
		return $this->aProcessedIds;
	}

	public function SetProcessedId($id) {
		$this->aProcessedIds[] = $id;
	}


	public function SetFileNamePrefix($sPrefix = "img_") {
		$this->sImgPrefix = $sPrefix;
	}

	public function GetFileNamePrefix() {
		return $this->sImgPrefix;
	}

	public function GetBasePath() {
		return $this->sBasePath;
	}

	public function GetSizeSuffix($sSize) {
		return $this->aSize[$sSize]['SUFFIX'];
	}


	private function GetPath2Identify() {
		return $this->sPath2Identify;
	}

	private function GetPath2Convert() {
		return $this->sPath2Convert;
	}

	public function AddSize($size,$iWidth,$iHeight,$sAspect) {

		if ($sAspect == LANDSCAPE) {
			$l = $iWidth."x".$iHeight;
			$p = $iHeight."x".$iWidth;
		} else {
			$p = $iWidth."x".$iHeight;
			$l = $iHeight."x".$iWidth;
		}

		$this->aSize[] = array($size => array(
											LANDSCAPE => $l,
											PORTRAIT => $p,
											));
	}



	/*
	 * Returns an array of image-meta data extracted using imagemagick identify
	 *
	 * @param unix path to an image
	 * @return array result
	 */
	public function Identify($sPath) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."() path: ".$sPath);

		$aReturn = array(
							"type" => '',
							"dimensions" => '',
							"width" => '',
							"height" => '',
							"aspect" => ''
						);

		if (!file_exists($sPath)) return false;

		$sCmd = $this->GetPath2Identify() . " " . $sPath;

		if (DEBUG) Logger::Msg($sCmd);

		$aOut = array();

		exec($sCmd,$aOut);

		/* split the output from identify */
		$aBits = explode(" ",$aOut[0]);

		$aReturn['type'] = $aBits[1];

		switch($aReturn['type']) {
			case "GIF" :
				$aReturn['ext'] = ".gif";
				break;
			case "JPG" :
			case "JPEG" :
				$aReturn['ext'] = ".jpg";
				break;
			case "PNG" :
				$aReturn['ext'] = ".png";
				break;
			default :
				return false;
		}

		$aReturn['dimensions'] = $aBits[2];

		$aBits2= explode("x",$aBits[2]);
		$iWidth = (int) $aBits2[0];
		$iHeight = (int) $aBits2[1];
		$aReturn['width'] = $iWidth;
		$aReturn['height'] = $iHeight;
		$aReturn['aspect'] = ($iWidth > $iHeight) ? LANDSCAPE : PORTRAIT ;



		if (DEBUG) Logger::Msg($aReturn);

		return $aReturn;

	}

	public function Convert($oImage,$size) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		if (!is_object($oImage)) return false;
		//if (!array_key_exists($size,ImageSize::Get("","","ALL"))) return false;

		if (!in_array($size,array("_sm")))
		$dimensions = ($oImage->GetAspect() == LANDSCAPE) ? ImageSize::Get($size,LANDSCAPE,"DIMENSIONS") : ImageSize::Get($size,PORTRAIT,"DIMENSIONS");

		$format = "jpg";
		if (in_array($oImage->GetType(),array("JPEG","PNG"))) {
			$format = "jpg";
		} elseif($oImage->GetType() == "GIF") {
			$format = "gif";
		}

		$cmd = $this->GetPath2Convert(). " ";

		switch($size) {

			/*
			 * Choose the appropriate imagemagick syntax
			 */

			/* logo_small version */
			case "_logo" :
				/* calculate the required resize percentage */
				$actualWidth = $oImage->GetWidth();
				$targetWidth = LOGO__DIMENSIONS_SMALL_WIDTH;
				$actualHeight = $oImage->GetHeight();
				$targetHeight = LOGO__DIMENSIONS_SMALL_HEIGHT;

				/*
 				Logger::Msg("ActualWidth: ".$actualWidth);
				Logger::Msg("TargetWidth: ".$targetWidth);
				Logger::Msg("ActualHeight: ".$actualHeight);
				Logger::Msg("TargetHeight: ".$targetHeight);
				*/

				/* work out which dimension is larger and use this to calculate resize percentage */
				$widthDiff = ($actualWidth - $targetWidth);
				$heightDiff = ($actualHeight - $targetHeight);

				$dimension = ($widthDiff > $heightDiff) ? "WIDTH" : "HEIGHT";

				//Logger::Msg("Resize Dimension: ".$dimension);

				/* calculate re-size required to create small version */
				if ($dimension == "WIDTH") {
					$percent = (100 - ceil((($actualWidth - $targetWidth) / $actualWidth) * 100));
				} else {
					$percent = (100 - ceil((($actualHeight - $targetHeight) / $actualHeight) * 100));
				}

				//Logger::Msg("Resize Percent: ".$percent);

				$cmd .= $oImage->GetPath() ." -resize ".$percent."% -format ".$format." ".$oImage->GetPath("_sm");
				break;


			/* square - fixed aspect ration (crop) */
			case "_sq" :
				$cmd .= $oImage->GetPath()." -thumbnail x200 -resize '200x<' -resize 50% -gravity center -crop 100x100+0+0 +repage -format jpg -quality 91 ".$oImage->GetPath("_sq");
				break;
			/* rounded corners - might implement at some point */
			case "_rd" :
					$cmd .= $oImage->GetPath()." ";
					$cmd .= "     \( +clone  -threshold -1 \ ";
					$cmd .= "        -draw 'fill black polygon 0,0 0,15 15,0 fill white circle 15,15 15,0' \ ";
					$cmd .= "        \( +clone -flip \) -compose Multiply -composite \ ";
					$cmd .= "        \( +clone -flop \) -compose Multiply -composite \ ";
					$cmd .= "     \) +matte -compose CopyOpacity -composite  ".$oImage->GetPath($size);
				break;
			default : /* standard resize based on predefined dimensions (defined in ImageSize class) */
				$cmd .= $oImage->GetPath() ." -resize ".$dimensions."\! -format ".$format." ".$oImage->GetPath($size);
				break;
		}

		if (DEBUG) Logger::Msg($cmd);

		exec($cmd);


	}


	/*
	 * Download & Process Remote Images
	 *
	 * @parm array image url's
	 * @param string website object images are associated with (usually a profile)
	 * @param int id of website object link
	 */
	protected function Process($aUrl,$sLinkTo,$iLinkId,$iImgType = PROFILE_IMAGE) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db,$_CONFIG;


		foreach($aUrl as $sUrl) {

			$bErrorFl  = false;
			$db->query("BEGIN");

			Logger::DB(2,get_class($this)."::".__FUNCTION__."()","Processing Url : ".$sUrl." ,LinkTo : ".$sLinkTo." ,LinkId : ".$iLinkId);

			/* 1.  Download the remote image */
			$sFile = File::GetRemoteFile($sUrl);

			if (!$sFile) {
				Logger::DB(1,get_class($this)."::".__FUNCTION__."()","Download Error : ".$sUrl." ,LinkTo : ".$sLinkTo." ,LinkId : ".$iLinkId);
				$bErrorFl = true;
				$db->query("ROLLBACK");
				continue;
			}

			/* 2.  Save a new image */
			$oImage = new Image();
			$oImage->GetNextId();

			$sTmpPath = "/tmp/img_".$oImage->GetId();

			if (!File::Write($sFile,$sTmpPath)) {
				Logger::DB(1,get_class($this)."::".__FUNCTION__."()","File Write Error : ".$sUrl." ,LinkTo : ".$sLinkTo." ,LinkId : ".$iLinkId);
				$bErrorFl = true;
				$db->query("ROLLBACK");
				continue;
			}


			if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."() Processing Image path : ".$oImage->GetPath());

			/* 3.  Identify the image */
			$aResult = $this->Identify($sTmpPath);


			if (!$aResult) {
				Logger::DB(1,get_class($this)."::".__FUNCTION__."()","Identify Failed : ".$sTmpPath." ,LinkTo : ".$sLinkTo." ,LinkId : ".$iLinkId);
				$bErrorFl = true;
				$db->query("ROLLBACK");
				continue;
			}

			$oImage->SetExt($aResult['ext']);
			$oImage->SetType($aResult['type']);
			$oImage->SetWidth($aResult['width']);
			$oImage->SetHeight($aResult['height']);
			$oImage->SetDimensions($aResult['dimensions']);
			$oImage->SetAspect($aResult['aspect']);

			if (!in_array($oImage->GetType(),array("JPEG","PNG","GIF"))) {

				Logger::DB(1,get_class($this)."::".__FUNCTION__."()","Identify Failed - Not an Image : ".$sTmpPath." ".$oImage->GetType()." ,LinkTo : ".$sLinkTo." ,LinkId : ".$iLinkId);
				$bErrorFl = true;
				$db->query("ROLLBACK");
				continue;
			}

			/* 4.  Update the image db record with details obtained by identify*/
			if (!$oImage->Save()) {
				Logger::DB(1,get_class($this)."::".__FUNCTION__."()","Image Update Failed : ".$sTmpPath." ,LinkTo : ".$sLinkTo." ,LinkId : ".$iLinkId);
				$bErrorFl = true;
				$db->query("ROLLBACK");
				continue;
			}

			/* move image from tmp path to img folder */
			rename($sTmpPath,$oImage->GetPath());

			if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."() Image info : ".serialize($oImage));

			/* 5.  Convert the image to thumbnail resolutions */
			foreach(ImageSize::Get("","","ALL") as $k => $v) {
				if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."() Creating Thumbnail : ".$k);
				$this->Convert($oImage,$k);
			}

			/* 6.  INSERT image_map link */
			$db->query("INSERT INTO ".$_CONFIG['image_map']." (img_id,link_to,link_id,type) VALUES (".$oImage->GetId().",'".$sLinkTo."',".$iLinkId.",".$iImgType.");");
			if ($db->getAffectedRows() != 1) $bErrorFl = true;


			/*
			* everything OK, commit the transaction
			*/
			$db->query("COMMIT");


		} // end foreach url

		if (!$bErrorFl) {
			return true;
		}

	}

	/**
	 *
	 * @param array $aResult db rows
	 * @param constant $iImgType eg PROFILE_IMAGE
	 * @param boolean $bCacheRefresh trigger profile page cache invalidation
	 * @return boolean
	 */
	public function ProcessImages($aResult,$iImgType,$bCacheRefresh = FALSE) {

		if (!is_array($aResult)) return false;

		//$i = 0;

		foreach($aResult as $aProfile) {

			//if ($i++ == 10) die("finished");

			if (LOG) Logger::DB(3,JOBNAME,'PROCESSING : profile_id = '.$aProfile['id']);

			$oProfile = ProfileFactory::Get($aProfile['type']);
			$oProfile->SetId($aProfile['id']);

			$aImage = $oProfile->GetImages($iImgType);

			/*
			if (is_array($aImage)) {

				foreach($aImage as $oImage) {
					$oImage->Delete();
				}
				unset($aImage);
			}
			*/

			$aUrl = array();

			if ($iImgType == PROFILE_IMAGE) {
				/* 2.  Download & Process new profile images */
				$oProfile->SetImgUrl(1,$aProfile['img_url1']);
				$oProfile->SetImgUrl(2,$aProfile['img_url2']);
				$oProfile->SetImgUrl(3,$aProfile['img_url3']);
				$oProfile->SetImgUrl(4,$aProfile['img_url4']);
				$aUrl = $oProfile->GetImageUrls();
			}

			if ($iImgType == LOGO_IMAGE) {
				if (strlen(trim($aProfile['logo_url'])) > 1) {
					$aUrl[] = $aProfile['logo_url'];
				}
				if (strlen(trim($aProfile['logo_banner_url'])) > 1) {
					$aUrl[] = $aProfile['logo_banner_url'];
				}

			}

			if ($this->Process($aUrl,$oProfile->GetLinkTo(),$oProfile->GetId(),$iImgType)) {
				/* reset flags to indicate images have been processed */
				if ($iImgType == PROFILE_IMAGE) $oProfile->SetImageProcessStatus(0);
				if ($iImgType == LOGO_IMAGE) $oProfile->SetLogoProcessFlag("F");
				Logger::DB(2,get_class($this)."::".__FUNCTION__."()","Process OK  : ".$oProfile->GetLinkTo()." : ".$oProfile->GetId());
			} else {
				if ($iImgType == PROFILE_IMAGE) $oProfile->SetImageProcessStatus(-1);
				if ($iImgType == LOGO_IMAGE) $oProfile->SetLogoProcessFlag("F");
				Logger::DB(2,get_class($this)."::".__FUNCTION__."()","Process Failed  : ".$oProfile->GetLinkTo()." : ".$oProfile->GetId());
			}



			/* update the cached version of the profile */
			if ($bCacheRefresh) {

				global $_CONFIG;

				if ($aProfile['type'] == 0) {
					$url = $_CONFIG['url']."/company/".$aProfile['company_url_name'];
					$uri = "/company/".$aProfile['company_url_name'];
				} else {
					$url = $_CONFIG['url']."/company/".$aProfile['company_url_name']."/".$aProfile['placement_url_name']."/";
					$uri = "/company/".$aProfile['company_url_name']."/".$aProfile['placement_url_name'];
				}

				Cache::Generate($url,$uri,$_CONFIG['site_id'],$sleep = false);
			}

		}
	}
}


/*
 * Handles Uploading of images uploaded from file
 *
 *
 */
class ImageProcessor_FileUpload extends ImageProcessor {

	public function Process($aPath,$sLinkTo,$iLinkId,$iImgType = PROFILE_IMAGE) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");

		global $db,$_CONFIG;


		foreach($aPath as $sImgPath) {

			$bErrorFl  = false;
			$db->query("BEGIN");


			/* 1.  Validate the supplied path */
			if (!file_exists($sImgPath)) {
				$bErrorFl = true;
				continue;
			}

			/* 2.  Create a new image object */
			$oImage = new Image();
			$oImage->GetNextId();


			Logger::DB(2,get_class($this)."::".__FUNCTION__."()","Processing Img : ".$sImgPath." ,LinkTo : ".$sLinkTo." ,LinkId : ".$iLinkId);


			if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."() Processing Image path : ".$oImage->GetPath());

			/* 3.  Identify the image */
			$aResult = $this->Identify($sImgPath);


			if (!$aResult) {
				Logger::DB(1,get_class($this)."::".__FUNCTION__."()","Identify Failed : ".$sTmpPath." ,LinkTo : ".$sLinkTo." ,LinkId : ".$iLinkId);
				$bErrorFl = true;
				$db->query("ROLLBACK");
				continue;
			}

			$oImage->SetExt($aResult['ext']);
			$oImage->SetType($aResult['type']);
			$oImage->SetWidth($aResult['width']);
			$oImage->SetHeight($aResult['height']);
			$oImage->SetDimensions($aResult['dimensions']);
			$oImage->SetAspect($aResult['aspect']);

			if (!in_array($oImage->GetType(),array("JPEG","PNG","GIF"))) {

				Logger::DB(1,get_class($this)."::".__FUNCTION__."()","Identify Failed - Not an Image : ".$sTmpPath." ".$oImage->GetType()." ,LinkTo : ".$sLinkTo." ,LinkId : ".$iLinkId);
				$bErrorFl = true;
				$db->query("ROLLBACK");
				continue;
			}


			/* 4.  Update the image db record with details obtained by identify*/
			if (!$oImage->Save()) {
				Logger::DB(1,get_class($this)."::".__FUNCTION__."()","Image Update Failed : ".$sTmpPath." ,LinkTo : ".$sLinkTo." ,LinkId : ".$iLinkId);
				$bErrorFl = true;
				$db->query("ROLLBACK");
				continue;
			}

			//print $sImgPath;
			//print "<br />";
			//print $oImage->GetPath();
			//$cmd = "mv ".$sImgPath." ".$oImage->GetPath();
			//exec(print $cmd);

			//if (!file_exists($oImage->GetPath())) {
			//	print "FILE MOVE ERROR";
			//}

			/* move image from tmp path to img folder */
			if (!rename($sImgPath,$oImage->GetPath())) {
				print "error";
			}

			if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."() Image info : ".serialize($oImage));

			/* 5.  Convert the image to thumbnail resolutions */
			if ($this->GetResizeFl()) {
				if ($this->GetResizeProfile() == PROFILE_IMAGE) {
					foreach(ImageSize::Get("","","ALL") as $k => $v) {
						if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."() Creating Thumbnail : ".$k);
						$this->Convert($oImage,$k);
					}
				} elseif ($this->GetResizeProfile() == LOGO_IMAGE) {
					$this->Convert($oImage,"_logo");
				}
			}

			/* 6.  INSERT image_map link */
			$db->query("INSERT INTO ".$_CONFIG['image_map']." (img_id,link_to,link_id,type) VALUES (".$oImage->GetId().",'".$sLinkTo."',".$iLinkId.",".$iImgType.");");
			if ($db->getAffectedRows() != 1) $bErrorFl = true;


			$this->SetProcessedId($oImage->GetId());

			/*
			* everything OK, commit the transaction
			*/
			$db->query("COMMIT;");


		} // end foreach url


		if (!$bErrorFl) {
			return true;
		}

	}

}


?>
