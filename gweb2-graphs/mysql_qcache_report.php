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
    } else if ($size == 'large' || $size == 'xlarge') {
       $eol1 = '';
       $space1 = '  ';
       $space2 = '                 ';
    }

    $series =
            "DEF:'incache'='${rrd_dir}/${rrd_prefix}_qcache_queries_in_cache.rrd':'sum':AVERAGE "
            ."DEF:'hits'='${rrd_dir}/${rrd_prefix}_qcache_hits_per_sec.rrd':'sum':AVERAGE "
            ."DEF:'inserts'='${rrd_dir}/${rrd_prefix}_qcache_inserts_per_sec.rrd':'sum':AVERAGE "
            ."DEF:'notcached'='${rrd_dir}/${rrd_prefix}_qcache_not_cached_per_sec.rrd':'sum':AVERAGE "
            ."DEF:'prunes'='${rrd_dir}/${rrd_prefix}_qcache_lowmem_prunes_per_sec.rrd':'sum':AVERAGE "
            ."CDEF:'kincache'='incache',1000,/ "
            ."AREA:'inserts'#157523:'Inserts' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:inserts_pos=inserts,0,INF,LIMIT "
                . "VDEF:inserts_last=inserts_pos,LAST "
                . "VDEF:inserts_min=inserts_pos,MINIMUM " 
                . "VDEF:inserts_avg=inserts_pos,AVERAGE " 
                . "VDEF:inserts_max=inserts_pos,MAXIMUM " 
                . "GPRINT:'inserts_last':'             ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'inserts_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'inserts_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'inserts_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'notcached'#20A0BF:'Not Cached':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:notcached_pos=notcached,0,INF,LIMIT "
                . "VDEF:notcached_last=notcached_pos,LAST "
                . "VDEF:notcached_min=notcached_pos,MINIMUM " 
                . "VDEF:notcached_avg=notcached_pos,AVERAGE " 
                . "VDEF:notcached_max=notcached_pos,MAXIMUM " 
                . "GPRINT:'notcached_last':'          ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'notcached_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'notcached_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'notcached_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "LINE2:'hits'#E7AF2E:'Hits' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:hits_pos=hits,0,INF,LIMIT "
                . "VDEF:hits_last=hits_pos,LAST "
                . "VDEF:hits_min=hits_pos,MINIMUM " 
                . "VDEF:hits_avg=hits_pos,AVERAGE " 
                . "VDEF:hits_max=hits_pos,MAXIMUM " 
                . "GPRINT:'hits_last':'                ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'hits_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'hits_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'hits_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "LINE2:'prunes'#FC0019:'Low-Memory Prunes' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:prunes_pos=prunes,0,INF,LIMIT "
                . "VDEF:prunes_last=prunes_pos,LAST "
                . "VDEF:prunes_min=prunes_pos,MINIMUM " 
                . "VDEF:prunes_avg=prunes_pos,AVERAGE " 
                . "VDEF:prunes_max=prunes_pos,MAXIMUM " 
                . "GPRINT:'prunes_last':'   ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'prunes_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'prunes_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'prunes_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "LINE2:'kincache'#503AF9:'Queries In Cache (K)' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:kincache_pos=kincache,0,INF,LIMIT "
                . "VDEF:kincache_last=kincache_pos,LAST "
                . "VDEF:kincache_min=kincache_pos,MINIMUM " 
                . "VDEF:kincache_avg=kincache_pos,AVERAGE " 
                . "VDEF:kincache_max=kincache_pos,MAXIMUM " 
                . "GPRINT:'kincache_last':'${space1}Now\:%6.1lf%s' "
                . "GPRINT:'kincache_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'kincache_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'kincache_max':'${space1}Max\:%6.1lf%s\\l' ";
    }

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
