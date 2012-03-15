<?php

/* Pass in by reference! */
function graph_apache_thr_report ( &$rrdtool_graph ) {

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

    $rrd_prefix = 'apache-80';

    $rrdtool_graph['title'] = 'Apache Threads';
    $rrdtool_graph['lower-limit'] = '0';
    $rrdtool_graph['vertical-label'] = 'Threads';
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
       $space1 = '   ';
       $space2 = '                 ';
    }
    
    $series =
         "DEF:'maxclients'='${rrd_dir}/${rrd_prefix}_maxclients.rrd':'sum':AVERAGE "
        ."DEF:'busyworkers'='${rrd_dir}/${rrd_prefix}_busyworkers.rrd':'sum':AVERAGE "
        ."DEF:'idleworkers'='${rrd_dir}/${rrd_prefix}_idleworkers.rrd':'sum':AVERAGE ";

    $series .= "AREA:'busyworkers'#${conf['mem_used_color']}:'Busy' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .= "CDEF:busyworkers_pos=busyworkers,0,INF,LIMIT "
                . "VDEF:busyworkers_last=busyworkers_pos,LAST "
                . "VDEF:busyworkers_min=busyworkers_pos,MINIMUM " 
                . "VDEF:busyworkers_avg=busyworkers_pos,AVERAGE " 
                . "VDEF:busyworkers_max=busyworkers_pos,MAXIMUM " 
                . "GPRINT:'busyworkers_last':' ${space1}Now\:%5.1lf%s' "
                . "GPRINT:'busyworkers_min':'${space1}Min\:%5.1lf%s${eol1}' "
                . "GPRINT:'busyworkers_avg':'${space1}Avg\:%5.1lf%s' "
                . "GPRINT:'busyworkers_max':'${space1}Max\:%5.1lf%s\\l' ";
    }

    $series .= "STACK:'idleworkers'#${conf['mem_cached_color']}:'Idle' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .= "CDEF:idleworkers_pos=idleworkers,0,INF,LIMIT "
                . "VDEF:idleworkers_last=idleworkers_pos,LAST "
                . "VDEF:idleworkers_min=idleworkers_pos,MINIMUM " 
                . "VDEF:idleworkers_avg=idleworkers_pos,AVERAGE " 
                . "VDEF:idleworkers_max=idleworkers_pos,MAXIMUM " 
                . "GPRINT:'idleworkers_last':' ${space1}Now\:%5.1lf%s' "
                . "GPRINT:'idleworkers_min':'${space1}Min\:%5.1lf%s${eol1}' "
                . "GPRINT:'idleworkers_avg':'${space1}Avg\:%5.1lf%s' "
                . "GPRINT:'idleworkers_max':'${space1}Max\:%5.1lf%s\\l' ";
    }

    $series .= "LINE2:'maxclients'#${conf['cpu_num_color']}:'Limit' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .= "CDEF:maxclients_pos=maxclients,0,INF,LIMIT "
                . "VDEF:maxclients_last=maxclients_pos,LAST "
                . "VDEF:maxclients_min=maxclients_pos,MINIMUM " 
                . "VDEF:maxclients_avg=maxclients_pos,AVERAGE " 
                . "VDEF:maxclients_max=maxclients_pos,MAXIMUM " 
                . "GPRINT:'maxclients_last':'${space1}Now\:%5.1lf%s' "
                . "GPRINT:'maxclients_min':'${space1}Min\:%5.1lf%s${eol1}' "
                . "GPRINT:'maxclients_avg':'${space1}Avg\:%5.1lf%s' "
                . "GPRINT:'maxclients_max':'${space1}Max\:%5.1lf%s\\l' ";
    }

    // If metrics like mem_used and mem_maxclients are not present we are likely not collecting them on this
    // host therefore we should not attempt to build anything and will likely end up with a broken
    // image. To avoid that we'll make an empty image
    if ( !file_exists("$rrd_dir/${rrd_prefix}_busyworkers.rrd") ) 
      $rrdtool_graph[ 'series' ] = 'HRULE:1#FFCC33:"No matching metrics detected"';   
    else
      $rrdtool_graph[ 'series' ] = $series;

    return $rrdtool_graph;
}

?>
