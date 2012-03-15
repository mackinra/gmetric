<?php

/* Pass in by reference! */
function graph_mysql_sort_report ( &$rrdtool_graph ) {

    global $conf,
           $context,
           $range,
           $rrd_dir,
           $size;

    if ($conf['strip_domainname']) {
       $hostname = strip_domainname($GLOBALS['hostname']);
    } else {
       $hostname = $GLOBALS['hostname'];
    }

    $rrd_prefix = 'mysql-3306';

    $rrdtool_graph['title'] = 'MySQL Sorts';
    $rrdtool_graph['lower-limit'] = '0';
    //$rrdtool_graph['vertical-label'] = 'Queries/sec';
    $rrdtool_graph['extras'] = ' --rigid';
    $rrdtool_graph['height'] += ($size == 'medium') ? 28 : 0;

    if ( $conf['graphreport_stats'] ) {
        $rrdtool_graph['height'] += ($size == 'medium') ? 4 : 0;
        $rmspace = '\\g';
    } else {
        $rmspace = '';
    }
    $rrdtool_graph['extras'] .= ($conf['graphreport_stats'] == true) ? ' --font LEGEND:7' : '';

    if ($size == 'small') {
       $eol1 = '\\l';
       $space1 = ' ';
       $space2 = '         ';
    } else if ($size == 'medium' || $size == 'default') {
       $eol1 = '';
       $space1 = ' ';
       $space2 = '';
    } else if ($size == 'large') {
       $eol1 = '';
       $space1 = '                 ';
       $space2 = '                 ';
    }

    $series =
        "DEF:'rows'='${rrd_dir}/${rrd_prefix}_sort_rows_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'range'='${rrd_dir}/${rrd_prefix}_sort_range_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'mergepasses'='${rrd_dir}/${rrd_prefix}_sort_merge_passes_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'scan'='${rrd_dir}/${rrd_prefix}_sort_scan_per_sec.rrd':'sum':AVERAGE "
        ."CDEF:'krows'='rows',1000,/ "
        ."AREA:'krows'#FFAB00:'Rows Sorted (K)' "
        ."LINE2:'range'#157419:'Range' "
        ."LINE2:'mergepasses'#DA4725:'Merge Passes' "
        ."LINE2:'scan'#4444FF:'Scan' "
        ;

    // If metrics like mem_used and mem_shared are not present we are likely not collecting them on this
    // host therefore we should not attempt to build anything and will likely end up with a broken
    // image. To avoid that we'll make an empty image
    if ( !file_exists("$rrd_dir/${rrd_prefix}_sort_rows_per_sec.rrd") ) 
      $rrdtool_graph[ 'series' ] = 'HRULE:1#FFCC33:"No matching metrics detected"';   
    else
      $rrdtool_graph[ 'series' ] = $series;

    return $rrdtool_graph;
}

?>
