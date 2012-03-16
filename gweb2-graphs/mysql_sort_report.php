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
    } else if ($size == 'large' || $size == 'xlarge') {
       $eol1 = '';
       $space1 = '   ';
       $space2 = '                 ';
    }

    $series =
        "DEF:'rows'='${rrd_dir}/${rrd_prefix}_sort_rows_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'range'='${rrd_dir}/${rrd_prefix}_sort_range_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'mergepasses'='${rrd_dir}/${rrd_prefix}_sort_merge_passes_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'scan'='${rrd_dir}/${rrd_prefix}_sort_scan_per_sec.rrd':'sum':AVERAGE "
        ."CDEF:'krows'='rows',1000,/ "
        ."AREA:'krows'#FFAB00:'Rows Sorted (K)' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:krows_pos=krows,0,INF,LIMIT "
                . "VDEF:krows_last=krows_pos,LAST "
                . "VDEF:krows_min=krows_pos,MINIMUM " 
                . "VDEF:krows_avg=krows_pos,AVERAGE " 
                . "VDEF:krows_max=krows_pos,MAXIMUM " 
                . "GPRINT:'krows_last':'${space1}Now\:%6.1lf%s' "
                . "GPRINT:'krows_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'krows_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'krows_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "LINE2:'range'#157419:'Range' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:range_pos=range,0,INF,LIMIT "
                . "VDEF:range_last=range_pos,LAST "
                . "VDEF:range_min=range_pos,MINIMUM " 
                . "VDEF:range_avg=range_pos,AVERAGE " 
                . "VDEF:range_max=range_pos,MAXIMUM " 
                . "GPRINT:'range_last':'          ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'range_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'range_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'range_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "LINE2:'mergepasses'#DA4725:'Merge Passes' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:mergepasses_pos=mergepasses,0,INF,LIMIT "
                . "VDEF:mergepasses_last=mergepasses_pos,LAST "
                . "VDEF:mergepasses_min=mergepasses_pos,MINIMUM " 
                . "VDEF:mergepasses_avg=mergepasses_pos,AVERAGE " 
                . "VDEF:mergepasses_max=mergepasses_pos,MAXIMUM " 
                . "GPRINT:'mergepasses_last':'   ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'mergepasses_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'mergepasses_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'mergepasses_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "LINE2:'scan'#4444FF:'Scan' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:scan_pos=scan,0,INF,LIMIT "
                . "VDEF:scan_last=scan_pos,LAST "
                . "VDEF:scan_min=scan_pos,MINIMUM " 
                . "VDEF:scan_avg=scan_pos,AVERAGE " 
                . "VDEF:scan_max=scan_pos,MAXIMUM " 
                . "GPRINT:'scan_last':'           ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'scan_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'scan_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'scan_max':'${space1}Max\:%6.1lf%s\\l' ";
    }

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
