<?php

/* Pass in by reference! */
function graph_mysql_process_report ( &$rrdtool_graph ) {

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

    $rrdtool_graph['title'] = 'MySQL Process States';
    $rrdtool_graph['lower-limit'] = '0';
    $rrdtool_graph['vertical-label'] = 'count';
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
       $space1 = '  ';
       $space2 = '                 ';
    }

    $series =
        "DEF:'closing'='${rrd_dir}/${rrd_prefix}_state_closing_tables.rrd':'sum':AVERAGE "
        ."DEF:'copying'='${rrd_dir}/${rrd_prefix}_state_copying_to_tmp_table.rrd':'sum':AVERAGE "
        ."DEF:'end'='${rrd_dir}/${rrd_prefix}_state_end.rrd':'sum':AVERAGE "
        ."DEF:'freeing'='${rrd_dir}/${rrd_prefix}_state_freeing_items.rrd':'sum':AVERAGE "
        ."DEF:'init'='${rrd_dir}/${rrd_prefix}_state_init.rrd':'sum':AVERAGE "
        ."DEF:'locked'='${rrd_dir}/${rrd_prefix}_state_locked.rrd':'sum':AVERAGE "
        ."DEF:'login'='${rrd_dir}/${rrd_prefix}_state_login.rrd':'sum':AVERAGE "
        ."DEF:'preparing'='${rrd_dir}/${rrd_prefix}_state_preparing.rrd':'sum':AVERAGE "
        ."DEF:'reading'='${rrd_dir}/${rrd_prefix}_state_reading_from_net.rrd':'sum':AVERAGE "
        ."DEF:'sending'='${rrd_dir}/${rrd_prefix}_state_sending_data.rrd':'sum':AVERAGE "
        ."DEF:'sorting'='${rrd_dir}/${rrd_prefix}_state_sorting_result.rrd':'sum':AVERAGE "
        ."DEF:'statistics'='${rrd_dir}/${rrd_prefix}_state_statistics.rrd':'sum':AVERAGE "
        ."DEF:'updating'='${rrd_dir}/${rrd_prefix}_state_updating.rrd':'sum':AVERAGE "
        ."DEF:'writing'='${rrd_dir}/${rrd_prefix}_state_writing_to_net.rrd':'sum':AVERAGE "
        ."DEF:'none'='${rrd_dir}/${rrd_prefix}_state_none.rrd':'sum':AVERAGE "
        ."DEF:'other'='${rrd_dir}/${rrd_prefix}_state_other.rrd':'sum':AVERAGE "
        ."AREA:'closing'#FF0000:'Closing Tables' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:closing_pos=closing,0,INF,LIMIT "
                . "VDEF:closing_last=closing_pos,LAST "
                . "VDEF:closing_min=closing_pos,MINIMUM " 
                . "VDEF:closing_avg=closing_pos,AVERAGE " 
                . "VDEF:closing_max=closing_pos,MAXIMUM " 
                . "GPRINT:'closing_last':'      ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'closing_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'closing_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'closing_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'copying'#00FF00:'Copying to Tmp Table':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:copying_pos=copying,0,INF,LIMIT "
                . "VDEF:copying_last=copying_pos,LAST "
                . "VDEF:copying_min=copying_pos,MINIMUM " 
                . "VDEF:copying_avg=copying_pos,AVERAGE " 
                . "VDEF:copying_max=copying_pos,MAXIMUM " 
                . "GPRINT:'copying_last':'${space1}Now\:%6.1lf%s' "
                . "GPRINT:'copying_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'copying_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'copying_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'end'#0000FF:'End':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:end_pos=end,0,INF,LIMIT "
                . "VDEF:end_last=end_pos,LAST "
                . "VDEF:end_min=end_pos,MINIMUM " 
                . "VDEF:end_avg=end_pos,AVERAGE " 
                . "VDEF:end_max=end_pos,MAXIMUM " 
                . "GPRINT:'end_last':'                 ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'end_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'end_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'end_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'freeing'#FFFF00:'Freeing Items':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:freeing_pos=freeing,0,INF,LIMIT "
                . "VDEF:freeing_last=freeing_pos,LAST "
                . "VDEF:freeing_min=freeing_pos,MINIMUM " 
                . "VDEF:freeing_avg=freeing_pos,AVERAGE " 
                . "VDEF:freeing_max=freeing_pos,MAXIMUM " 
                . "GPRINT:'freeing_last':'       ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'freeing_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'freeing_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'freeing_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'init'#FF00FF:'Init':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:init_pos=init,0,INF,LIMIT "
                . "VDEF:init_last=init_pos,LAST "
                . "VDEF:init_min=init_pos,MINIMUM " 
                . "VDEF:init_avg=init_pos,AVERAGE " 
                . "VDEF:init_max=init_pos,MAXIMUM " 
                . "GPRINT:'init_last':'                ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'init_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'init_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'init_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'locked'#00FFFF:'Locked':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:locked_pos=locked,0,INF,LIMIT "
                . "VDEF:locked_last=locked_pos,LAST "
                . "VDEF:locked_min=locked_pos,MINIMUM " 
                . "VDEF:locked_avg=locked_pos,AVERAGE " 
                . "VDEF:locked_max=locked_pos,MAXIMUM " 
                . "GPRINT:'locked_last':'              ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'locked_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'locked_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'locked_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'login'#800000:'Login':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:login_pos=login,0,INF,LIMIT "
                . "VDEF:login_last=login_pos,LAST "
                . "VDEF:login_min=login_pos,MINIMUM " 
                . "VDEF:login_avg=login_pos,AVERAGE " 
                . "VDEF:login_max=login_pos,MAXIMUM " 
                . "GPRINT:'login_last':'               ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'login_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'login_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'login_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'preparing'#008000:'Preparing':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:preparing_pos=preparing,0,INF,LIMIT "
                . "VDEF:preparing_last=preparing_pos,LAST "
                . "VDEF:preparing_min=preparing_pos,MINIMUM " 
                . "VDEF:preparing_avg=preparing_pos,AVERAGE " 
                . "VDEF:preparing_max=preparing_pos,MAXIMUM " 
                . "GPRINT:'preparing_last':'           ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'preparing_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'preparing_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'preparing_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'reading'#000080:'Reading From Net':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:reading_pos=reading,0,INF,LIMIT "
                . "VDEF:reading_last=reading_pos,LAST "
                . "VDEF:reading_min=reading_pos,MINIMUM " 
                . "VDEF:reading_avg=reading_pos,AVERAGE " 
                . "VDEF:reading_max=reading_pos,MAXIMUM " 
                . "GPRINT:'reading_last':'    ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'reading_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'reading_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'reading_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'sending'#FDC2C1:'Sending Data':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:sending_pos=sending,0,INF,LIMIT "
                . "VDEF:sending_last=sending_pos,LAST "
                . "VDEF:sending_min=sending_pos,MINIMUM " 
                . "VDEF:sending_avg=sending_pos,AVERAGE " 
                . "VDEF:sending_max=sending_pos,MAXIMUM " 
                . "GPRINT:'sending_last':'        ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'sending_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'sending_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'sending_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'sorting'#D8ABDE:'Sorting Result':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:sorting_pos=sorting,0,INF,LIMIT "
                . "VDEF:sorting_last=sorting_pos,LAST "
                . "VDEF:sorting_min=sorting_pos,MINIMUM " 
                . "VDEF:sorting_avg=sorting_pos,AVERAGE " 
                . "VDEF:sorting_max=sorting_pos,MAXIMUM " 
                . "GPRINT:'sorting_last':'      ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'sorting_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'sorting_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'sorting_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'statistics'#20A0BF:'Statistics':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:statistics_pos=statistics,0,INF,LIMIT "
                . "VDEF:statistics_last=statistics_pos,LAST "
                . "VDEF:statistics_min=statistics_pos,MINIMUM " 
                . "VDEF:statistics_avg=statistics_pos,AVERAGE " 
                . "VDEF:statistics_max=statistics_pos,MAXIMUM " 
                . "GPRINT:'statistics_last':'          ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'statistics_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'statistics_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'statistics_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'updating'#DA4725:'Updating':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:updating_pos=updating,0,INF,LIMIT "
                . "VDEF:updating_last=updating_pos,LAST "
                . "VDEF:updating_min=updating_pos,MINIMUM " 
                . "VDEF:updating_avg=updating_pos,AVERAGE " 
                . "VDEF:updating_max=updating_pos,MAXIMUM " 
                . "GPRINT:'updating_last':'            ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'updating_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'updating_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'updating_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'writing'#FFAB00:'Writing To Net':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:writing_pos=writing,0,INF,LIMIT "
                . "VDEF:writing_last=writing_pos,LAST "
                . "VDEF:writing_min=writing_pos,MINIMUM " 
                . "VDEF:writing_avg=writing_pos,AVERAGE " 
                . "VDEF:writing_max=writing_pos,MAXIMUM " 
                . "GPRINT:'writing_last':'      ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'writing_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'writing_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'writing_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'other'#C0C000:'Other':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:other_pos=other,0,INF,LIMIT "
                . "VDEF:other_last=other_pos,LAST "
                . "VDEF:other_min=other_pos,MINIMUM " 
                . "VDEF:other_avg=other_pos,AVERAGE " 
                . "VDEF:other_max=other_pos,MAXIMUM " 
                . "GPRINT:'other_last':'               ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'other_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'other_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'other_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'none'#E0E0E0:'None':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:none_pos=none,0,INF,LIMIT "
                . "VDEF:none_last=none_pos,LAST "
                . "VDEF:none_min=none_pos,MINIMUM " 
                . "VDEF:none_avg=none_pos,AVERAGE " 
                . "VDEF:none_max=none_pos,MAXIMUM " 
                . "GPRINT:'none_last':'                ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'none_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'none_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'none_max':'${space1}Max\:%6.1lf%s\\l' ";
    }

    // If metrics like mem_used and mem_shared are not present we are likely not collecting them on this
    // host therefore we should not attempt to build anything and will likely end up with a broken
    // image. To avoid that we'll make an empty image
    if ( !file_exists("$rrd_dir/${rrd_prefix}_state_closing_tables.rrd") ) 
      $rrdtool_graph[ 'series' ] = 'HRULE:1#FFCC33:"No matching metrics detected"';   
    else
      $rrdtool_graph[ 'series' ] = $series;

    return $rrdtool_graph;
}

?>
