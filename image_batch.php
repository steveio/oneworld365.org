<?php

die("DEMISED - do not use, images are re-sized at upload time, this routine will delete images");

$sBasePath = "/www/vhosts/oneworld365.org/htdocs";

require_once($sBasePath."/conf/config.php");
require_once($sBasePath."/classes/db_pgsql.class.php");
require_once($sBasePath."/classes/logger.php");
require_once($sBasePath."/classes/cache.class.php");
require_once($sBasePath."/classes/image.class.php");
require_once($sBasePath."/classes/file.class.php");
require_once($sBasePath."/classes/template.class.php");
require_once($sBasePath."/classes/ProfileInterface.php");
require_once($sBasePath."/classes/ProfileAbstract.class.php");
require_once($sBasePath."/classes/ProfileFactory.class.php");
require_once($sBasePath."/classes/ProfilePlacement.class.php");
require_once($sBasePath."/classes/ProfileCompany.class.php");
require_once($sBasePath."/classes/ProfileGeneral.class.php");
require_once($sBasePath."/classes/ProfileTour.class.php");
require_once($sBasePath."/classes/ProfileJob.class.php");



$db = new db($dsn,$debug = false);



define("JOBNAME","IMAGE_BATCH");


if (LOG) Logger::DB(3,JOBNAME,'STARTED PROCESSING');



/* get placement profiles that require image refresh */
$db->query("SELECT 
				p.id
				,p.url_name as placement_url_name
				,p.type
				,p.img_url1
				,p.img_url2
				,p.img_url3
				,p.img_url4
				,c.url_name as company_url_name
			FROM 
				".$_CONFIG['profile_hdr_table']." p
				,".$_CONFIG['company_table']." c
			WHERE  
				p.company_id = c.id
				AND p.img_status = 1
			ORDER BY 
				p.last_updated desc");
			
if ($db->getNumRows() < 1) {
	if (LOG) Logger::DB(3,JOBNAME,'0 PROFILES TO PROCESS - FINISHED');
} else {

	$aResult = $db->getRows();
	
	if (LOG) Logger::DB(2,JOBNAME,count($aResult).' PROFILES TO PROCESS');
	

	$oIP = new ImageProcessor();
	$oIP->ProcessImages($aResult,PROFILE_IMAGE,$bCacheUpdate = TRUE);
	
	if (LOG) Logger::DB(2,JOBNAME,'FINISHED PROCESSING');
}

/* get company profiles that require image refresh */
$db->query("SELECT 
				c.id
				,c.url_name
				,0 as type
				,c.img_url1
				,c.img_url2
				,c.img_url3
				,c.img_url4
			FROM 
				".$_CONFIG['company_table']." c
			WHERE 
				c.img_status = 1
			ORDER BY 
				c.last_updated desc");
			
if ($db->getNumRows() < 1) {
	if (LOG) Logger::DB(3,JOBNAME,'0 COMP PROFILES TO PROCESS - FINISHED');
} else {

	$aResult = $db->getRows();
	
	if (LOG) Logger::DB(2,JOBNAME,count($aResult).' COMP PROFILES TO PROCESS');
	
	$oIP = new ImageProcessor();
	$oIP->ProcessImages($aResult,PROFILE_IMAGE,$bCacheUpdate = TRUE);

	
	if (LOG) Logger::DB(2,JOBNAME,'FINISHED PROCESSING');
}

/*
 * Get COMP profiles where logo refresh flag = true
 * 
 */

$db->query("SELECT 
				c.id
				,c.url_name
				,0 as type
				,c.logo_url
				,c.logo_banner_url
			FROM 
				".$_CONFIG['company_table']." c
			WHERE 
				c.logo_refresh_fl = 'T'
			ORDER BY 
				c.last_updated desc");
			
if ($db->getNumRows() < 1) {
	if (LOG) Logger::DB(3,JOBNAME,'0 COMP LOGO TO PROCESS - FINISHED');
} else {

	$aResult = $db->getRows();
	
	if (LOG) Logger::DB(2,JOBNAME,count($aResult).' COMP LOGO TO PROCESS');
	
	$oIP = new ImageProcessor();
	$oIP->ProcessImages($aResult,LOGO_IMAGE,$bCacheUpdate = TRUE);

	
	if (LOG) Logger::DB(2,JOBNAME,'FINISHED PROCESSING');
}

unset($oIP);
unset($aResult);
unset($db);



?>
