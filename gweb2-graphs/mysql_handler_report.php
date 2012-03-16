<?php

/* Pass in by reference! */
function graph_mysql_handler_report ( &$rrdtool_graph ) {

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

    $rrdtool_graph['title'] = 'MySQL Handlers';
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
        "DEF:'write'='${rrd_dir}/${rrd_prefix}_handler_write_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'update'='${rrd_dir}/${rrd_prefix}_handler_update_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'delete'='${rrd_dir}/${rrd_prefix}_handler_delete_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'readfirst'='${rrd_dir}/${rrd_prefix}_handler_read_first_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'readkey'='${rrd_dir}/${rrd_prefix}_handler_read_key_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'readnext'='${rrd_dir}/${rrd_prefix}_handler_read_next_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'readprev'='${rrd_dir}/${rrd_prefix}_handler_read_prev_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'readrnd'='${rrd_dir}/${rrd_prefix}_handler_read_rnd_per_sec.rrd':'sum':AVERAGE "
        ."DEF:'readrndnext'='${rrd_dir}/${rrd_prefix}_handler_read_rnd_next_per_sec.rrd':'sum':AVERAGE "
        ."AREA:'write'#4D4A47:'Write' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:write_pos=write,0,INF,LIMIT "
                . "VDEF:write_last=write_pos,LAST "
                . "VDEF:write_min=write_pos,MINIMUM " 
                . "VDEF:write_avg=write_pos,AVERAGE " 
                . "VDEF:write_max=write_pos,MAXIMUM " 
                . "GPRINT:'write_last':'        ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'write_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'write_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'write_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'update'#C79F71:'Update':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:update_pos=update,0,INF,LIMIT "
                . "VDEF:update_last=update_pos,LAST "
                . "VDEF:update_min=update_pos,MINIMUM " 
                . "VDEF:update_avg=update_pos,AVERAGE " 
                . "VDEF:update_max=update_pos,MAXIMUM " 
                . "GPRINT:'update_last':'       ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'update_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'update_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'update_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'delete'#BDB8B3:'Delete':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:delete_pos=delete,0,INF,LIMIT "
                . "VDEF:delete_last=delete_pos,LAST "
                . "VDEF:delete_min=delete_pos,MINIMUM " 
                . "VDEF:delete_avg=delete_pos,AVERAGE " 
                . "VDEF:delete_max=delete_pos,MAXIMUM " 
                . "GPRINT:'delete_last':'       ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'delete_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'delete_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'delete_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'readfirst'#8C286E:'Read First':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:readfirst_pos=readfirst,0,INF,LIMIT "
                . "VDEF:readfirst_last=readfirst_pos,LAST "
                . "VDEF:readfirst_min=readfirst_pos,MINIMUM " 
                . "VDEF:readfirst_avg=readfirst_pos,AVERAGE " 
                . "VDEF:readfirst_max=readfirst_pos,MAXIMUM " 
                . "GPRINT:'readfirst_last':'   ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'readfirst_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'readfirst_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'readfirst_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'readkey'#BAB27F:'Read Key':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:readkey_pos=readkey,0,INF,LIMIT "
                . "VDEF:readkey_last=readkey_pos,LAST "
                . "VDEF:readkey_min=readkey_pos,MINIMUM " 
                . "VDEF:readkey_avg=readkey_pos,AVERAGE " 
                . "VDEF:readkey_max=readkey_pos,MAXIMUM " 
                . "GPRINT:'readkey_last':'     ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'readkey_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'readkey_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'readkey_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'readnext'#C02942:'Read Next':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:readnext_pos=readnext,0,INF,LIMIT "
                . "VDEF:readnext_last=readnext_pos,LAST "
                . "VDEF:readnext_min=readnext_pos,MINIMUM " 
                . "VDEF:readnext_avg=readnext_pos,AVERAGE " 
                . "VDEF:readnext_max=readnext_pos,MAXIMUM " 
                . "GPRINT:'readnext_last':'    ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'readnext_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'readnext_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'readnext_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'readprev'#FA6900:'Read Prev':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:readprev_pos=readprev,0,INF,LIMIT "
                . "VDEF:readprev_last=readprev_pos,LAST "
                . "VDEF:readprev_min=readprev_pos,MINIMUM " 
                . "VDEF:readprev_avg=readprev_pos,AVERAGE " 
                . "VDEF:readprev_max=readprev_pos,MAXIMUM " 
                . "GPRINT:'readprev_last':'    ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'readprev_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'readprev_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'readprev_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'readrnd'#5A3D31:'Read Rnd':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:readrnd_pos=readrnd,0,INF,LIMIT "
                . "VDEF:readrnd_last=readrnd_pos,LAST "
                . "VDEF:readrnd_min=readrnd_pos,MINIMUM " 
                . "VDEF:readrnd_avg=readrnd_pos,AVERAGE " 
                . "VDEF:readrnd_max=readrnd_pos,MAXIMUM " 
                . "GPRINT:'readrnd_last':'     ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'readrnd_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'readrnd_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'readrnd_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'readrndnext'#69D2E7:'Read Rnd Next':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:readrndnext_pos=readrndnext,0,INF,LIMIT "
                . "VDEF:readrndnext_last=readrndnext_pos,LAST "
                . "VDEF:readrndnext_min=readrndnext_pos,MINIMUM " 
                . "VDEF:readrndnext_avg=readrndnext_pos,AVERAGE " 
                . "VDEF:readrndnext_max=readrndnext_pos,MAXIMUM " 
                . "GPRINT:'readrndnext_last':'${space1}Now\:%6.1lf%s' "
                . "GPRINT:'readrndnext_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'readrndnext_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'readrndnext_max':'${space1}Max\:%6.1lf%s\\l' ";
    }


    // If metrics like mem_used and mem_shared are not present we are likely not collecting them on this
    // host therefore we should not attempt to build anything and will likely end up with a broken
    // image. To avoid that we'll make an empty image
    if ( !file_exists("$rrd_dir/${rrd_prefix}_handler_write_per_sec.rrd") ) 
      $rrdtool_graph[ 'series' ] = 'HRULE:1#FFCC33:"No matching metrics detected"';   
    else
      $rrdtool_graph[ 'series' ] = $series;

    return $rrdtool_graph;
}

?>
