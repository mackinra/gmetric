<?php

/* Pass in by reference! */
function graph_mysql_tmp_disk_report ( &$rrdtool_graph ) {

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

    $rrdtool_graph['title'] = 'MySQL Temp Disk Usage';
    $rrdtool_graph['lower-limit'] = '0';
    $rrdtool_graph['vertical-label'] = 'GBytes';
    $rrdtool_graph['extras'] = ' --base 1024 --rigid';
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

    $series = "DEF:'${rrd_prefix}_tmp_disk_total'='${rrd_dir}/${rrd_prefix}_tmp_disk_total.rrd':'sum':AVERAGE "
      . "DEF:'${rrd_prefix}_tmp_disk_free'='${rrd_dir}/${rrd_prefix}_tmp_disk_free.rrd':'sum':AVERAGE "
      . "CDEF:'kdisk_used'='${rrd_prefix}_tmp_disk_total','${rrd_prefix}_tmp_disk_free',- "
      . "CDEF:'kdisk_total'='${rrd_prefix}_tmp_disk_total',1,/ "
      . "AREA:'kdisk_used'#0AE0E3:'Used' "
      . "LINE2:'kdisk_total'#{$conf['cpu_num_color']}:'Total' "
      ;

    // If metrics like mem_used and mem_shared are not present we are likely not collecting them on this
    // host therefore we should not attempt to build anything and will likely end up with a broken
    // image. To avoid that we'll make an empty image
    if ( !file_exists("$rrd_dir/${rrd_prefix}_tmp_disk_total.rrd") ) 
      $rrdtool_graph[ 'series' ] = 'HRULE:1#FFCC33:"No matching metrics detected"';   
    else
      $rrdtool_graph[ 'series' ] = $series;

    return $rrdtool_graph;
}

?>
