<?php

/* Pass in by reference! */
function graph_mysql_disk_report ( &$rrdtool_graph ) {

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

    $rrdtool_graph['title'] = 'MySQL Disk Usage';
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
    } else if ($size == 'large' || $size == 'xlarge') {
       $eol1 = '';
       $space1 = '      ';
       $space2 = '                 ';
    }

    $series = "DEF:'${rrd_prefix}_disk_total'='${rrd_dir}/${rrd_prefix}_disk_total.rrd':'sum':AVERAGE "
      . "DEF:'${rrd_prefix}_disk_free'='${rrd_dir}/${rrd_prefix}_disk_free.rrd':'sum':AVERAGE "
      . "CDEF:'kdisk_used'='${rrd_prefix}_disk_total','${rrd_prefix}_disk_free',- "
      . "CDEF:'kdisk_total'='${rrd_prefix}_disk_total',1,/ "
      . "AREA:'kdisk_used'#0AE0E3:'Used' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:kdisk_used_pos=kdisk_used,0,INF,LIMIT "
                . "VDEF:kdisk_used_last=kdisk_used_pos,LAST "
                . "VDEF:kdisk_used_min=kdisk_used_pos,MINIMUM " 
                . "VDEF:kdisk_used_avg=kdisk_used_pos,AVERAGE " 
                . "VDEF:kdisk_used_max=kdisk_used_pos,MAXIMUM " 
                . "GPRINT:'kdisk_used_last':'  ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'kdisk_used_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'kdisk_used_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'kdisk_used_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "LINE2:'kdisk_total'#${conf['cpu_num_color']}:'Total' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:kdisk_total_pos=kdisk_total,0,INF,LIMIT "
                . "VDEF:kdisk_total_last=kdisk_total_pos,LAST "
                . "VDEF:kdisk_total_min=kdisk_total_pos,MINIMUM " 
                . "VDEF:kdisk_total_avg=kdisk_total_pos,AVERAGE " 
                . "VDEF:kdisk_total_max=kdisk_total_pos,MAXIMUM " 
                . "GPRINT:'kdisk_total_last':' ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'kdisk_total_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'kdisk_total_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'kdisk_total_max':'${space1}Max\:%6.1lf%s\\l' ";
    }

    // If metrics like mem_used and mem_shared are not present we are likely not collecting them on this
    // host therefore we should not attempt to build anything and will likely end up with a broken
    // image. To avoid that we'll make an empty image
    if ( !file_exists("$rrd_dir/${rrd_prefix}_disk_total.rrd") ) 
      $rrdtool_graph[ 'series' ] = 'HRULE:1#FFCC33:"No matching metrics detected"';   
    else
      $rrdtool_graph[ 'series' ] = $series;

    return $rrdtool_graph;
}

?>
