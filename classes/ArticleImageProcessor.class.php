<?php

/**
 * 
 * Article image processor 
 * 
 * Tasked with locating articles with no attached images (uploaded via admin CMS)
 * article body is parsed for embedded image URLs (images added via tinyMCE editor)
 * re-szied image proxies are generated and one of these is attached to article 
 * 
 * @author stevee
 *
 */

class ArticleImageProcessor {
	
	
	public function __construct() {
	}
	
	public function getArticleNoImageId() {
		
	    global $db;
	    
	    $sql = "select
        	    a.id
        	    from
        	    article a,
        	    article_map m
        	    where
        	    a.id = m.article_id
        	    AND NOT EXISTS (
        	        select 1 from image_map i
        	        where
        	        i.link_to = 'ARTICLE'
        	        AND i.link_id = a.id
        	    )";

	    $db->query($sql);
						
		if ($db->getNumRows() < 1) return false;
						
		return $aResult = $db->getRowsNum();
	}

	public function process($id)
	{
	    try {
    	    if (!is_numeric($id)) throw new Exception("Invalid article id: ".$id);

    	    if (LOG) Logger::DB(3,JOBNAME,'Processing Article Id: '.$id);

    	    $oArticle = new Article();
    	    $oArticle->GetFetchMode() == FETCHMODE__SUMMARY;
    	    $oArticle->GetById($id);

    	    if (LOG) Logger::DB(3,JOBNAME,'Processing Article: '.$oArticle->GetTitle());
    	    
    	    $strArticleBody = $oArticle->GetDescFull();
    	    $arrImgUrl = array();
    	    preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i',$strArticleBody, $arrImgUrl );
    	    if (count($arrImgUrl[1]) < 1) 
    	    {
    	        if (LOG) Logger::DB(3,JOBNAME,"No image URLs found in body: ".$id);
    	        //throw new Exception("No image URLs found in body: ".$id);
    	        return false;
    	    }
   	 
    	    if (LOG) Logger::DB(3,JOBNAME,'Found '.count($arrImgUrl)." image URLs in body");

    	    foreach($arrImgUrl[1] as $strImgUrl)
    	    {
    	        if (LOG) Logger::DB(3,JOBNAME,"Processing Image URL: ".$strImgUrl);

    	        $bIsAdminUploadedImage = $this->isAdminUploadedImage($strImgUrl);
    	            	        
    	        if ($bIsAdminUploadedImage)
    	        {
    	            /*
    	            $iImageId = $this->parseImageOidFromUrl($strImgUrl);
    	            
    	            if (LOG) Logger::DB(3,JOBNAME,'Image Id: '.$iImageId);
    	            
    	            $oImage = new Image();
    	            $oImage->GetById($iImageId);

    	            if (!is_numeric($oImage->GetId())) throw new Exception("Failed to fetch image: ".$iImageId);
    	            $oArticle->AttachImage($oImage);
                    */

    	        } else {
    	            if (LOG) Logger::DB(3,JOBNAME,"External image, invoke processor: ".$strImgUrl."\n");
    	            // convert URL to Path for images uploaded to server
    	            //   http://www.oneworld365.org/img/101/Volunteer in Panama.jpg
    	            //   /www/vhosts/oneworld365.org/htdocs/img/101/Volunteer in Panama.jpg
    	        }

    	    }


	    } catch (Exception $e) {
	        throw $e;
	    }
	}

	public function parseImageOidFromUrl($strImgUrl)
	{
	    preg_match("/http:\/\/www\.oneworld365\.org\/img\/[0-9]{3}\/[0-9]{2}\/([0-9]{5})/",$strImgUrl,$arrMatches );
	    return($arrMatches[1]);
	}

	public function isAdminUploadedImage($strImgUrl)
	{
	    if (preg_match("/http:\/\/www\.oneworld365\.org\/img\/[0-9]{3}\/[0-9]{2}\//",$strImgUrl)) return true;
	}

	public function isCMSUploadedImage($strImgUrl)
	{
	    if (preg_match("/http:\/\/www\.oneworld365\.org\/img\/[0-9]{?}\//",$strImgUrl)) return true;
	}
	
	public function generateImageProxies()
	{
	    //$oIP = new ImageProcessor_FileUpload;
	    //$result = $oIP->Process($aResult['TMP_PATH'],"ARTICLE",$article_id,$iImgType = PROFILE_IMAGE);
	    
	}
}
