<?php



class BugLog {

	// constructor
	function BugLog(&$db) {

		$this->db = $db;
		
		$this->oid[] = "";
		$this->reportedDate[] = "";
		$this->reportedBy[] = "";
		$this->status[] = "";
		$this->type[] = "";
		$this->platform[] = "";
		$this->summary[] = "";
		$this->fixedNotes[] = "";
		$this->priority[] = "";
		$this->sortBy = "reportdate"; //default sort order
		
		$this->bugCount = "";
		$this->findBugID = "";
	}

	// get reported bugs
	function getBugs() {

		$this->db->query("SELECT oid, * FROM problem_log ORDER BY ".$this->sortBy." DESC");
		$r = $this->db->getRows();
		/*
		print "<pre>";
		var_dump($r);
		print "</pre>";
		die();
		*/
		$this->bugCount = $this->db->getNumRows();
		for ($i =0; $i < $this->bugCount; $i++) {
			$this->oid[$i] = $r[$i]['oid'];
			$this->reportedDate[$i] =  $r[$i]['reportdate'];
			$this->reportedBy[$i] = $r[$i]['reportedby'];
			$this->priority[$i] = $r[$i]['priority'];
			$this->status[$i] =  $r[$i]['status'];
			$this->type[$i] =  $r[$i]['type'];
			$this->summary[$i] =  $r[$i]['summary'];
			$this->fixedNotes[$i] =  $r[$i]['fixednotes'];
			$this->fixedBy[$i] =  $r[$i]['fixedby'];
			$this->fixedDate[$i] =  $r[$i]['fixeddate'];
		}
	}


	// report a bug
	function reportBug($reportedBy,$type,$platform,$summary,$priority) {
		$status = "active";
		$fixednotes = "";
		$this->db->query("INSERT INTO problem_log (reportdate,reportedby,status,type,summary,fixednotes,priority) VALUES ('now'::timestamp,'$reportedBy','$status','$type','$summary','$fixednotes','$priority');");
	}


	// delete an item
	function delBug() {
		$this->db->query("DELETE FROM problem_log * WHERE oid = $oid;");
	}


	// update bug
	function updateBug($oid,$status,$priority,$fixednotes,$fixedBy) {
		$this->db->query("UPDATE problem_log SET status = '$status', priority = '$priority', fixednotes = '$fixednotes', fixedBy = '$fixedBy', fixeddate = 'now'::timestamp WHERE oid = $oid");
	}


	// display reported bugs
	function displayBugs() {
		$this->getBugs();
		if ($this->bugCount > 0){
			// loop to display each item
			for ($i =0; $i < $this->bugCount; $i++) {
				// perform highlighting
				($row_high =="") ?  $row_high = " class=\"highlight\"" : $row_high = "";
				echo "<tr".$row_high." id='row_".$this->oid[$i]."'>\n";

				echo "<td class=\"tableBody\" valign=\"top\">".$this->oid[$i]."</td>\n";
				echo "<td class=\"tableBody\" valign=\"top\">".substr($this->reportedDate[$i],0,16)."</td>\n";
				echo "<td class=\"tableBody\" valign=\"top\">".$this->reportedBy[$i]."</td>\n";
				echo "<td class=\"tableBody\" valign=\"top\">".$this->priority[$i]."</td>\n";
				echo "<td class=\"tableBody\" valign=\"top\">".$this->status[$i]."</td>\n";
				echo "<td class=\"tableBody\" valign=\"top\">".$this->type[$i]."</td>\n";
				echo "<td class=\"tableBody\" valign=\"top\">".$this->summary[$i]."</td>\n";
				echo "<td class=\"tableBody\" valign=\"top\">".$this->fixedNotes[$i]."</td>\n";
				echo "<td class=\"tableBody\" valign=\"top\">".$this->fixedBy[$i]."</td>\n";
				echo "<td class=\"tableBody\" valign=\"top\">".substr($this->fixedDate[$i],0,16)."</td>\n";

				echo "</tr>\n";
			}
		// no bugs display 
		} else {
			echo "<tr class=\"highlight\">\n";
			echo "<td class='text' colspan=8>&nbsp;There are currently 0 Reported Bugs</td>\n";
			echo "</tr>\n";
		}
	}


	// display bugOids
	function displayBugOids() {
		$this->db->query("SELECT oid FROM problem_log");
		$r = $this->db->getRows();
		for ($i =0; $i < $this->db->getNumRows(); $i++) {
			$this->oid[$i] = $r[$i]['oid'];
			?>
			<option name="bugID" value="<?echo $this->oid[$i] ?>"><?echo $this->oid[$i] ?>
			<?
		}
	}


	// find a bug by ID
	function findBug($bugID) {

		$this->db->query("SELECT oid,* FROM problem_log WHERE oid = $bugID");
		$r = $this->db->getRows();

		if($this->db->getNumRows == 1) { 
			$oid = $r[0]['oid'];
			$status = $r[0]['status'];
			$priority = $r[0]['priority'];
			$fixedNotes =  $r[0]['fixednotes'];
			$fixedBy =  $r[0]['fixedby'];
			$fixedDate =  $r[0]['fixeddate'];

			if ($status == "active") {
				$statusOption = "closed";
			} else {
				$statusOption = "active";
			}
			?>
			<form name="findBug" enctype="multipart/form-data" action="<?echo basename($PHP_SELF)?>" method="POST">
			<tr>
				<td class="text" valign="top"><? echo $oid ?></td>
				<input type="hidden" name="oid" value="<? echo $oid ?>">
				<td class="text" valign="top">
				<select name="status" class="text">
					<option value="<? echo $status ?>" selected><? echo $status ?>
					<option value="<? echo $statusOption ?>"><? echo $statusOption ?>
				</select>
				</td>
				<td class="text" valign="top"><input type="text" name="priority" size="4" value="<? echo $priority ?>" class="text"></td>
				<td class="text" valign="top"><textarea cols="32" rows="5" name="fixednotes" class="text"><? echo $fixedNotes ?></textarea></td>			
				<td class="text" valign="top"><input type="text" name="fixedBy" size="20" value="<? echo $fixedBy ?>" class="text"></td>
			</tr>
			<tr>
				<td colspan="6" align="right"><input type="submit" name="updateBug" value="update"></td>
			</tr>

			</form>
			<?
		} else { 
		echo "No matching bugs found...";
		}

	}

// end bug log class
}


?>
