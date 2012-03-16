<?php

/* Pass in by reference! */
function graph_mysql_qcache_mem_report ( &$rrdtool_graph ) {

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

    $rrdtool_graph['title'] = 'MySQL Query Cache Memory';
    $rrdtool_graph['lower-limit'] = '0';
    //$rrdtool_graph['vertical-label'] = '';
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
       $space1 = '    ';
       $space2 = '                 ';
    }

    $series =
            "DEF:'cachesize'='${rrd_dir}/${rrd_prefix}_query_cache_size.rrd':'sum':AVERAGE "
            ."DEF:'freemem'='${rrd_dir}/${rrd_prefix}_qcache_free_memory.rrd':'sum':AVERAGE "
            ."CDEF:'usedmem'='cachesize','freemem',- "
            ."AREA:'usedmem'#74C46C:'Used Mem' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:usedmem_pos=usedmem,0,INF,LIMIT "
                . "VDEF:usedmem_last=usedmem_pos,LAST "
                . "VDEF:usedmem_min=usedmem_pos,MINIMUM " 
                . "VDEF:usedmem_avg=usedmem_pos,AVERAGE " 
                . "VDEF:usedmem_max=usedmem_pos,MAXIMUM " 
                . "GPRINT:'usedmem_last':' ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'usedmem_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'usedmem_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'usedmem_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'freemem'#FDC2C1:'Free Mem':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:freemem_pos=freemem,0,INF,LIMIT "
                . "VDEF:freemem_last=freemem_pos,LAST "
                . "VDEF:freemem_min=freemem_pos,MINIMUM " 
                . "VDEF:freemem_avg=freemem_pos,AVERAGE " 
                . "VDEF:freemem_max=freemem_pos,MAXIMUM " 
                . "GPRINT:'freemem_last':' ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'freemem_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'freemem_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'freemem_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    
    // If metrics like mem_used and mem_shared are not present we are likely not collecting them on this
    // host therefore we should not attempt to build anything and will likely end up with a broken
    // image. To avoid that we'll make an empty image
    if ( !file_exists("$rrd_dir/${rrd_prefix}_query_cache_size.rrd") ) 
      $rrdtool_graph[ 'series' ] = 'HRULE:1#FFCC33:"No matching metrics detected"';   
    else
      $rrdtool_graph[ 'series' ] = $series;

    return $rrdtool_graph;
}

?>
