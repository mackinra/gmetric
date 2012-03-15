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
    } else if ($size == 'large') {
       $eol1 = '';
       $space1 = '                 ';
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
        ."AREA:'write'#4D4A47:'Write' "
        ."AREA:'update'#C79F71:'Update':STACK "
        ."AREA:'delete'#BDB8B3:'Delete':STACK "
        ."AREA:'readfirst'#8C286E:'Read First':STACK "
        ."AREA:'readkey'#BAB27F:'Read Key':STACK "
        ."AREA:'readnext'#C02942:'Read Next':STACK "
        ."AREA:'readprev'#FA6900:'Read Prev':STACK "
        ."AREA:'readrnd'#5A3D31:'Read Rnd':STACK "
        ."AREA:'readrndnext'#69D2E7:'Read Rnd Next':STACK "
        ;


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
