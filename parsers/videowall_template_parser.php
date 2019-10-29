<?php


//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


//function to get the nice aray of the video wall parameters and values, so that we do not cluter the main function too much
if(!function_exists('videowallsz_videowall_parameter_values')) {

	function videowallsz_videowall_parameter_values($toParse) {

		$parsed = array();

		//First we are grabbing the parameters that can and probably will include spaces within them.

		//When loaded from DB it will have double quote even if we saved it with asterisk...
		$toParse = str_replace('"', "'", $toParse);

		//VideoWall Title
		if( ($t = stripos($toParse, ' title=')) > -1 ) {
			//Lets get the title then
			//title=\'wall title\'
			$parsed['title'] = substr($toParse, $t+8, stripos($toParse, "'", $t+8) - ($t + 8));

			//get parameters and values prior to title parameter
			$tmp = substr($toParse, 0, $t) . ' ';
			//get values after the title parameter and its values ( position + (starting space + parameter + = ) + length of parameter value + quotes )
			$tmp .= substr($toParse, $t + 8 + strlen($parsed['title']) + 2);

			$toParse = $tmp;
		}

		//No videos message
		if( ($t = stripos($toParse, ' message=')) > -1 ) {

			//Lets get the message then
			$parsed['message'] = substr($toParse, $t+10, stripos($toParse, "'", $t+10) - ($t + 10) );

			//get parameters and values prior to message parameter
			$tmp = substr($toParse, 0, $t) . ' ';
			//get values after the message parameter and its values ( position + (starting space + parameter + = ) + length of parameter value + quotes )
			$tmp .= substr($toParse, $t + 10 + strlen($parsed['message']) + 2);

			$toParse = $tmp;
		}

		//no videos template_name
		if( ($t = stripos($toParse, ' template_name=')) > -1 ) {
			//Lets get the template_name then
			$parsed['template_name'] = substr($toParse, $t+16, stripos($toParse, "'", $t+16) - ($t + 16));

			//get parameters and values prior to template_name parameter
			$tmp = substr($toParse, 0, $t) . ' ';
			//get values after the template_name parameter and its values ( position + (starting space + parameter + = ) + length of parameter value + quotes )
			$tmp .= substr($toParse, $t + 16 + strlen($parsed['template_name']) + 2);

			$toParse = $tmp;
		}

		//We can now split the rest with explode()

		$tmp = explode(' ', $toParse);

		foreach($tmp as $key => $value) {
			$value = trim($value, " \t\n\r\0\x0B".chr(0xC2).chr(0xA0));
			if( $value !== '' && $value !== ']' && $value !== '""'&& $value !== '"'
				&& $value !== 'wall') {
					//explode on = and trim ' and "
					$t = explode('=', $value);
					if(isset($t[1])) {
						$parsed[$t[0]] = trim($t[1], "'");
					}
					else {
						$parsed[$t[0]] = true;
					}
			}
		}

		return $parsed;
	}
}

?>