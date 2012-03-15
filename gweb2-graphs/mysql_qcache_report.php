<?php

/* Pass in by reference! */
function graph_mysql_qcache_report ( &$rrdtool_graph ) {

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

    $rrdtool_graph['title'] = 'MySQL Query Cache';
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
    } else if ($size == 'large') {
       $eol1 = '';
       $space1 = '                 ';
       $space2 = '                 ';
    }

    $series =
            "DEF:'incache'='${rrd_dir}/${rrd_prefix}_qcache_queries_in_cache.rrd':'sum':AVERAGE "
            ."DEF:'hits'='${rrd_dir}/${rrd_prefix}_qcache_hits_per_sec.rrd':'sum':AVERAGE "
            ."DEF:'inserts'='${rrd_dir}/${rrd_prefix}_qcache_inserts_per_sec.rrd':'sum':AVERAGE "
            ."DEF:'notcached'='${rrd_dir}/${rrd_prefix}_qcache_not_cached_per_sec.rrd':'sum':AVERAGE "
            ."DEF:'prunes'='${rrd_dir}/${rrd_prefix}_qcache_lowmem_prunes_per_sec.rrd':'sum':AVERAGE "
            ."CDEF:'kincache'='incache',1000,/ "
            ."AREA:'inserts'#157523:'Inserts' "
            ."AREA:'notcached'#20A0BF:'Not Cached':STACK "
            ."LINE2:'hits'#E7AF2E:'Hits' "
            ."LINE2:'prunes'#FC0019:'Low-Memory Prunes' "
            ."LINE2:'kincache'#503AF9:'Queries In Cache (K)' "
            ;

    // If metrics like mem_used and mem_shared are not present we are likely not collecting them on this
    // host therefore we should not attempt to build anything and will likely end up with a broken
    // image. To avoid that we'll make an empty image
    if ( !file_exists("$rrd_dir/${rrd_prefix}_qcache_queries_in_cache.rrd") ) 
      $rrdtool_graph[ 'series' ] = 'HRULE:1#FFCC33:"No matching metrics detected"';   
    else
      $rrdtool_graph[ 'series' ] = $series;

    return $rrdtool_graph;
}

?>
