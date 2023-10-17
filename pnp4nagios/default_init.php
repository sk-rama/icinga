<?php
#
# Common Initialization Values for GEOTEK Icinga2 Default Template
# Colors are matched to Icingaweb2 Color Scheme

// Standard Colors
$_COLOR_STD    		= '#0095BF';    // Icingaweb2 Color
$_COLOR_STD_DARK	= '#0085AA';		// Darker than Standard, used for Area border
$_COLOR_WARN 			= '#FFAA44';   	// Icingaweb2 Color (Warning)
$_COLOR_CRIT 			= '#FF5566';   	// Icingaweb2 Color (Error)
$_COLOR_MAX  			= '#000000';   	// Must be different from Single Graph / Line colors

// Single Graphs / Lines
$_COLOR_AREA   	 	= '#0095BF';    // Icingaweb2 Color for single graphs
$_COLOR_AREAS   	= ['#00CC00', '#FF000080', '#80800020', '#80008080'];   // Colors for multiple graphs
$_COLOR_LINE    	= '#0095BF';    // Icingaweb2 Color for single graphs
$_COLOR_LINES   	= ['#00CC00', '#FF000080', '#80800080', '#80008080'];   // Colors for multiple graphs
$_COLOR_LIGHT   	= '#C0C0C0';    // Light Color, e.g. for Zero or marking lines, visible but in background
                              
// Multiple Graphs - Need to choose different colors with shine through capability
$_COLOR_GRAD_BG1 	= '#F8F8F8';    // Lignt Background "From" Color (Light Gray at bottom)
$_COLOR_GRAD_BG2 	= '#E0E0E0';    // Lignt Background "To" Color (Medium Gray at top)
$_COLOR_LINE_BG3 	= '#C0C0C0';    // Optional Line at top of BG Area
  
$_COLOR_AREA_BG  	= '#00CC00';    // Background Area, gets written first (Green)
$_COLOR_AREA_FG  	= '#FF000080';  // Foreground Area that allows to shine through Background Area, gets written secondly (Red)

$hostname  				= $this->MACRO['DISP_HOSTNAME'];		// Name of Server being checked
$service 					= $this->MACRO['DISP_SERVICEDESC'];	// Icinga2 Service Check Name
$template					= $TEMPLATE[1];		// Name of the RRD Check Template used


//
// Shorten Names and cleanup illegal characters from name string so that pnp4nagios won't throw exceptions
//
if (!function_exists('CleanupName')) {		// avoid redeclaration errors with pnp4nagios pages
	function CleanupName ($str) {
		$str = str_replace('_Label:', ' ', $str);
		$str = substr(str_replace(':', '\:', $str), 0, 16);	// shorten to 16 chars (Windows Drive names)
		return $str;
	}
}


//
// Format number with SI Prefixes
// Todo: Add fractional prefixes such as m and u
if (!function_exists('FormatSI')) {				// avoid redeclaration errors with pnp4nagios pages
	function FormatSI($val, $precision = 2) {
	    $unit_list = array ('', 'k', 'M', 'G', 'T');

	    $index_max = count($unit_list) - 1;
	    $val = max($val, 0);

	    for ($index = 0; $val >= 1000 && $index < $index_max; $index++) {
	        $val /= 1000;
	    }

	    return trim(round($val, $precision) . ' ' . $unit_list[$index]);
	}
}


?>
