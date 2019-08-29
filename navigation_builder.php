<?

require_once("./classes/navigation.class.php");




$file = "./conf/nav.xml";
$xml = simplexml_load_file($file) or die ("Unable to load Navigation XML file!");


$oNav = new Nav();

foreach($xml->xpath('//section') as $section) {

		$oSection = new NavSection();
		$oSection->SetTitle((string)$section->title);
		$oSection->SetDesc((string)$section->desc);
		$oSection->SetLink((string)$section->link);

		foreach($section->subsections as $subsections) {
			foreach($subsections as $subsection) {
				$oSubSection = new NavSubSection();
				$oSubSection->SetTitle((string)$subsection->title);
				$oSubSection->SetLink((string)$subsection->link);
				$oSubSection->SetClass((string)$subsection->class);
				
				// only support for 2 level nav, could be made into recursive func in future
				foreach($subsection->subsections as $section_subsections) {
					foreach($section_subsections as $section_subsection) {
						$oLevel2SubSection = new NavSubSection();
						$oLevel2SubSection->SetTitle((string)$section_subsection->title);
						$oLevel2SubSection->SetLink((string)$section_subsection->link);
						$oLevel2SubSection->SetClass((string)$section_subsection->class);
						$oSubSection->SetSubSection($oLevel2SubSection);
						
					}
				}
				$oSection->SetSubSection($oSubSection);
			}
		}

		$oNav->SetSection($oSection);
}	


$oNav->LoadTemplate("nav_primary.php");




/*
 * Code tpo auto-generate list of Continent / Countries
 * 
$oCountry = new Country($db);
$oContinent = new Continent($db);

$d['COUNTRY_SELECT_PANEL'] = $oCountry->GetSelectListFullHTML($prefix = '',$return_objects = TRUE);


$aContinent = $d['COUNTRY_SELECT_PANEL']['continent'];
$aCountry = $d['COUNTRY_SELECT_PANEL']['country'];

foreach ($aContinent as $oContinent) {

	?>
	<subsection>
	<title><?= $oContinent->name; ?></title>
	<link><?= "/continent/".$oContinent->url_name; ?></link>
	<?php 
	foreach ($aCountry as $oCountry) {
		if ($oCountry->continent_id == $oContinent->id) {
		?>
		<subsection>
		<title><?= $oCountry->name; ?></title>
		<link><?= "/country/".$oCountry->url_name; ?></link>
		</subsection>		
		<?php 
		}
	}
	?>
	</subsection>
	<?php	
}


//var_dump($d['COUNTRY_SELECT_PANEL']);
die(__FILE__."::".__LINE__);

*/

?>