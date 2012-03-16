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
    } else if ($size == 'large' || $size == 'xlarge') {
       $eol1 = '';
       $space1 = '     ';
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
            ."AREA:'questions'#FDC2C1:'Questions' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .= "CDEF:questions_pos=questions,0,INF,LIMIT "
                . "VDEF:questions_last=questions_pos,LAST "
                . "VDEF:questions_min=questions_pos,MINIMUM " 
                . "VDEF:questions_avg=questions_pos,AVERAGE " 
                . "VDEF:questions_max=questions_pos,MAXIMUM " 
                . "GPRINT:'questions_last':'     ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'questions_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'questions_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'questions_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'select'#FC0019:'Select' ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:select_pos=select,0,INF,LIMIT "
                . "VDEF:select_last=select_pos,LAST "
                . "VDEF:select_min=select_pos,MINIMUM " 
                . "VDEF:select_avg=select_pos,AVERAGE " 
                . "VDEF:select_max=select_pos,MAXIMUM " 
                . "GPRINT:'select_last':'        ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'select_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'select_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'select_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'delete'#FC7C25:'Delete':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:delete_pos=delete,0,INF,LIMIT "
                . "VDEF:delete_last=delete_pos,LAST "
                . "VDEF:delete_min=delete_pos,MINIMUM " 
                . "VDEF:delete_avg=delete_pos,AVERAGE " 
                . "VDEF:delete_max=delete_pos,MAXIMUM " 
                . "GPRINT:'delete_last':'        ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'delete_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'delete_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'delete_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'insert'#FCF43F:'Insert':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:insert_pos=insert,0,INF,LIMIT "
                . "VDEF:insert_last=insert_pos,LAST "
                . "VDEF:insert_min=insert_pos,MINIMUM " 
                . "VDEF:insert_avg=insert_pos,AVERAGE " 
                . "VDEF:insert_max=insert_pos,MAXIMUM " 
                . "GPRINT:'insert_last':'        ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'insert_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'insert_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'insert_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'update'#05D12E:'Update':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:update_pos=update,0,INF,LIMIT "
                . "VDEF:update_last=update_pos,LAST "
                . "VDEF:update_min=update_pos,MINIMUM " 
                . "VDEF:update_avg=update_pos,AVERAGE " 
                . "VDEF:update_max=update_pos,MAXIMUM " 
                . "GPRINT:'update_last':'        ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'update_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'update_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'update_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'replace'#3373D5:'Replace':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:replace_pos=replace,0,INF,LIMIT "
                . "VDEF:replace_last=replace_pos,LAST "
                . "VDEF:replace_min=replace_pos,MINIMUM " 
                . "VDEF:replace_avg=replace_pos,AVERAGE " 
                . "VDEF:replace_max=replace_pos,MAXIMUM " 
                . "GPRINT:'replace_last':'       ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'replace_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'replace_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'replace_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'deletemulti'#922B14:'Delete Multi':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:deletemulti_pos=deletemulti,0,INF,LIMIT "
                . "VDEF:deletemulti_last=deletemulti_pos,LAST "
                . "VDEF:deletemulti_min=deletemulti_pos,MINIMUM " 
                . "VDEF:deletemulti_avg=deletemulti_pos,AVERAGE " 
                . "VDEF:deletemulti_max=deletemulti_pos,MAXIMUM " 
                . "GPRINT:'deletemulti_last':'  ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'deletemulti_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'deletemulti_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'deletemulti_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'insertselect'#AAABA1:'Insert Select':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:insertselect_pos=insertselect,0,INF,LIMIT "
                . "VDEF:insertselect_last=insertselect_pos,LAST "
                . "VDEF:insertselect_min=insertselect_pos,MINIMUM " 
                . "VDEF:insertselect_avg=insertselect_pos,AVERAGE " 
                . "VDEF:insertselect_max=insertselect_pos,MAXIMUM " 
                . "GPRINT:'insertselect_last':' ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'insertselect_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'insertselect_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'insertselect_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'updatemulti'#D8ABDE:'Update Multi':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:updatemulti_pos=updatemulti,0,INF,LIMIT "
                . "VDEF:updatemulti_last=updatemulti_pos,LAST "
                . "VDEF:updatemulti_min=updatemulti_pos,MINIMUM " 
                . "VDEF:updatemulti_avg=updatemulti_pos,AVERAGE " 
                . "VDEF:updatemulti_max=updatemulti_pos,MAXIMUM " 
                . "GPRINT:'updatemulti_last':'  ${space1}Now\:%6.1lf%s' "
                . "GPRINT:'updatemulti_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'updatemulti_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'updatemulti_max':'${space1}Max\:%6.1lf%s\\l' ";
    }
    $series .= "AREA:'replaceselect'#18BA9C:'Replace Select':STACK ";
    if ( $conf['graphreport_stats'] && in_array($size,array('large','xlarge')) ) {
        $series .="CDEF:replaceselect_pos=replaceselect,0,INF,LIMIT "
                . "VDEF:replaceselect_last=replaceselect_pos,LAST "
                . "VDEF:replaceselect_min=replaceselect_pos,MINIMUM " 
                . "VDEF:replaceselect_avg=replaceselect_pos,AVERAGE " 
                . "VDEF:replaceselect_max=replaceselect_pos,MAXIMUM " 
                . "GPRINT:'replaceselect_last':'${space1}Now\:%6.1lf%s' "
                . "GPRINT:'replaceselect_min':'${space1}Min\:%6.1lf%s${eol1}' "
                . "GPRINT:'replaceselect_avg':'${space1}Avg\:%6.1lf%s' "
                . "GPRINT:'replaceselect_max':'${space1}Max\:%6.1lf%s\\l' ";
    }


    // If metrics like mem_used and mem_shared are not present we are likely not collecting them on this
    // host therefore we should not attempt to build anything and will likely end up with a broken
    // image. To avoid that we'll make an empty image
    if ( !file_exists("$rrd_dir/${rrd_prefix}_select_qps.rrd") && !file_exists("$rrd_dir/${rrd_prefix}_update_qps.rrd") ) 
      //$rrdtool_graph[ 'series' ] = 'HRULE:1#FFCC33:"No matching metrics detected"';   
        $rrdtool_graph = NULL;
    else
      $rrdtool_graph[ 'series' ] = $series;

    return $rrdtool_graph;
}

?>
