<?php

/* Pass in by reference! */
function graph_mysql_select_report ( &$rrdtool_graph ) {

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

    $rrdtool_graph['title'] = 'MySQL Select Types';
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
         "DEF:'fulljoin'='${rrd_dir}/${rrd_prefix}_select_full_join_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'fullrange'='${rrd_dir}/${rrd_prefix}_select_full_range_join_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'range'='${rrd_dir}/${rrd_prefix}_select_range_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'rangecheck'='${rrd_dir}/${rrd_prefix}_select_range_check_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'scan'='${rrd_dir}/${rrd_prefix}_select_scan_per_sec.rrd':'sum':AVERAGE "
        ."AREA:'fulljoin'#FC0019:'Full Join' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:fulljoin_pos=fulljoin,0,INF,LIMIT "
                . "VDEF:fulljoin_last=fulljoin_pos,LAST "
                . "VDEF:fulljoin_min=fulljoin_pos,MINIMUM " 
                . "VDEF:fulljoin_avg=fulljoin_pos,AVERAGE " 
                . "VDEF:fulljoin_max=fulljoin_pos,MAXIMUM " 
                . "GPRINT:'fulljoin_last':'  ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'fulljoin_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'fulljoin_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'fulljoin_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'fullrange'#FC7C25:'Full Range':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:fullrange_pos=fullrange,0,INF,LIMIT "
                . "VDEF:fullrange_last=fullrange_pos,LAST "
                . "VDEF:fullrange_min=fullrange_pos,MINIMUM " 
                . "VDEF:fullrange_avg=fullrange_pos,AVERAGE " 
                . "VDEF:fullrange_max=fullrange_pos,MAXIMUM " 
                . "GPRINT:'fullrange_last':' ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'fullrange_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'fullrange_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'fullrange_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'range'#FCF43F:'Range':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:range_pos=range,0,INF,LIMIT "
                . "VDEF:range_last=range_pos,LAST "
                . "VDEF:range_min=range_pos,MINIMUM " 
                . "VDEF:range_avg=range_pos,AVERAGE " 
                . "VDEF:range_max=range_pos,MAXIMUM " 
                . "GPRINT:'range_last':'      ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'range_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'range_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'range_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'rangecheck'#05D12E:'Range Check':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:rangecheck_pos=rangecheck,0,INF,LIMIT "
                . "VDEF:rangecheck_last=rangecheck_pos,LAST "
                . "VDEF:rangecheck_min=rangecheck_pos,MINIMUM " 
                . "VDEF:rangecheck_avg=rangecheck_pos,AVERAGE " 
                . "VDEF:rangecheck_max=rangecheck_pos,MAXIMUM " 
                . "GPRINT:'rangecheck_last':'${space1}Now\:%6.1lf%s' "
                . "GPRINT:'rangecheck_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'rangecheck_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'rangecheck_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'scan'#81B2EE:'Scan':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:scan_pos=scan,0,INF,LIMIT "
                . "VDEF:scan_last=scan_pos,LAST "
                . "VDEF:scan_min=scan_pos,MINIMUM " 
                . "VDEF:scan_avg=scan_pos,AVERAGE " 
                . "VDEF:scan_max=scan_pos,MAXIMUM " 
                . "GPRINT:'scan_last':'       ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'scan_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'scan_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'scan_max':'${space1}Max\:%6.1lf%s\\l' ";
    }

    // If metrics like mem_used and mem_shared are not present we are likely not collecting them on this
    // host therefore we should not attempt to build anything and will likely end up with a broken
    // image. To avoid that we'll make an empty image
    if ( !file_exists("$rrd_dir/${rrd_prefix}_select_full_join_per_sec.rrd") ) 
      $rrdtool_graph[ 'series' ] = 'HRULE:1#FFCC33:"No matching metrics detected"';   
    else
      $rrdtool_graph[ 'series' ] = $series;

    return $rrdtool_graph;
}

?>
