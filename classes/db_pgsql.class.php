<?php

/* 	
	db_pgsql.class.php
	PostgreSQL Database Wrapper
			  
*/

class db {

	public function __construct($dsn = null, $debug = false)
	{
		$this->db($dsn,$debug);
	}

	// constructor
	function db($dsn = null,$debug = false) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		

		$this->dbname 	= $dsn['dbname'];
		$this->port     = $dsn['dbport'];
		$this->user     = $dsn['dbuser'];
		$this->pass		= $dsn['dbpass'];

		// -------------------------------------------

		$this->conn_str = "port=$this->port dbname=$this->dbname user=$this->user password=$this->pass";
		
		//if (DEBUG) Logger::Msg($this->conn_str);
		
		$this->db = null;
		// automatically call connect method
		$this->connect();
		$this->setDateStyle();
	} // end constructor


	// connect to database
	function connect() {
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
		if(0 == $this->db) {
			$this->db=pg_connect($this->conn_str);
		}
		if (!$this->db) {
			$this->halt(); // call error handler
		}
	}

	function close() {
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
		pg_close($this->db);
	}

	// set postgres date style
	function setDateStyle(){
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
		$res = pg_Exec($this->db, "set DATESTYLE to 'European'");
	}

	// error handler
	function halt() {
	
		global $_CONFIG;

		$this->Notify("DB Connect Fail: ".$this->conn_str,$throttle = TRUE);
		
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header("Location: ".$_CONFIG['url']."/back_soon.php");
	
	}

	// get first row
	function getFirstRow($query) {
	
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
		if (DEBUG) Logger::Msg("<span style='font-size: 11px;'>".$query."</span>");
		
		if(strlen($query = trim($query))) {
			$result = pg_exec($this->db, $query);
			$count = pg_numrows($result);
			if($count >= 1) {
				return pg_fetch_array($result);
			} else {
				// email failed queries for trace analysis
		        //$this->errorMail($query);
				return false; // query failed
			}
		} else {
			print("<b>getFirstRow() Warning:</b><br />No query supplied.<br />");
			return false;
		}
	}
	
	// get first cell
	function getFirstCell($query) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
		if(DEBUG) Logger::Msg("<span style='font-size: 11px;'>".$query."</span>");


		if(strlen($query = trim($query))) {
			if($result = pg_exec($this->db, $query)) {
				$row = pg_fetch_array($result);
				return $row[0];
			} else {
				// email failed queries for trace analysis
	    	    //$this->errorMail($query);
				return false; // query failed
			}
		} else {
			print("<b>getFirstCell() Warning:</b><br />No query supplied.<br />");
			return false;
		}
	}


	// execute a query
	function query($query) {

		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
	
		if(DEBUG) Logger::Msg("<span style='font-size: 11px;'>".$query."</span>");

		if(strlen($query = trim($query))) {
			if($this->last_result = pg_exec($this->db, $query)) {
				return true;
			} else {
				//print $query;
				$this->last_result = false;
				$this->Notify($query, $throttle = FALSE);
				return false;
			}
		} else {
			print("<b>Query() Warning:</b><br />No query supplied.<br />");
			return false;
		}
	}


	// Get a row (as an array) from the last query:
	function getRow($fetchmode = PGSQL_BOTH,$rowIdx = NULL) {
		if($this->last_result) {
			return pg_fetch_array($this->last_result,$rowIdx,$fetchmode);
		} else {
			return false; // last_result is not a valid result set
		}
	}

    function getRows($fetchmode = PGSQL_ASSOC) {
		for ($i = 0; $i < $this->getNumRows(); $i++) {
			$arr[$i] = pg_fetch_array($this->last_result,NULL,$fetchmode);
		}
		return $arr;
	}

	function getRowsNum($fetchmode = PGSQL_NUM) {
		$arr = array();
		for ($i = 0; $i < $this->getNumRows(); $i++) {
			$tmp = pg_fetch_array($this->last_result,NULL,$fetchmode);
			$arr[]= (is_numeric($tmp[0])) ? (int) $tmp[0] : $tmp[0]; 
		}
		return $arr;
	}
	


	// Get number of rows from last query:
	function getNumRows() {
		if($this->last_result) {
			return pg_numrows($this->last_result);
		} else {
			return 0; // last_result is not a valid result set
		}
	}

	// Get number of rows affected by last query:
	function getAffectedRows() {
		if($this->last_result) {
			return pg_affected_rows($this->last_result);
		} else {
			return 0; // last_result is not a valid result set
		}
	}

	// Get oid of last-inserted row:
	function getLastOid() {
		if($this->last_result) {
			return pg_last_oid($this->last_result);
		} else {
			return false;
		}
	}

	// Get row (as an object) from the last query
	function getObject() {
		if($this->last_result) {
			return pg_fetch_object($this->last_result);
		} else {
			return false; // last_result is not a valid result set
		}
	}

    function getObjects() {
		for ($i = 0; $i < $this->getNumRows(); $i++) {
			$arr[$i] = pg_fetch_object($this->last_result,$i);
		}
		return $arr;
	}

	function getAll() {
		return pg_fetch_all($this->last_result);
	}

	function query_assoc_paged ($sql, $limit=0, $offset=0) {
		
		if (DEBUG) Logger::Msg(get_class($this)."::".__FUNCTION__."()");
		
		$this->num_rows = false;
	
		if (!$this->query($sql)) return false;
		
		$result = $this->last_result;
				
		if (!$result) return (false);
	
		// save the number of rows we are working with
		$this->num_rows = @pg_num_rows($result);
		
		// moves the internal row pointer of the result to point to our
		// desired offset. The next call to pg_fetch_assoc() would return
		// that row.
		if (! empty($offset)) {
			if (! @pg_result_seek($result, $offset)) {
				return (array());
			}
		}
	
		// gather the results together in an array of arrays...
		$data = array();
		
		while (($row = pg_fetch_assoc($result)) !== false) {
			
			$data[] = $row;			
			
			// After reading N rows from this result set, free our memory
			// and return the rows we fetched...
			if (! empty($limit) && count($data) >= $limit) {
				pg_free_result($result);
				return ($data);
			} 
		}
		pg_free_result($result);
		return($data);
	}

	function Notify($query, $throttle = TRUE) {
		

		if ($throttle) {
			/*
			 * Only send a notification email every 15 mins, prevents a flood of db connect fail messages
			 */
			$checkfile = LOG_PATH."/db_connect_error.txt";
	
			if  (file_exists($checkfile) && (filemtime($checkfile) > (time() - (60 * 15)))) {
				return FALSE;
			}
			
			// update checkfile time, send error notification
		    $handle = fopen($checkfile, "w+") or die("Failed to open DB checkfile");
			fwrite($handle, "error mail last sent: ".time());
			fclose($handle);
		}		

		$message .= "\nDate: " . date("d/m/y H:i:s") . "\n";
		$message .= "Vhost: ".$_SERVER['HTTP_HOST']." \n";
		$message .= "Page: " . $_SERVER['PHP_SELF'] . "\n";
		$message .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
		$message .= $query;
		$message .= "\n";
		$message .= "Error: ".pg_last_error($this->db)."\n";
		$message .= "\n";

		// Add GET, POST & COOKIE variables:
		ob_start();
		print "_POST...\n";
		var_dump($_POST);
		print "_GET...\n";
		var_dump($_GET);
		print "_COOKIE...\n";
		var_dump($_COOKIE);
		print "_SERVER\n";
		var_dump($_SERVER);
	

		$user_details = ob_get_contents();
		ob_end_clean();
		$message .= $user_details;

		/*
		// save to a log file
		$path = "/var/tmp/db_query.log";
		$f = fopen($path, 'a');
		fwrite($f, $message);
		fclose($f);
		*/

		// mail the error report
		//global $_CONFIG;
		//$to = "steveedwards01@yahoo.co.uk";
		//$subject =  $_CONFIG['site_title'] . " :: ERROR : db->query() failure log";
		//$headers = 'From: '.$_CONFIG['website_email'] . "\r\n";
		//$headers .= 'Reply-To: '.$_CONFIG['website_email'] . "\r\n";
		//mail($to,$subject,$message,$headers);

	}
	
} // end db class

?>
