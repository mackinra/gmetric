<?php

/* Pass in by reference! */
function graph_mysql_tmp_report ( &$rrdtool_graph ) {

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

    $rrdtool_graph['title'] = 'MySQL Temp Objects';
    $rrdtool_graph['lower-limit'] = '0';
    $rrdtool_graph['vertical-label'] = 'created per sec';
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
            "DEF:'tables'='${rrd_dir}/${rrd_prefix}_created_tmp_tables_per_sec.rrd':'sum':AVERAGE "
            ."DEF:'disktables'='${rrd_dir}/${rrd_prefix}_created_tmp_disk_tables_per_sec.rrd':'sum':AVERAGE "
            ."DEF:'files'='${rrd_dir}/${rrd_prefix}_created_tmp_files_per_sec.rrd':'sum':AVERAGE "
            ."AREA:'tables'#FFAB00:'Tables' "
//                        ."LINE2:'tables'#837C04:'Temp Tables' "
            ."LINE2:'disktables'#F51D30:'Disk Tables' "
            ."LINE2:'files'#157419:'Files' "
            ;

    // If metrics like mem_used and mem_shared are not present we are likely not collecting them on this
    // host therefore we should not attempt to build anything and will likely end up with a broken
    // image. To avoid that we'll make an empty image
    if ( !file_exists("$rrd_dir/${rrd_prefix}_created_tmp_tables_per_sec.rrd") ) 
      $rrdtool_graph[ 'series' ] = 'HRULE:1#FFCC33:"No matching metrics detected"';   
    else
      $rrdtool_graph[ 'series' ] = $series;

    return $rrdtool_graph;
}

?>
