<?php

/* Pass in by reference! */
function graph_mysql_command_report ( &$rrdtool_graph ) {

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

    $title = 'MySQL Command Rates';
    $rrdtool_graph['title'] = $title; 
    $rrdtool_graph['lower-limit'] = '0';
    $rrdtool_graph['vertical-label'] = 'Queries/sec';
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
             "DEF:'select'='${rrd_dir}/${rrd_prefix}_select_qps.rrd':'sum':AVERAGE "
            ."DEF:'delete'='${rrd_dir}/${rrd_prefix}_delete_qps.rrd':'sum':AVERAGE "
            ."DEF:'insert'='${rrd_dir}/${rrd_prefix}_insert_qps.rrd':'sum':AVERAGE "
            ."DEF:'update'='${rrd_dir}/${rrd_prefix}_update_qps.rrd':'sum':AVERAGE "
            ."DEF:'deletemulti'='${rrd_dir}/${rrd_prefix}_delete_multi_qps.rrd':'sum':AVERAGE "
            ."DEF:'insertselect'='${rrd_dir}/${rrd_prefix}_insert_select_qps.rrd':'sum':AVERAGE "
            ."DEF:'updatemulti'='${rrd_dir}/${rrd_prefix}_update_multi_qps.rrd':'sum':AVERAGE "
            ."DEF:'replace'='${rrd_dir}/${rrd_prefix}_replace_qps.rrd':'sum':AVERAGE "
            ."DEF:'replaceselect'='${rrd_dir}/${rrd_prefix}_replace_select_qps.rrd':'sum':AVERAGE "
            ."DEF:'questions'='${rrd_dir}/${rrd_prefix}_questions_per_sec.rrd':'sum':AVERAGE "
            ."AREA:'questions'#FDC2C1:'Questions' "
            ."AREA:'select'#FC0019:'Select' "
            ."AREA:'delete'#FC7C25:'Delete':STACK "
            ."AREA:'insert'#FCF43F:'Insert':STACK "
            ."AREA:'update'#05D12E:'Update':STACK "
            ."AREA:'replace'#3373D5:'Replace':STACK "
            ."AREA:'deletemulti'#922B14:'Delete Multi':STACK "
            ."AREA:'insertselect'#AAABA1:'Insert Select':STACK "
            ."AREA:'updatemulti'#D8ABDE:'Update Multi':STACK "
            ."AREA:'replaceselect'#18BA9C:'Replace Select':STACK ";


    // If metrics like mem_used and mem_shared are not present we are likely not collecting them on this
    // host therefore we should not attempt to build anything and will likely end up with a broken
    // image. To avoid that we'll make an empty image
    if ( !file_exists("$rrd_dir/${rrd_prefix}_select_qps.rrd") && !file_exists("$rrd_dir/${rrd_prefix}_update_qps.rrd") ) 
      $rrdtool_graph[ 'series' ] = 'HRULE:1#FFCC33:"No matching metrics detected"';   
    else
      $rrdtool_graph[ 'series' ] = $series;

    return $rrdtool_graph;
}

?>
