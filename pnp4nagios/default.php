<?php
/*
GEOTEK Default Template for Icinga2 / pnp4nagios

Copyright (c) 2017 Martin Kasztantowicz, GEOTEK Datentechnik GmbH https://geotek.de

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

History:
V.1.0 2017-04-02 
 * Initial Version
V.1.1 2017-04-08 
 * Uppers and Lowers definition changed. Entries refer now to normalized values instead of raw values
 * Changed the sample array values accordingly
 * Show zero line with bidirectional but asymmetric graphs also
V.1.1.1 2017-04-10
 * Avoid redeclaration error when used with pnp4nagios pages
V.1.2 dev
 * Allow several measurements to be combined into single graphs
 * Allow building custom graphs by freely defining line, area or gradient graphs
 * Allow overriding color for specific graphs

Motivation:
This alternative pnp default template  was made because because I was fed up from insane measurement units such as 10mGB or 10Mms, the unappealing, oldfashioned and inconsistent graph colors and the amount of time it took to apply and manage changes across numerous templates in order to keep them consistent. While it seems impossible to solve all rrdgraphe issues completely, it is believed to be an improvement and should work sufficiently with most Nagios Check Commands out of the box. 

Features:
* Modern looking graphs that matches the Icingaweb2 color scheme
* Global graph attributes can be very easily changed
* Avoids display of weird Units such as 2222.5mGB or 0.10kms (well, at least mostly)
* Makes all graps show consistent measurement units so that througput is always shown in GB/s for example,
  no matter if scripts exports RRD data in KB/s, B/s or any other UOM. 
* Allow graphs to be shown in such a way, that it is immediately apparent from the overwiew window,
  if something is wrong or not (hostalive4 in the below examples)
* Correct or add UOMs (Units of measurement, such as MB) for checks with broken or missing  UOM in performance data
* Change the name of unclear graph data to something more comprehensible, possibly in your native language
* Change behaviour of specific graphs without the need to create and manage multiple templates. 
  All changes for specific graphs are easily managed in one place (this file)
* Modifications may be done based either on the Icinga2 Service Name or on the Check Command Name. In contrast, the pnpnagios
  template system allows to use only the Check Command Name
* Easily reorder graphs, so that the most important graph is shown first and displayed in the Icingaweb2 overview page
* Easily suppress graphs that need not be shown
* Allows to add comments to a graph
* Combine multiple data points into graphs, freely override graph style (line / area / gradient) and color

Installation:
Simply copy the two files default.php and default_init.php into the Template directory of pnp4nagios, usually located here:
/usr/share/pnp4nagios/templates
Optionally you may want to remove any other templates in this directory that you don't want to become active

Configuration:
Global changes that should effect everything, such as color changes, should be put into default_init.php
This default template should fit most of your checks, except some complex checks that involve combination of
data into graphs, where you will continue to need special templates. You may either use existing templates or build your own,
possibly by copying the GEOTEK default template, renaming it and modifying the script accordingly. In any case it is
a good idea to include the default_init-file so that the color scheme is consisten for all graphs
See the comments and examples below for instructions
*/

include ('default_init.php');

/*********************************************************************************
Put your modifications here...
You need to add a new case section for your service only if you are unhappy with the default graphs
Please note that we use the Icinga Service Name here, not the Check Template Name!
*/
switch ($service) {
	  case 'example-service':
      // Override the performance data value names 
      // Fill an array with constants, where the first array position refers to the first Perfdata,
	  	// the second array position to the second perfdata and so on. Leave blank to use the default value.
      // In this example the name of the first perfdata is kept unchanged, the second is perfdata to 'CPU Idle'
      $valnames = ['', 'CPU Idle'];

      // Select the graph type for each performance data (default: area)
      // Values: line | area | grad 
      $graphtypes = ['area', 'line'];

      // Override the Performance Data Colors 
      // Fill an array with constants, where the first array position refers to the first Perfdata,
	  	// the second array position to the second perfdata and so on. Leave blank to use the default value.
      $colors = ['', 'CPU Idle'];
      //
      // Reorder and omit some graphs. RRD Graphs are shown in the given order
      // Leave out an RRD number to omit it completely. Graph numbering in the array starts at 0
      // Be careful when reordering or selecting only specifig graphs. RRD Reordering 
      // is evaluated first, all subsequent override arrays then refer to the reordered graph numbers!
      // In this example, RRD graphs 2 and 0 are shown in this order, graph 1 is suppressed.
      $graphs = [2, 0];

      // Alternatively: if you want to combine several measurements into a single graph,
      // specify these measurements as an array. Both graphs must have identical Y-Values and UOMs.
      // In the following example the first graph combines RRD 2 and 3, the second graph shows RRD 0 only:
      $graphs = [[2, 3], 0];

      // Override the RRD Units. This is useful if UOM (Unit of Measurement) is missing from the RRD performance
      // data or even wrong. 
      $units = ['MB', '%'];

      // If you are measuring speed in 'MB/s' but the check command outputs 'MB' as UOM, 
      // this option allows to add the string '/s' (or anything else) to the legend for clarity. 
      // Internally we still use the 'UOM' for unit conversion.
      $unit_adds = ['', '/s'];

      // Override the number of decimal places in the measurement values and on the Y axis
      // In the first graph values are shown like 123.456, in the second like 123.
      $decimals = [3, 0];

      // Override the upper Y axis size. Without this parameter, the Y axis is normalized to the warning, critical,
      // maximum actual values reported by your check command, whichever is greater.
      // You may want to specify a lower value here in order to see smaller values in your graph.
      // In this case tha warn / crit hrules may be off scale and are not shown for small data values
      // The Y scale will grow automatically for larger measurement values but will never shrink below the given values.
      // This is good for response time graphs where the graphs should give an impression about the absolute values
      // and should be roughly comparable between devices. Without these parameters very small values would always 
      // blow up the graphs to full scale. 
      // Note: the numbers entered refer to the normalized values, that is, if the check plugin returns kB 
      // a value of 200 sets the upper limit to 200 B
      $uppers = [200, 400];

      // The lower Y axis values may also be overridden. You may enter negative values for bidirectional graphs.
      $lowers = ['', 20];

      // Add some notes below the graph
      $notes = ['', ''];
      //
      break;
	  case 'cpu-utilization-snmp':
	  	$valnames = ['CPU Utilization'];
	  	break;
	  case 'cpu-utilization-win':
	  	$valnames = ['CPU Utilization'];
	  	break;
	  case 'diskio-wmi':
    case 'diskio-c-wmi':
    case 'diskio-d-wmi':
    case 'diskio-e-wmi':
      $valnames = ['Disk Idle %', 'Disk Busy %', '', 'Read busy % ', 'Write busy %', 'Read ',
        'Read Ops/s ', 'Write', 'Write Ops/s', '', '', 'Read Queue ', 'Write Queue'];
      $graphs = [[0, 1], [3, 4], [6, 8], [5, 7], [11, 12]];
	  	$graphtypes = ['grad'];
      $colors = [$_COLOR_LINE_BG3, $_COLOR_STD];
      $units = ['%', '%', 'Ops/s', 'Bytes/s', 'Entries'];
	  	break;
	  case 'esxi-io-read':
	  	$unit_adds = ['/s'];
	  	break;
	  case 'esxi-io-write':
	  	$unit_adds = ['/s'];
	  	break;
	  case 'esxi-net-receive':
	  	$uppers = [1E5];
	  	$units = ['B'];
	  	$unit_adds = ['/s'];
	  	break;
	  case 'esxi-net-send':
	  	$uppers = [1E5];
	  	$units = ['KB'];
	  	$unit_adds = ['/s'];
	  	break;
	  case 'esxi-net-usage':
	  	$uppers = [1E5];
	  	$units = ['KB'];
	  	$unit_adds = ['/s'];
	  	break;
	  case 'memory-win':
	  	$valnames = ['Used'];
	  	break;
	  case 'pagefile-win-wmi':
	  	$valnames = ['Total  ', 'Used  ', 'Used  ', 'Peak Used', 'Peak Used'];
	  	$graphtypes = ['grad', 'area', 'area', 'line', 'line'];
      $colors = [$_COLOR_LINE_BG3, $_COLOR_STD, $_COLOR_STD, $_COLOR_WARN, $_COLOR_WARN];
      $graphs = [[0, 3, 1], [2, 4]];
      $units = ['%', '%', 'Ops/s', 'Bytes/s'];
	  	break;
	  case 'swap-win':
	  	$valnames = ['Used'];
	  	break;
	  case 'time-snmp';
      $units = ['s'];
      $uppers = [10];
      $lowers = [-10];
	  	break;
	  case 'uptime-snmp':
	  	$valnames = ['Uptime'];
	  	$uppers = [1E6];
	   	$decimals = [0];
	  	break;
	  case 'iftraffic64-snmp':
      $graphs = [[0,1], [2, 3], [4, 5]];
	  	break;
}



// If it is more convenient to make an exception for a template name instead of a service name, enter it here
// The variables and arrays are the same as above.
switch ($template) {
	  case 'disk-windows':
	  	$s = '';
	  	$notes = [$s, $s, $s, $s];
	  	break;
	  case 'hostalive4':
	    $valnames = ['Round Trip', 'Packet Loss'];
	   	$decimals = [0, 0];
      $uppers = [.2, ''];
      break;
	  case 'http':
	    $valnames = ['Response Time', 'Response Size'];
      $uppers = [.2, '10000'];
   	  break;
	  case 'check_interface_table_port':
      $graphs = [1, 2];
      $uppers = [1000000];
	  	break;
	  case 'iftraffic':
      $graphs = [[0,1], [2, 3]];
    	$units = ['%', 'Bytes/s'];
	  	break;
	  case 'network-windows':
	  	$unit_adds = ['/s', '/s','/s','/s', '/s','/s'];
	  	$uppers = [1E6, 1E6, 1E6, 1E6, 1E6, 1E6];
	  	break;
	  case 'snmp-interface':
	  	$units = ['bit/s', '', ''];
      $notes = [' ', ' '];  // gets rid of line break display error
      $decimals = [1, 1];
      $graphs = [[0, 1], [2, 4], [3, 5]];
      $uppers = [1000000];
	  	$valnames = ['Traffic In', 'Traffic Out', 'Errors In', 'Discards In', 'Errors Out', 'Discards Out'];
	  	break;
	  case 'snmp-memory':
	  	$units = ['KB', 'KB'];
	  	$valnames = ['RAM Used', 'Swap Used'];
	  	break;
	  case 'users-windows':
      $decimals = [0];
	  	$uppers = [10];
	  	break;
}


// ********************************************************************************
// Leave anything below this line untouched unless you know what you do...

for ($i = 0; $i < count($DS); $i++) {

	// Check if graph ordering is requested
	if (!isset($graphs)) $j = $i;
	elseif (isset($graphs[$i])) $j = $graphs[$i];
	else continue;

	// Make $j an array of graphs even if it contains only one graph	
	if (!is_array($j)) $j = [$j];

  // Combine measurements into single graph...
  $sg = 0;
  foreach($j as $k) {
    $arr = $this->DS[$k];
    $arr['NAME'] = CleanupName($arr['NAME']);
    $arr['LABEL'] = CleanupName($arr['LABEL']);	

    $minimum  = '';
    $maximum  = '';
    $warning  = '';
    $warn_max = '';
    $warn_min = '';
    $critical = '';
    $crit_min = '';
    $crit_max = '';
    $upper    = '';
  	$unit     = $arr['UNIT'];		// original UOM in performance data, e.g. 'KB'
		if (empty($unit)) $unit = ' ';	// Make with of graphs with and without Y-axis units equal
  	$unit_n   = $unit;					// normalized UOM that we take as base, e.g. 'B'
  	$unit_mult = 1;							// Multiplication factor $unit / $unit_n
    $precision = 2;
    $valname  = substr($arr['LABEL'], 0, 16);
    
    $note     = '';
    if (isset($notes[$i])) {
    	$note = $notes[$i];
    }
    
    if (isset($decimals[$i])) {
    	if ($decimals[$i] !== '') $precision = $decimals[$i];
    }
    $fmt = '%6.'.$precision.'lf';
    
    if (isset($units[$i])) {
    	if ($units[$i] !== '') $unit_n = $unit = $units[$i];
    }

    if (isset($valnames[$k])) {
    	if ($valnames[$k] !== '') $valname = $valnames[$k];
    }

    if ( $arr['MIN'] != '' && is_numeric($arr['MIN']) ) {
    	$minimum = $arr['MIN'];
    }
    if ( $arr['MAX'] != '' && is_numeric($arr['MAX']) ) {
    	$maximum = $arr['MAX'];
    }	
    if ($arr['WARN'] != '' && is_numeric($arr['WARN']) ){
    	$warning = $arr['WARN'];
    }
    if ($arr['WARN_MAX'] != '' && is_numeric($arr['WARN_MAX']) ) {
    	$warn_max = $arr['WARN_MAX'];
    }
    if ( $arr['WARN_MIN'] != '' && is_numeric($arr['WARN_MIN']) ) {
    	$warn_min = $arr['WARN_MIN'];
    }
  	  if ( $arr['CRIT'] != '' && is_numeric($arr['CRIT']) ) {
    	$critical = $arr['CRIT'];
    }
    if ( $arr['CRIT_MAX'] != '' && is_numeric($arr['CRIT_MAX']) ) {
    	$crit_max = $arr['CRIT_MAX'];
    }
    if ( $arr['CRIT_MIN'] != '' && is_numeric($arr['CRIT_MIN']) ) {
    	$crit_min = $arr['CRIT_MIN'];
    }



    // Set Raw maximum to the reported limits
    $upper = max($maximum, $warning, $warn_max, $crit_max); 
    $lower = min($minimum, $warning, $warn_min, $crit_min);

  	if ($lower == '') $lower = 0;
    if ($upper == '') $upper = 0;

    // Unit Normalization. We don't use pnp::adjust_unit as it does not work very well	
  	switch ($unit) {
  	  case '%%':
  	   	$unit_n = "%";
  	   	$unit_mult = 1;
  	   	if ($upper == '' or $upper == 0) $upper = '100';
        break;
    	case 'KB':			// KB is actually wrong, should be kB
    	 	$unit_n = 'B';
    	  $unit_mult = 1000;
    	 	break;
    	case 'kB':
    	 	$unit_n = 'B';
    	  $unit_mult = 1000;
    	 	break;
    	case 'KB/s':
    	 	$unit_n = 'B/s';
    	  $unit_mult = 1000;
    	 	break;
    	case 'MB':
    	 	$unit_n = 'B';
    	  $unit_mult = 1E6;
    	 	break;
    	case 'MB/s':
    	 	$unit_n = 'B/s';
    	  $unit_mult = 1E6;
    	 	break;
    	case 'Kb':
    	 	$unit_n = 'b';
    	  $unit_mult = 1000;
    	 	break;
    	case 'Kb/s':
    	 	$unit_n = 'b/s';
    	  $unit_mult = 1000;
    	 	break;
    	case 'Mb':
    	 	$unit_n = 'b';
    	  $unit_mult = 1E6;
    	 	break;
    	case 'Mb/s':
    	 	$unit_n = 'b/s';
    	  $unit_mult = 1E6;
    	 	break;
    	case 'ms':
    	 	$unit_n = 's';
    	  $unit_mult = 1E-3;
    	 	break;
    	case 'us':
    	 	$unit_n = 's';
    	  $unit_mult = 1E-6;
    	 	break;
    }


    $upper_n = $upper * $unit_mult;
    $lower_n = $lower * $unit_mult;

    if (isset($unit_adds[$i])) {
    	if ($unit_adds[$i] !== '') $unit_n .= $unit_adds[$i];	
    }

    // Set upper override value, if given
    if (isset($uppers[$i])) {
    	if ($uppers[$i] !== '') $upper_n = $uppers[$i];	
    }

    // Set lower override value, if given
    if (isset($lowers[$i])) {
    	if ($lowers[$i] !== '') $lower_n = $lowers[$i];
    }

    // avoid pnp errors
    if ($upper_n == '') $upper_n = 0;
    if ($lower_n == '') $lower_n = 0;

    if ($sg == 0) {
  	  $opt[$i+1]  = '-F -v "'.$unit_n.'" -l '.$lower_n.' -u '.$upper_n.' -t "'.$hostname.' / '. $service.'"';
      $ds_name[$i+1] = $arr['NAME'];
  	  $def[$i+1]  = rrd::def("var$sg", $arr['RRDFILE'], $arr['DS'], "AVERAGE");
    }
    else {
      $ds_name[$i+1] .= ', '.$arr['NAME'];
  	  $def[$i+1] .= rrd::def("var$sg", $arr['RRDFILE'], $arr['DS'], "AVERAGE");    
    }
   
  	$def[$i+1] .= rrd::cdef("var_n$sg", "var$sg,$unit_mult,*");
  	if ($upper_n >= 0 and $lower_n < 0) {
  		// show zero line for bidirectional graphs
  	 	$def[$i+1] .= rrd::line1(0, $_COLOR_LIGHT);
  	}

    $graphtype = 'area';
    
    // If single graph, take single graph color
    // If combined graph, take colors from combined graph array
    $color = (count($j) >1) ? $_COLOR_AREAS[$sg] : $_COLOR_AREA;

    // Explicit color assignment overrides anything else
    if (isset($colors[$k])) {
      if ($colors[$k] != '') {
        $color = $colors[$k];
      }
    }    
    if (isset($graphtypes[$k])) {
      if ($graphtypes[$k] != '') {
        $graphtype = $graphtypes[$k];
      }
    }
    switch ($graphtype) {
      case 'area': 
        $def[$i+1] .= rrd::area("var_n$sg", $color, "$valname");
        break;
      case 'grad': 
        $def[$i+1] .= rrd::gradient("var_n$sg", $_COLOR_GRAD_BG1, $_COLOR_GRAD_BG2, "$valname");
        $def[$i+1] .= rrd::line1("var_n$sg", $_COLOR_LINE_BG3);
        break;
      case 'line': 
        $def[$i+1] .= rrd::line1("var_n$sg", $color, "$valname");
        break;
    }
    
    // $def[$i+1] .= rrd::area("var_n$sg", $color, "$valname");

    // With no SI prefix gprint adds an extra space after the numeric value. This kludge tries to compensate for this
    // Doesn't work sometimes when zooming into graphs  :-(
    $sp = ' ';
    $act = $arr['ACT']*$unit_mult;
    if (is_numeric(substr(FormatSI($act, $precision), -1))) {
    	$sp = '';
    }
    $act_n = FormatSI($act, $precision);

  	// $def[$i+1] .= "GPRINT:var_n:LAST:\"%3.2lf %s".str_replace("%", "%%", $unit_n)." curr \" ";
  	$def[$i+1] .= rrd::gprint("var_n$sg", "LAST", "$fmt$sp%S".str_replace("%", "%%", $unit_n)." curr ");
  	$def[$i+1] .= rrd::gprint("var_n$sg", "MAX", "$fmt$sp%S".str_replace("%", "%%", $unit_n)." max ");
  	$def[$i+1] .= rrd::gprint("var_n$sg", "AVERAGE", "$fmt$sp%S".str_replace("%", "%%", $unit_n)." avg \\l");
    
    $sg += 1;
  }
  
  $saveline = false;
  if ($warning != '') {
  	$warning_n = $warning*$unit_mult;
  	$def[$i+1] .= rrd::hrule($warning_n, $_COLOR_WARN , "Warning \\t".FormatSI($warning_n, $precision).$unit_n."\\n");
  	$saveline = true;
  }
  if ($warn_min != '' and $warn_min != 0) {				// Don't waste graph space with Zero levels
  	$warn_min_n = $warn_min*$unit_mult;
  	$def[$i+1] .= rrd::hrule($warn_min_n, $_COLOR_WARN , "Warning (min) \\t".FormatSI($warn_min_n, $precision).$unit_n."\\n");
  	$saveline = true;
  }
  if ($warn_max != '') {
  	$warn_max_n = $warn_max*$unit_mult;
  	$def[$i+1] .= rrd::hrule($warn_max_n, $_COLOR_WARN , "Warning (max) \\t".FormatSI($warn_max_n, $precision).$unit_n."\\n");
  	$saveline = true;
  }
  if ($critical != '') {
  	$crit = $critical*$unit_mult;
  	$def[$i+1] .= rrd::hrule($crit, $_COLOR_CRIT , "Critical \\t".FormatSI($crit, $precision).$unit_n."\\n");	
  	$saveline = true;
  }
  if ($crit_min != '' and $crit_min != 0) {				// Don't waste graph space with Zero levels
  	$crit_min_n = $crit_min*$unit_mult;
  	$def[$i+1] .= rrd::hrule($crit_min_n, $_COLOR_CRIT , "Critical (min) \\t".FormatSI($crit_min_n, $precision).$unit_n."\\n");
  	$saveline = true;
  }
  if ($crit_max != '') {
  	$crit_max_n = $crit_max*$unit_mult;
  	$def[$i+1] .= rrd::hrule($crit_max_n, $_COLOR_CRIT , "Critical (max) \\t".FormatSI($crit_max_n, $precision).$unit_n."\\n");
  	$saveline = true;
  }
  if ($maximum != '') {
  	$maximum_n = $maximum*$unit_mult;
  	$def[$i+1] .= rrd::hrule($maximum_n, $_COLOR_MAX , "Total \\t".FormatSI($maximum_n, $precision).$unit_n."\\n");
  	$saveline = true;
  }
  if ($saveline == false and $note == '') $note = ' ';		// force newline, avoid overwriting perfdata line in case no hrules are present
  $txt = '<span foreground="blue" size="x-large">Test</span>';
  $def[$i+1] .= rrd::comment($note.'\\u');
	$def[$i+1] .= rrd::comment(' GEOTEK Template / '.$TEMPLATE[1].'\\r');
}



?>
