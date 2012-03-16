<?php

/* Pass in by reference! */
function graph_mysql_qcache_block_report ( &$rrdtool_graph ) {

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

    $rrdtool_graph['title'] = 'MySQL Query Cache Blocks';
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
            "DEF:'totalblocks'='${rrd_dir}/${rrd_prefix}_qcache_total_blocks.rrd':'sum':AVERAGE "
            ."DEF:'freeblocks'='${rrd_dir}/${rrd_prefix}_qcache_free_blocks.rrd':'sum':AVERAGE "
            ."CDEF:'usedblocks'='totalblocks','freeblocks',- "
            ."AREA:'usedblocks'#00A0C1:'Used Blocks' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:usedblocks_pos=usedblocks,0,INF,LIMIT "
                . "VDEF:usedblocks_last=usedblocks_pos,LAST "
                . "VDEF:usedblocks_min=usedblocks_pos,MINIMUM " 
                . "VDEF:usedblocks_avg=usedblocks_pos,AVERAGE " 
                . "VDEF:usedblocks_max=usedblocks_pos,MAXIMUM " 
                . "GPRINT:'usedblocks_last':' ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'usedblocks_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'usedblocks_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'usedblocks_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "LINE2:'totalblocks'#{$conf['cpu_num_color']}:'Total Blocks' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:totalblocks_pos=totalblocks,0,INF,LIMIT "
                . "VDEF:totalblocks_last=totalblocks_pos,LAST "
                . "VDEF:totalblocks_min=totalblocks_pos,MINIMUM " 
                . "VDEF:totalblocks_avg=totalblocks_pos,AVERAGE " 
                . "VDEF:totalblocks_max=totalblocks_pos,MAXIMUM " 
                . "GPRINT:'totalblocks_last':'${space1}Now\:%6.1lf%s' "
                . "GPRINT:'totalblocks_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'totalblocks_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'totalblocks_max':'${space1}Max\:%6.1lf%s\\l' ";
    }

    // If metrics like mem_used and mem_shared are not present we are likely not collecting them on this
    // host therefore we should not attempt to build anything and will likely end up with a broken
    // image. To avoid that we'll make an empty image
    if ( !file_exists("$rrd_dir/${rrd_prefix}_qcache_total_blocks.rrd") ) 
      $rrdtool_graph[ 'series' ] = 'HRULE:1#FFCC33:"No matching metrics detected"';   
    else
      $rrdtool_graph[ 'series' ] = $series;

    return $rrdtool_graph;
}

?>
