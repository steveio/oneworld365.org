<?php

/*
 * Pager.class.php
 *
 * Simple pagination of result sets
 *
 * Useage :
 *		Setup a pager instance passing a total result count :
 *		$oPage->GetByCount($total_results,$pager_id = "P1");
 *		
 *		Render the paging device (passing base url):
 *		$oPager->getPageNav($url);
 *
 * Device can be styled using following CSS :
 *
 * .page-links {margin:1.5em 0 1em 0; padding: 2em; width: 330px; }
 * .page-links ul {list-style:none;}
 * .page-links ul li {float:left; width:auto;}
 * .page-links ul li a {background:transparent; color:#006699; font-weight:bold; padding:0 0.3em; text-decoration:none; border: 1px solid #FFFFFF;}
 * .page-links ul li a:hover {text-decoration:underline; border: 1px solid #006699;}
 * .page-links ul li.previous a {background:url(/images/arrow-prev.gif) 30px 1px no-repeat; display:block; text-indent:-.25em; width:3.2em; }
 * .page-links ul li.currentpage {color:#f60; padding:0 0.3em; text-decoration:underline;}
 * .page-links ul li.next a {background:url(/images/arrow-next.gif) 0 1px no-repeat; display:block; text-indent:1em; width:2.6em;}
 * .page-links ul li.prev-inactive { color:#CCCCCC; font-weight:bold; padding:0 0.3em; text-decoration:none; background:url(/images/arrow-prev.gif) 30px 1px no-repeat; display:block; text-indent:-.25em; width:3.2em; }
 * .page-links ul li.next-inactive { color:#CCCCCC; font-weight:bold; padding:0 0.3em; text-decoration:none; background:url(/images/arrow-next.gif) 0 1px no-repeat; display:block; text-indent:1em; width:2.6em; }
 * 
*/



define("PAGER_ITEMS_PER_PAGE",10);
define("PAGER_RESULTS_PER_PAGE",10);


class PagedResultSet
{

	private $sPagerId; /* unique pager instance id */
	private $iResultCount; /* total no results */
	private $iResultsPerPage = PAGER_RESULTS_PER_PAGE; /* no items per page */
	private $page; /* the selected page */ 
	private $pageSize; /* no items per page */
	private $sUrl; /* Base Url for pager links   */

	public function __Construct() {


	}

	public function GetByCount($total_results,$pager_id = "P1")
	{
		$this->SetUrl("http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]);

		$this->SetPagerId($pager_id);
		$this->SetResultCount($total_results);
		$current_page = $this->GetOffsetFromUrl($this->GetPagerId());
		if ($current_page > $total_results)
		    $current_page = 1;

		$this->pageSize = $this->GetResultsPerPage();
		if ((int)$current_page <= 0) {
			$current_page = 1;
		}
		if ($current_page > $this->GetNumPages()) {
			$current_page = $this->GetNumPages();
		}
		$this->setPageNum($current_page);
		$this->iResultOffset = (($this->page -1) * $this->pageSize);
		/*
		print_r("<pre>");
		print_r($this);
		print_r("</pre>");
		die();
		*/
	}


	/* same as GetByCount() w/ reference to array which is sliced according to page being viewed */
	public function GetFromArray(&$array,$pager_id = "P1")
	{

		$this->aResults = $array;
		$total_results = count($array);		
	
        $this->SetUrl("http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]);
        //$this->SetResultsPerPage(PAGER_RESULTS_PER_PAGE);

        $this->SetPagerId($pager_id);
        $this->SetResultCount($total_results);
        $current_page = $this->GetOffsetFromUrl($this->GetPagerId());
        $this->pageSize = $this->GetResultsPerPage();
        if ((int)$current_page <= 0) {
                $current_page = 1;
        }
        if ($current_page > $this->GetNumPages()) {
                $current_page = $this->GetNumPages();
        }
        $this->setPageNum($current_page);
        $this->iResultOffset = (($this->page -1) * $this->pageSize);


		$start_offset = $this->iResultOffset;
		$end_offset = $this->pageSize;
		//print "start_idx: ".$start_offset."<br />";
		//print "end_idx: ".$end_offset."<br />";
		//print "total_results: ".$total_results;
	
		$array = array_slice($array,$start_offset,$end_offset);

	}


	/* returns current page id */
	public function GetOffsetFromUrl($sPagerId) {
		return $resultpage = $_REQUEST[$sPagerId];
	}

	public function SetResultsPerPage($i) {
		$this->iResultsPerPage = $i;
	}

	private function GetResultsPerPage() {
		return $this->iResultsPerPage;
	}

	private function SetResultCount($total_results) {
		$this->iResultCount = $total_results;
	}

	private function SetPagerId($id) {
		$this->sPagerId = $id;
	}

	private function GetPagerId() {
		return $this->sPagerId;
	}

	public function GetNumPages() {
		$iCount = (is_array($this->aResults)) ? count($this->aResults) : $this->iResultCount;
		return ceil($iCount / (float)$this->pageSize);
	}

	private function setPageNum($pageNum) {
		if ($pageNum > $this->GetNumPages() or $pageNum <= 0) return FALSE;
		$this->page = $pageNum;
	}

	public function GetPageNum() {
		return $this->page;
	}


	/* index of first pager link item */
	public function GetStartIdx() {
		if ($this->GetPageNum() <=  $this->GetResultsPerPage()) {
			return 1;
		} else {
			return $this->GetPageNum();
		}
	}

	private function GetEndIdx() {
		if ((($this->GetStartIdx() + $this->GetResultsPerPage()) -1) >= $this->GetNumPages()) {
			return $this->GetNumPages();
		} else {
			return ($this->GetStartIdx() + $this->GetResultsPerPage()) -1;
		}
	}

	private function ShowPreviousLink() {
		if ($this->GetPageNum() > $this->GetResultsPerPage()) {
			return TRUE;
		}
	}

	private function ShowNextLink() {
		if ($this->GetEndIdx() < $this->GetNumPages()) {
			return TRUE;
		}
	}

	private function GetPrevOffset() {
		return $this->GetPageNum()- $this->GetResultsPerPage();
	}

	private function GetNextOffset() {
		//if (($this->GetPageNum() + $this->GetResultsPerPage()) < $this->GetNumPages()) {
		//	return ($this->GetPageNum() + $this->GetResultsPerPage()) - ($this->GetPageNum() - $this->GetStartIdx()) ;
		//} else {
			return $this->GetPageNum()+1;
		//}
	}
	
	private function SetUrl($url) {
		$this->sUrl = $url;
	}


	/* return url minus any query string */
	private function GetUrl() {
		if (strpos($this->sUrl, "?") !== FALSE) {
			$tmp = explode("?",$this->sUrl);
			return $tmp[0];
		} else {
			return $this->sUrl;
		}
	}

	/* return query string minus any refs to this pager instance */
	private function GetQueryStr() {

		$qs = "";

		if (strpos($this->sUrl, "?") !== FALSE) {
			$tmp = explode("?",$this->sUrl);
			$opt = $this->parseQueryString($tmp[1]);
			foreach($opt as $k => $v) {
				if ($k != $this->GetPagerId()) {
					$qs .= "&".$k."=".$v;
				}
			}
			return $qs;

		}
	}

	
	public function getPageNav() {
		return $this->Render();
	}

	public function Render()
	{

		/*
		print "<div>";
		print "current_page: ".$this->GetPageNum()."<br/>";
		print "items_per_page: ".$this->GetResultsPerPage()."<br/>";
		print "total_results:" .$this->iResultCount."<br />";
		print "total_pager_items: ".$this->GetNumPages()."<br/>";
		print "start_idx: ".$this->GetStartIdx()."<br/>";
		print "end_idx: ".$this->GetEndIdx()."<br/>";
		print "</div>";
		*/

		if ($this->GetNumPages() > 1) {

			$out .=	"";
			$out .= "<div class=\"page-links clear\">";
			$out .= "<ul class='pager'>";

			for ($i=$this->GetStartIdx(); $i<=$this->GetEndIdx(); $i++) {
				if($i == $this->GetStartIdx() && $this->GetPageNum() > 1) {
					$out .= "<li><a href=\"#\" id=\"pageid_".($i-1)."\">«</a></li>";
				}
	
				if ($i==$this->GetPageNum()) {
					$out .= "<li class=\"active\"><a href=\"#\">".$i."</a></li>";
				} else {
					$qs = "?&".$this->GetPagerId()."=".$i; //.$this->GetQueryStr();
					$out .= "<li><a href=\"#\" id=\"pageid_".$i."\" title=\"View Page ".$i."\">".$i."</a></li>";
				}
	
				if ($i == $this->GetEndIdx() && $this->GetPageNum() < $this->GetNumPages()) {
				    $out .= "<li><a id=\"pageid_".($this->GetStartIdx() + $this->pageSize)."\" href=\"#\" title=\"Next\">»</a></li> ";
				}

			}

			$out .= "</ul>";
			$out .= "</div>";

			return $out;
		}
	}

	public function RenderHTML()
	{

		global $sUri;
	
		if ($this->GetNumPages() > 1) {
	
			$out .=	"";
			$out .= "<div class=\"page-links clear\">";
			$out .= "<ul class='pager'>";
	
			for ($i=$this->GetStartIdx(); $i<=$this->GetEndIdx(); $i++) {
			    if($i == $this->GetStartIdx() && $this->GetPageNum() > 1 && ($this->GetStartIdx() > $this->GetResultsPerPage())) {
				    $qs = "?&".$this->GetPagerId()."=".($this->GetStartIdx() - $this->GetResultsPerPage());
				    $out .= "<li><a href=\"".$sUri.$qs."\" id=\"pageid_".($this->GetStartIdx() + $this->GetResultsPerPage())."\">«</a></li>";
				}
	
				if ($i==$this->GetPageNum()) {
					$out .= "<li class=\"active\"><a href=\"#\">".$i."</a></li>";
				} else {
					$qs = "?&".$this->GetPagerId()."=".$i; //.$this->GetQueryStr();
					$out .= "<li><a href=\"".$sUri.$qs."\" id=\"pageid_".$i."\" title=\"View Page ".$i."\">".$i."</a></li>";
				}
					    
				if ($i == $this->GetEndIdx() && ($this->GetStartIdx() + $this->GetResultsPerPage()) < $this->GetNumPages()) {
				    $qs = "?&".$this->GetPagerId()."=".($this->GetStartIdx() + $this->GetResultsPerPage()); //.$this->GetQueryStr();
				    $out .= "<li><a id=\"pageid_".($this->GetStartIdx() + $this->GetResultsPerPage())."\" href=\"".$sUri.$qs."\" title=\"Next\">»</a></li> ";
				}
	
			}
	
			$out .= "</ul>";
			$out .= "</div>";
	
			return $out;
		}
	}


	private function parseQueryString($str) {
		$op = array();
		$pairs = explode("&", $str);
		foreach ($pairs as $pair) {
			if (strlen($pair) >= 1) {
				list($k, $v) = array_map("urldecode", explode("=", $pair));
				$op[$k] = $v;
			}
		}
		return $op;
	} 
}



?>
