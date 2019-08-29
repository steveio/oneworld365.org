<?php




class BalancedDistributor {

	private $iFetchSize;
	
	private $iStartIdx;
	private $idx;
	private $xIdx;
	private $yIdx;
	private $total_y; // number of y axis elements
	private $total_x; // number of x axis elements
	private $total_elements;

	private $aDataSet;
	private $aElements; // elements fetch'd from dataset

	public function __Construct($aDataSet) {

		$this->aElements = array();
	
		if (!is_array($aDataSet)) return false;
	
		$this->aDataSet = $aDataSet;
		
		$this->total_y = count(array_keys($this->aDataSet));
		$this->total_x = $this->GetTotalX() -1;
		$this->total_elements = 0;
		
		$this->SetTotalElements();
		
		//print "total_y: ".$this->total_y."<br />";
		//print "total_x: ".$this->total_x."<br />"; 
		//print "total_elements: ".$this->total_elements."<br />"; 
		
		$this->i = 0;
		$this->x = 0;
		$this->y = 0;

		$this->iStartIdx = 0;
		//print "end init... <br />";
				
	}
	
	public function GetTotalElements() {
		return $this->total_elements;
	}
	
	private function SetTotalElements() {
		foreach($this->aDataSet as $row) {
			foreach($row as $element) {
				$this->total_elements++;
			}
		}
	}
	
	public function GetTotalX() {
		$max_count = 0; 
		foreach($this->aDataSet as $row) {
			$row_count = count($row);
			if ($row_count > $max_count) {
				$max_count = $row_count;
			}
		}
		return $max_count;
	}
	
	public function SetFetchSize($i) {
		$this->iFetchSize = $i;
	}
	
	public function GetFetchSize() {
		return $this->iFetchSize;
	}
	
	public function SetStartIdx($idx) {
		$this->iStartIdx = $idx;
		$this->Seek($this->iStartIdx);
	}

	public function SetIdx($i) {
		$this->i = $i;
	}


	private function Seek($to_idx) {
			
		//print "Seek: to_idx: ".$to_idx."<br />";
		
		if ($this->i == $to_idx) return;

		do {
			//print "Current y : ".$this->y . "<br />";
			//print "Current x : ".$this->x . "<br />";
			//print "Current i : ".$this->i . "<br />";
							
			if (!$this->Iterate()) break;

		} while ($this->i < $to_idx);		
		
	}
	
	
	public function Iterate($fetch = FALSE) {

		//print "Iterate: y: ".$this->y.", x: ".$this->x."<br />";
		
		// increment Y index
		if ($this->y < $this->total_y) {
			
			if ($this->ElementExists($this->y,$this->x)) {
				//print "Element: ".$this->GetElement($this->y,$this->x)."<br />";
				if ($fetch) {
					$this->aElements[] = $this->GetElement($this->y,$this->x);
				}
				$this->i++;
			}
			
			$this->y++;
			
		} else {
			$this->x++;
			if ($this->x > $this->total_x) return FALSE;
			$this->y = 0;
		}
		
		return TRUE;
		
	}

	public function Fetch($total) {

		//print "<b>Fetch : ".$total."<br /></b>";
		
		$to_idx = $this->i + $total;
		
		//print "from: ".$this->i."<br />";
		//print "to: ".$to_idx."<br />";
		
		do {
			
			//print "Current y : ".$this->y . "<br />";
			//print "Current x : ".$this->x . "<br />";
			//print "Current i : ".$this->i . "<br />";
			
			if (!$this->Iterate($fetch = TRUE)) break;
			
		} while ($this->i < $to_idx);		
		
		return $this->aElements;
		
		
	}
	
	private function ElementExists($y, $x) {
		return (isset($this->aDataSet[$y][$x])) ? TRUE : FALSE;
	}
	
	private function GetElement($y, $x) {
		if (isset($this->aDataSet[$y][$x])) {
			return $this->aDataSet[$y][$x];
		}  
	}
	
}


?>
