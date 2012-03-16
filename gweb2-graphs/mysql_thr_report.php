<?php

/* Pass in by reference! */
function graph_mysql_thr_report ( &$rrdtool_graph ) {

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

    $rrdtool_graph['title'] = 'MySQL Threads';
    $rrdtool_graph['lower-limit'] = '0';
    $rrdtool_graph['vertical-label'] = 'threads (connections)';
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
    } else if ($size == 'large' || $size == 'xlarge') {
       $eol1 = '';
       $space1 = '      ';
       $space2 = '                 ';
    }

    $series =
        "DEF:'max_connections'='${rrd_dir}/${rrd_prefix}_max_connections.rrd':'sum':AVERAGE "
       ."DEF:'threads_connected'='${rrd_dir}/${rrd_prefix}_threads_connected.rrd':'sum':AVERAGE "
       ."DEF:'threads_running'='${rrd_dir}/${rrd_prefix}_threads_running.rrd':'sum':AVERAGE "
       ."AREA:'threads_connected'#{$conf['mem_cached_color']}:'Connected' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:threads_connected_pos=threads_connected,0,INF,LIMIT "
                . "VDEF:threads_connected_last=threads_connected_pos,LAST "
                . "VDEF:threads_connected_min=threads_connected_pos,MINIMUM " 
                . "VDEF:threads_connected_avg=threads_connected_pos,AVERAGE " 
                . "VDEF:threads_connected_max=threads_connected_pos,MAXIMUM " 
                . "GPRINT:'threads_connected_last':'${space1}Now\:%6.1lf%s' "
                . "GPRINT:'threads_connected_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'threads_connected_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'threads_connected_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'threads_running'#{$conf['mem_used_color']}:'Running' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:threads_running_pos=threads_running,0,INF,LIMIT "
                . "VDEF:threads_running_last=threads_running_pos,LAST "
                . "VDEF:threads_running_min=threads_running_pos,MINIMUM " 
                . "VDEF:threads_running_avg=threads_running_pos,AVERAGE " 
                . "VDEF:threads_running_max=threads_running_pos,MAXIMUM " 
                . "GPRINT:'threads_running_last':'  ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'threads_running_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'threads_running_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'threads_running_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "LINE2:'max_connections'#{$conf['cpu_num_color']}:'Limit' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:max_connections_pos=max_connections,0,INF,LIMIT "
                . "VDEF:max_connections_last=max_connections_pos,LAST "
                . "VDEF:max_connections_min=max_connections_pos,MINIMUM " 
                . "VDEF:max_connections_avg=max_connections_pos,AVERAGE " 
                . "VDEF:max_connections_max=max_connections_pos,MAXIMUM " 
                . "GPRINT:'max_connections_last':'    ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'max_connections_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'max_connections_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'max_connections_max':'${space1}Max\:%6.1lf%s\\l' ";
    }

    // If metrics like mem_used and mem_shared are not present we are likely not collecting them on this
    // host therefore we should not attempt to build anything and will likely end up with a broken
    // image. To avoid that we'll make an empty image
    if ( !file_exists("$rrd_dir/${rrd_prefix}_max_connections.rrd") ) 
      $rrdtool_graph[ 'series' ] = 'HRULE:1#FFCC33:"No matching metrics detected"';   
    else
      $rrdtool_graph[ 'series' ] = $series;

    return $rrdtool_graph;
}

?>
