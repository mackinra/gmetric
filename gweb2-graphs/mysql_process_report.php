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
    } else if ($size == 'large') {
       $eol1 = '';
       $space1 = '                 ';
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
        ."AREA:'closing'#FF0000:'Closing Tables' "
        ."AREA:'copying'#00FF00:'Copying to Tmp Table':STACK "
        ."AREA:'end'#0000FF:'End':STACK "
        ."AREA:'freeing'#FFFF00:'Freeing Items':STACK "
        ."AREA:'init'#FF00FF:'Init':STACK "
        ."AREA:'locked'#00FFFF:'Locked':STACK "
        ."AREA:'login'#800000:'Login':STACK "
        ."AREA:'preparing'#008000:'Preparing':STACK "
        ."AREA:'reading'#000080:'Reading From Net':STACK "
        ."AREA:'sending'#FDC2C1:'Sending Data':STACK "
        ."AREA:'sorting'#D8ABDE:'Sorting Result':STACK "
        ."AREA:'statistics'#20A0BF:'Statistics':STACK "
        ."AREA:'updating'#DA4725:'Updating':STACK "
        ."AREA:'writing'#FFAB00:'Writing To Net':STACK "
        ."AREA:'other'#C0C000:'Other':STACK "
        ."AREA:'none'#E0E0E0:'None':STACK ";

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
