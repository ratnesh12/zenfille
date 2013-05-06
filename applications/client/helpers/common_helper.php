<?php
	/**
	* Checks an array: exist and has more than 0 elements
	* 
	* @author	Sergey Koshkarev <koshkarev.ss@gmail.com>
	* @param	array
	* @return	bool
	*/
	function check_array($array = array())
	{
		if (is_array($array) && (count($array) > 0))
		{
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	* Checks is current http request AJAX or not
	* 
	* @author	Sergey Koshkarev <koshkarev.ss@gmail.com>
	* @return	bool
	*/
	function is_ajax() 
	{
    	return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest");
	}
	
	/**
	* Formats bytes to more appealing look 
	* 
	* @author	Sergey Koshkarev
	* @param int size in bytes
	* @return	string
	* 
	*/
	function format_bytes($size) 
	{
	    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
	    for ($i = 0; $size >= 1024 && $i < 4; $i++)
		{
			$size /= 1024;
		}
	    return round($size, 2).$units[$i];
	}
	
	/**
	* Removes folder recursively
	* 
	* @author	Sergey Koshkarev <koshkarev.ss@gmail.com>
	* @param	string	folder to be removed
	* @return	void
	*/
	function rrmdir($dir) 
	{
   		if (is_dir($dir)) 
		{
     		$objects = scandir($dir);
     		foreach ($objects as $object) 
			{
       			if ($object != "." && $object != "..") 
				{
         			if (filetype($dir."/".$object) == "dir")
					{
						rrmdir($dir."/".$object); 
					}
					else 
					{
						unlink($dir."/".$object);
					}
       			}
     		}
		    reset($objects);
		    rmdir($dir);
   		}
 	} 
	
	function create_password($pw_length = 8, $use_caps = TRUE, $use_numeric = TRUE, $use_specials = TRUE) 
	{
		$caps = array();     
		$numbers = array();
		$num_specials = 0;
		$reg_length = $pw_length;
		$pws = array();
		$chars = range(97, 122); // create a-z
		if ($use_caps) $caps = range(65, 90); // create A-Z
		if ($use_numeric) $numbers = range(48, 57); // create 0-9
		$all = array_merge($chars, $caps, $numbers);
		if ($use_specials) {
			$reg_length =  ceil($pw_length*0.75);
			$num_specials = $pw_length - $reg_length;
			if ($num_specials > 5) $num_specials = 5;
			$signs = range(33, 47);
			$rs_keys = array_rand($signs, $num_specials);	
			if (count($rs_keys) > 1)
			{				
				foreach ($rs_keys as $rs) {
					$pws[] = chr($signs[$rs]);
				}
			}
			else
			{
				$pws[] = chr($signs[$rs_keys]);
			}
		} 
		$rand_keys = array_rand($all, $reg_length);
		foreach ($rand_keys as $rand) {
			$pw[] = chr($all[$rand]);
		}	
		$compl = array_merge($pw, $pws);	
		shuffle($compl);
		return implode('', $compl);
	}
	
	/**
 * @params      : $a            array           the recursion array
 *              : $s            array           storage array
 *              : $l            integer         the depth level
 *
 */
if( ! function_exists( 'array_flat' ) )
{
    function array_flat( $a, $s = array( ), $l = 0 )
    {
        # check if this is an array
        if( !is_array( $a ) )                           return $s;
       
        # go through the array values
        foreach( $a as $k => $v )
        {
            # check if the contained values are arrays
            if( !is_array( $v ) )
            {
                # store the value
                $s[ ]       = $v;
               
                # move to the next node
                continue;
               
            }
           
            # increment depth level
            $l++;
           
            # replace the content of stored values
            $s              = array_flat( $v, $s, $l );
           
            # decrement depth level
            $l--;
           
        }
       
        # get only unique values
        if( $l == 0 ) $s = array_values( array_unique( $s ) );
       
        # return stored values
        return $s;
       
    } # end of function array_flat( ...
 } 	
	/**
	* Rounds up float/integer value. Used in estimate calculations
	* 
	* @author	Sergey Koshkarev <koshkarev.ss@gmail.com>
	* @param	float	initial value
	* @return	int 
	*/
	function round_up($value)
	{ 
        $num_decimals = strlen(substr(strrchr($value, "."), 1));

        if (!$num_decimals && $value%10 === 0) {
            $a = $value;
        } else {
            $a = ceil($value / 10) * 10;
		}

        return $a;
	}
	
	/**
	* Add business days to date
	*
	* @author	Sergey Koshkarev
	* @param	date	initial date	
	* @param	int	how many days to add
	* @param	array	holidays
	* @param	string	date format
	* @return	date	new date with adjustments
	*/
	function add_business_days($startdate, $buisnessdays = 0, $holidays = array(), $dateformat = 'Y-m-d')
    {
   		$enddate = strtotime($startdate);
    	$day = date('N',$enddate);
    	while($buisnessdays > 0)
		{ // compatible with 1 businessday if I'll need it
	        $enddate = strtotime(date('Y-m-d',$enddate).' +1 day');
	        $day = date('N',$enddate);
	        if($day < 6 && !in_array(date('Y-m-d',$enddate), $holidays))$buisnessdays--;
    	}
    	return date($dateformat, $enddate);
	}
	
	/**
	* Get the time of last modification for selected file
	*
	* @author	Sergey Koshkarev
	* @param	string	
	* @return	int	time of modification
	*/
	function get_file_modification_time($file_path = '')
	{
		if (empty($file_path))
		{
			return time();
		}
		
		if (file_exists($file_path))
		{
			return filemtime($file_path);	
		}
		return time();
	}
 
?>