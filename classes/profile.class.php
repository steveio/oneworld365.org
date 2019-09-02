<?php
/*******************************************************************************/
/* profile.php                                                                 */
/*                                                                             */
/* script execution time & resource use profiling                              */
/*                                                                             */
/*******************************************************************************/


/*

// Useage -

$oProfile = new Profile();
$oProfile->profile__time__start(PROF__TIME_PROFILE__TOTAL);


$oProfile->profile__time__stop(PROF__TIME_PROFILE__TOTAL);
$oProfile->profile__memory(PROF__MEMORY_PROFILE__MEM);

echo "<span>Page took ".$oProfile->profile__time__msec(PROF__TIME_PROFILE__TOTAL)." msecs<br/></span>";
echo "<span>Memory usage = ".$oProfile->profile__memory__get_usage(PROF__MEMORY_PROFILE__MEM, PROF__MEMORY_OUTPUT__KBYTES, 0)." KBytes<br/></span>";
echo "<span>Memory peak usage = ".$oProfile->profile__memory__get_peak_usage(PROF__MEMORY_PROFILE__MEM, PROF__MEMORY_OUTPUT__KBYTES)." KBytes<br/></span>";


*/


define("PROF__TIME_PROFILE__TOTAL",	"prof__profile__total");
define("PROF__MEMORY_PROFILE__MEM",	"profile__profile__memory");
define("PROF__MEMORY_OUTPUT__BYTES",	"b");
define("PROF__MEMORY_OUTPUT__KBYTES",	"k");
define("PROF__MEMORY_OUTPUT__MBYTES",	"m");

class profile
	{
	var $time;
	var $memory;
	var $seconds_at_start;

	/***************************************************************************/
	/* Initialisation code                                                     */
	/***************************************************************************/
	function __Construct()
		{
		$this ->time   = array();
		$this ->memory = array();

		$t = explode(" ", microtime());
		$this ->seconds_at_start = $t[1];
		}


	/***************************************************************************/
	/* profile__time__start($id)                                               */
	/*                                                                         */
	/* $id      = timer identifier, used to sub-divied timer. This could be    */
	/*            any identifier, such as function name.                       */
	/*                                                                         */
	/* Records a timer entry point. Each time this function is called a new    */
	/* entry is made under the specified id. For each call to this function    */
	/* should be an equivelent call to profile__timer__stop(). You should not  */
	/* nest calls with the same id in the same instance, otherwise the timing  */
	/* information will be counted multiple times, and acturate reading can    */
	/* not be relied upon.                                                     */
	/*                                                                         */
	/* Return:                                                                 */
	/*   none                                                                  */
	/***************************************************************************/
	function profile__time__start($id = 'undefined')
		{
		if (!array_key_exists($id, $this ->time))
			{
			$this->time[$id] = array();
			}

		if (array_key_exists(count($this ->time[$id]) -1,  $this ->time[$id]))
			if (array_key_exists('start', $this ->time[$id][count($this ->time[$id]) -1]))
				$this ->profile__time__stop($id);					// Terminate previous $time[$id], if exists

		$this ->time[$id][count($this ->time[$id])]['start'] = $this ->get_time_in_microseconds();
//		print_r($this ->time); echo "<br/>";
		}


	/***************************************************************************/
	/* profile__time__stop($id)                                                */
	/*                                                                         */
	/* $id      = timer identifier, used to sub-divied timer. This could be    */
	/*            any identifier, such as function name.                       */
	/*                                                                         */
	/* Records a timer exit point. When this function is called it will record */
	/* the time under the specified id's last entry point.                     */
	/*                                                                         */
	/* Return:                                                                 */
	/*   none                                                                  */
	/***************************************************************************/
	function profile__time__stop($id = 'undefined')
		{
		if (array_key_exists($id, $this ->time))
			{
			if (!array_key_exists('stop', $this ->time[$id][count($this ->time[$id]) -1]))
				{
				$this->time[$id][count($this ->time[$id]) -1]['stop'] = $this ->get_time_in_microseconds();
				}
			}
//		print_r($this ->time); echo "<br/>";
		}


	/***************************************************************************/
	/* profile__time__diff($id, $object)                                       */
	/*                                                                         */
	/* $id      = timer identifier                                             */
	/* $object  = which object should be returned. A positive value returns    */
	/*            nth object (0 the first, 1 = seconds, ..). if $object is -1  */
	/*            the last object is returned. If null is passed (or not       */
	/*            passed to the fuction), then whole time is returned i.e. the */
	/*            last entry - first entry.                                    */
	/*                                                                         */
	/* Calculates the difference in time between the start and end points of   */
	/* a specified timing point.                                               */
	/*                                                                         */
	/* If the specified timing point does not exist 0 will be returned. If     */
	/* the end point does not exist but the start point does then the start    */
	/* point is subsituted for the end point, this will result on a diff of 0  */
	/* being returned in all cases excpet when $object is NULL and there is    */
	/* more than 1 timing point recorded.                                      */
	/*                                                                         */
	/* Return:                                                                 */
	/*   the time difference of a timing point, in seconds to 6 decimal places */
	/*   0 if the timing point invalid or not end point recoded.               */
	/***************************************************************************/
	function profile__time__diff($id = 'undefined', $object = NULL)
		{
		$diff = 0;									// default return value
		$start_pos = $end_pos = $object;

		if (array_key_exists($id, $this ->time))
			{
			/*******************************************************************/
			/* setup start and end points                                      */
			/*******************************************************************/
			if (($object == -1) || ($object === NULL))
				{
				$start_pos = $end_pos = count($this ->time[$id]) -1;
				}
			if ($object === NULL)
				{
				$start_pos = 0;
				}


			/*******************************************************************/
			/* Get the time values                                             */
			/*******************************************************************/
			$start_time = $this ->time[$id][$start_pos]['start'];
			if ($this ->time[$id][$end_pos]['stop'] != NULL)
				$end_time = $this ->time[$id][$end_pos]['stop'];
			else
				$end_time = $this ->time[$id][$end_pos]['start'];			// no stop entry, use start entry instead


			/*******************************************************************/
			/* calc differnce                                                  */
			/*******************************************************************/
			$diff = round($end_time - $start_time, 6);
			}


//		echo "Time Diff = ".$diff."<br/>";
		return($diff);
		}



	/***************************************************************************/
	/* profile__time__msec($id, $object)                                       */
	/*                                                                         */
	/* $id      = timer identifier                                             */
	/* $object  = which object should be returned. A positive value returns    */
	/*            nth object (0 the first, 1 = seconds, ..). if $object is -1  */
	/*            the last object is returned. If null is passed (or not       */
	/*            passed to the fuction), then whole time is returned i.e. the */
	/*            last entry - first entry.                                    */
	/*                                                                         */
	/* Calculates the difference in time between the start and end points of   */
	/* a specified timing point.                                               */
	/*                                                                         */
	/* Calls the profile__time_diff function to retrive the time difference    */
	/* in seconds and then converts it into milliseconds.                      */
	/* See profile__time__diff() for details.                                  */
	/*                                                                         */
	/* Return:                                                                 */
	/*   the time difference of a timing point, in msecs                       */
	/***************************************************************************/
	function profile__time__msec($id = 'undefined', $object = NULL)
		{
		return(round($this ->profile__time__diff($id, $object) * 1000, 0));
		}


	/***************************************************************************/
	/* profile__time__usec($id, $object)                                       */
	/*                                                                         */
	/* $id      = timer identifier                                             */
	/* $object  = which object should be returned. A positive value returns    */
	/*            nth object (0 the first, 1 = seconds, ..). if $object is -1  */
	/*            the last object is returned. If null is passed (or not       */
	/*            passed to the fuction), then whole time is returned i.e. the */
	/*            last entry - first entry.                                    */
	/*                                                                         */
	/* Calculates the difference in time between the start and end points of   */
	/* a specified timing point.                                               */
	/*                                                                         */
	/* Calls the profile__time_diff function to retrive the time difference    */
	/* in seconds and then converts it into microseconds.                      */
	/* See profile__time__diff() for details.                                  */
	/*                                                                         */
	/* Return:                                                                 */
	/*   the time difference of a timing point, in usecs                       */
	/***************************************************************************/
	function profile__time__usec($id = 'undefined', $object = NULL)
		{
		return(round($this ->profile__time__diff($id, $object) * 1000000, 0));
		}


	/***************************************************************************/
	/* profile__time__count($id, $object)                                      */
	/*                                                                         */
	/* $id      = timer identifier                                             */
	/*                                                                         */
	/* Returns the number of timer enteries in the given object.               */
	/*                                                                         */
	/* Return:                                                                 */
	/*   number of timer enteries                                              */
	/***************************************************************************/
	function profile__time__count($id = 'undefined')
		{
		return(	array_key_exists($id, $this ->time) ? count($this ->time[$id]) : 0 );
		}


	/***************************************************************************/
	/* profile__time_sum__msec($id)                                            */
	/*                                                                         */
	/* $id      = timer identifier                                             */
	/*                                                                         */
	/* Calculates the sum of all objects for the givien id.                    */
	/*                                                                         */
	/* Calls the profile__time_diff function to retrive the time difference    */
	/* in seconds and then converts it into milliseconds.                      */
	/* See profile__time__diff() for details.                                  */
	/*                                                                         */
	/* Return:                                                                 */
	/*   the time difference of a timing point, in msecs                       */
	/***************************************************************************/
	function profile__time_sum__msec($id = 'undefined')
		{
		$sum = 0;
		$all_objects = array_key_exists($id, $this ->time) ? count($this ->time[$id]) : 0;
		for($time_object = 0; $time_object < $all_objects; $time_object++)
			{
			$sum += $this ->profile__time__diff($id, $time_object);
			}
		return(round($sum * 1000, 0));
		}


	/***************************************************************************/
	/* profile__memory($id)                                                    */
	/*                                                                         */
	/* $id      = memory identifier, used to sub-divied profile. This could be */
	/*            any identifier, such as function name.                       */
	/*                                                                         */
	/* Records a memory snapshot. Each time this function is called a new      */
	/* entry is made under the specified id. For each call 2 enteries are made */
	/* "mem", "mem_peak", where mem records the current memory usage, and      */
	/* mem_peek records the maximum aount used upto this point. The value      */
	/* recorded is stored in bytes.                                            */
	/*                                                                         */
	/* If the PHP functions get_memory_usage() or get_memory_peak_usage() do   */
	/* not exist then a value of 0 is returned.                                */
	/*                                                                         */
	/* Return:                                                                 */
	/*   none                                                                  */
	/***************************************************************************/
	function profile__memory($id = 'undefined')
		{
		if (!array_key_exists($id, $this ->memory))
			{
			$this->memory[$id] = array();
			}
		$pos = count($this->memory[$id]);


		if (function_exists('memory_get_usage'))
			{
			$this->memory[$id][$pos]['mem'] = memory_get_usage();
			}
		else
			{
			$this->memory[$id][$pos]['mem'] = 0;
			}

		if (function_exists('memory_get_peak_usage'))
			{
			$this->memory[$id][$pos]['mem_peak'] = memory_get_peak_usage();
			}
		else
			{
			$this->memory[$id][$pos]['mem_peak'] = 0;
			}

//print_r($this ->memory); echo "<br/>";
		}



	/***************************************************************************/
	/* profile__memory__get_usage($id, $output, $object)                       */
	/*                                                                         */
	/* $id      = memory identifier, used to sub-divied profile. This could be */
	/*            any identifier, such as function name.                       */
	/* $output  = one of the following                                         */
	/*               PROF__MEMORY_OUTPUT__BYTES                               */
	/*               PROF__MEMORY_OUTPUT__KBYTES                              */
	/*               PROF__MEMORY_OUTPUT__MBYTES                              */
	/* $object  = which object should be returned. A positive value returns    */
	/*            nth object (0 the first, 1 = seconds, ..). if $object is -1  */
	/*            or NULL the last object is returned.                         */
	/*                                                                         */
	/* Records a memory snapshot. Each time this function is called a new      */
	/* entry is made under the specified id. For each call 2 enteries are made */
	/* "mem", "mem_peak", where mem records the current memory usage, and      */
	/* mem_peek records the maximum aount used upto this point. The value      */
	/* recorded is stored in bytes.                                            */
	/*                                                                         */
	/* If the PHP functions get_memory_usage() or get_memory_peak_usage() do   */
	/* not exist then a value of 0 is returned.                                */
	/*                                                                         */
	/* Return:                                                                 */
	/*   none                                                                  */
	/***************************************************************************/
	function profile__memory__get_usage($id = 'undefined', $output = PROF__MEMORY_OUTPUT__KBYTES, $object = NULL)
		{
		$mem = 0;
		if (array_key_exists($id, $this ->memory))
			{
			if (($object == -1) || ($object === NULL))
				{
				$object = count($this ->memory[$id])-1;
				}

		if (array_key_exists('mem', $this ->memory[$id][$object]))
			$mem = $this ->memory[$id][$object]['mem'];

			if ($output == PROF__MEMORY_OUTPUT__KBYTES)
				{
				$mem /= 1024;
				}
			if ($output == PROF__MEMORY_OUTPUT__MBYTES)
				{
				$mem /= (1024 * 1024);
				}
			}

		return(round($mem));
		}



	/***************************************************************************/
	/* profile__memory__get_peak_usage($id, $output, $object)                  */
	/*                                                                         */
	/* $id      = memory identifier, used to sub-divied profile. This could be */
	/*            any identifier, such as function name.                       */
	/* $output  = one of the following                                         */
	/*               PROF__MEMORY_OUTPUT__BYTES                                */
	/*               PROF__MEMORY_OUTPUT__KBYTES                               */
	/*               PROF__MEMORY_OUTPUT__MBYTES                               */
	/* $object  = which object should be returned. A positive value returns    */
	/*            nth object (0 the first, 1 = seconds, ..). if $object is -1  */
	/*            or NULL the last object is returned.                         */
	/*                                                                         */
	/* Records a memory snapshot. Each time this function is called a new      */
	/* entry is made under the specified id. For each call 2 enteries are made */
	/* "mem", "mem_peak", where mem records the current memory usage, and      */
	/* mem_peek records the maximum aount used upto this point. The value      */
	/* recorded is stored in bytes.                                            */
	/*                                                                         */
	/* If the PHP functions get_memory_usage() or get_memory_peak_usage() do   */
	/* not exist then a value of 0 is returned.                                */
	/*                                                                         */
	/* Return:                                                                 */
	/*   Amount of memory at that specific time                                */
	/***************************************************************************/
	function profile__memory__get_peak_usage($id = 'undefined', $output = PROF__MEMORY_OUTPUT__KBYTES, $object = NULL)
		{
		$mem = 0;
		if (array_key_exists($id, $this ->memory))
			{
			if (($object == -1) || ($object === NULL))
				{
				$object = count($this ->memory[$id])-1;
				}

		if (array_key_exists('mem_peak', $this ->memory[$id][$object]))
			$mem = $this ->memory[$id][$object]['mem_peak'];

			if ($output == PROF__MEMORY_OUTPUT__KBYTES)
				{
				$mem /= 1024;
				}
			if ($output == PROF__MEMORY_OUTPUT__MBYTES)
				{
				$mem /= (1024 * 1024);
				}
			}

		return(round($mem));
		}


	function get_time_in_microseconds()
		{
		$t = explode(" ", microtime());
		return(($t[1] - $this ->seconds_at_start) + $t[0]);
		}

	}
?>

