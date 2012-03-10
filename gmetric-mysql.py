#!/usr/bin/env python

from gmetric import Gmetric, MetricInfo
import os, time, urllib2, re
from optparse import OptionParser
import MySQLdb


class GmetricMySQL(Gmetric):
    ''' collects basic mysql stats '''

    currentMetrics = dict()

    GMETRIC_GROUP = 'mysql'

    MYSQL_QUERY_RATE_OPERATIONS = set(['select',
                                 'insert', 'insert_select', 
                                 'replace', 'replace_select',
                                 'update', 'update_multi',
                                 'delete', 'delete_multi',
                                 'set_option', 'change_db',
                                 'show_fields', 'show_create_table',
                                 'begin', 'commit', 'rollback',
                                 'alter_table', 'truncate', 'create_table', 'drop_table'])
    MYSQL_RATE_OPERATIONS = set(['connections', 'qcache_hits', 'qcache_inserts', 'qcache_not_cached', 'qcache_lowmem_prunes',
                           'questions', 'threads_created', 'table_locks_immediate', 'table_locks_waited',
                           'created_tmp_tables', 'created_tmp_disk_tables', 'created_tmp_files', 'slow_queries',
                           'handler_delete', 'handler_read_first', 'handler_read_key', 'handler_read_next', 'handler_read_prev',
                           'handler_read_rnd', 'handler_read_rnd_next', 'handler_update', 'handler_write',
                           'sort_merge_passes', 'sort_range', 'sort_rows', 'sort_scan',
                           'select_full_join', 'select_full_range_join', 'select_range', 'select_range_check', 'select_scan'])
    MYSQL_STATE_VARIABLES = set(['threads_connected', 'threads_running', 'threads_cached', 'qcache_queries_in_cache',
                                 'qcache_free_memory', 'qcache_total_blocks', 'qcache_free_blocks'])
    MYSQL_PROCESS_STATES = set(['state_closing_tables', 'state_copying_to_tmp_table', 'state_end', 'state_freeing_items', 
                           'state_init', 'state_locked', 'state_login', 'state_preparing', 'state_reading_from_net', 
                           'state_sending_data', 'state_sorting_result', 'state_statistics', 'state_updating', 
                           'state_writing_to_net', 'state_none', 'state_other'])
    def __init__(self, mysql_username, mysql_password, mysql_hostname = 'localhost', mysql_port = None, gmetric = None, config = None):
        if mysql_port is None:
            self.mysqlPort = 3306
        else:
            self.mysqlPort = mysql_port

        logFileName = 'gmetric-mysql-' + mysql_hostname + '-' + str(self.mysqlPort) + '.log'

        Gmetric.__init__(self, logFileName, gmetric, config)

        if mysql_hostname is not None:
            self.mysql_hostname = mysql_hostname
    
        self.connection = MySQLdb.connect (host = self.mysql_hostname, port = self.mysqlPort, user = mysql_username, passwd = mysql_password)

    def __del__(self):
        if self.connection is not None:
            self.connection.close()
    
    def getMetricsTextFromServer(self):
        data = self.getSQLOperationsCounts()
        self.currentMetricsText = self.METRIC_TIMESTAMP + self.METRIC_SEPARATOR + str(self.currentTimestamp)
        for key in data:
            self.currentMetricsText = self.currentMetricsText + "\n" + key + self.METRIC_SEPARATOR + str(data[key])
        return self.currentMetricsText 

    def calculateMetricRate(self, metricName, deltaTime):
        if metricName not in self.currentMetrics:
            return None
        
        if metricName not in self.previousMetrics:
            return None
        
        newValue = self.currentMetrics[metricName]
        if newValue == 0:
            return 0
        
        oldValue = self.previousMetrics[metricName]
        
        deltaValue = newValue - oldValue
        if deltaValue < 0:
            return None
        
        metricRate = deltaValue / deltaTime
        return metricRate

    def getSQLOperationsCounts(self):
        self.currentMetrics.clear()
        cursor = self.connection.cursor (MySQLdb.cursors.DictCursor)
        cursor.execute ("SHOW GLOBAL STATUS")
        rows = cursor.fetchall ()
        for row in rows:
            name = row["Variable_name"].replace("Com_", "").lower()
            value = row["Value"]
            try:
                self.currentMetrics[name] = float(value)
            except ValueError, ve:
                pass
        
        cursor.close ()
        return self.currentMetrics

    def increment (self, processStates, key, incr):
        if key in processStates:
            processStates[key] += incr
        else:
            processStates[key] = incr
    
    def getProcessStateCounts(self):
        processStates = dict()
        cursor = self.connection.cursor (MySQLdb.cursors.DictCursor)
        try:
            cursor.execute ("SHOW PROCESSLIST")
            rows = cursor.fetchall ()
            processStates["state_other"] = 0;
            for row in rows:
                state = row["State"]
                if state is None or state == "":
                    state = "none"
                state = state.replace(" ","_").lower()
                if "state_"+state in self.MYSQL_PROCESS_STATES:
                    self.increment(processStates, "state_"+state, 1)
                else:
                    self.increment(processStates, "state_other", 1)
        except MySQLdb.Error, e:
            pass
        
        cursor.close ()
        return processStates

    def getMaxConnections(self):
       cursor = self.connection.cursor()
       cursor.execute ("SHOW GLOBAL VARIABLES LIKE 'max_connections'")
       row = cursor.fetchone()
       return row[1]	

    def getDataDir(self):
       cursor = self.connection.cursor()
       cursor.execute ("SHOW GLOBAL VARIABLES LIKE 'datadir'")
       row = cursor.fetchone()
       return row[1]	

    def getTempDir(self):
       cursor = self.connection.cursor()
       cursor.execute ("SHOW GLOBAL VARIABLES LIKE 'tmpdir'")
       row = cursor.fetchone()
       return row[1]	

    def getQueryCacheSize(self):
       cursor = self.connection.cursor()
       cursor.execute ("SHOW GLOBAL VARIABLES LIKE 'query_cache_size'")
       row = cursor.fetchone()
       return row[1]    

    def getSlaveLag(self):
       cursor = self.connection.cursor(MySQLdb.cursors.DictCursor)
       try:
           cursor.execute("SHOW SLAVE STATUS")
           row = cursor.fetchone()
           if row is not None:
               return row["Seconds_Behind_Master"]
       except MySQLdb.Error, e:
           pass
       return None

    def getGmetricPrefix(self):
        """ creates prefix """
        return "mysql-" + str(self.mysqlPort) + "_"

    def run(self):
        self.getMetricsFromFile()
        
        maxConnections = self.getMaxConnections()

        if self.mysql_hostname == 'localhost' or self.mysql_hostname == '127.0.0.1':
            dataDir = self.getDataDir()
            diskStat = os.statvfs(dataDir)
            diskFree = float(diskStat.f_frsize * diskStat.f_bavail) / (1024*1024*1024)
            diskTotal = float(diskStat.f_frsize * diskStat.f_blocks) / (1024*1024*1024)
    
            tmpDir = self.getTempDir()
            tmpDiskStat = os.statvfs(tmpDir)
            tmpDiskFree = float(tmpDiskStat.f_frsize * tmpDiskStat.f_bavail) / (1024*1024*1024)
            tmpDiskTotal = float(tmpDiskStat.f_frsize * tmpDiskStat.f_blocks) / (1024*1024*1024)

        slaveLag = self.getSlaveLag()
        self.getMetricsTextFromServer()
        self.saveMetricsToFile()

        processStates = self.getProcessStateCounts()

        deltaTime = self.currentTimestamp - self.previousTimestamp

        prefix = self.getGmetricPrefix()
        metrics = []
        metrics.append(MetricInfo(prefix + "max_connections", maxConnections, MetricInfo.GMETRIC_UINT32, None, self.GMETRIC_GROUP))
        if self.mysql_hostname == 'localhost' or self.mysql_hostname == '127.0.0.1':
            metrics.append(MetricInfo(prefix + "disk_free", round(diskFree,2), MetricInfo.GMETRIC_FLOAT, "GB", self.GMETRIC_GROUP))
            metrics.append(MetricInfo(prefix + "disk_total", round(diskTotal,2), MetricInfo.GMETRIC_FLOAT, "GB", self.GMETRIC_GROUP))
            metrics.append(MetricInfo(prefix + "tmp_disk_free", round(tmpDiskFree,2), MetricInfo.GMETRIC_FLOAT, "GB", self.GMETRIC_GROUP))
            metrics.append(MetricInfo(prefix + "tmp_disk_total", round(tmpDiskTotal,2), MetricInfo.GMETRIC_FLOAT, "GB", self.GMETRIC_GROUP))
        if deltaTime > 0:
            for metricName in self.MYSQL_QUERY_RATE_OPERATIONS:
                requestRate = self.calculateMetricRate(metricName, deltaTime)
                if requestRate is not None:
                    metrics.append(MetricInfo(prefix + metricName.lower() + '_qps', requestRate, MetricInfo.GMETRIC_DOUBLE, None, self.GMETRIC_GROUP))
            for metricName in self.MYSQL_RATE_OPERATIONS:
                requestRate = self.calculateMetricRate(metricName, deltaTime)
                if requestRate is not None:
                    metrics.append(MetricInfo(prefix + metricName.lower() + '_per_sec', requestRate, MetricInfo.GMETRIC_DOUBLE, None, self.GMETRIC_GROUP))
        if processStates:
            for metricName in self.MYSQL_PROCESS_STATES:
                if metricName in processStates:
                    metrics.append(MetricInfo(prefix + metricName.lower(), processStates[metricName], MetricInfo.GMETRIC_UINT32, None, self.GMETRIC_GROUP))
                else:
                    metrics.append(MetricInfo(prefix + metricName.lower(), 0, MetricInfo.GMETRIC_UINT32, None, self.GMETRIC_GROUP))
        for metricName in self.MYSQL_STATE_VARIABLES:
            if metricName in self.currentMetrics:
                metrics.append(MetricInfo(prefix + metricName.lower(), self.currentMetrics[metricName], MetricInfo.GMETRIC_UINT32, None, self.GMETRIC_GROUP))

        metrics.append(MetricInfo(prefix + "query_cache_size", self.getQueryCacheSize(), MetricInfo.GMETRIC_UINT32, "bytes", self.GMETRIC_GROUP))
        
        if slaveLag is not None:
            metrics.append(MetricInfo(prefix + "slave_lag", slaveLag, MetricInfo.GMETRIC_UINT32, None, self.GMETRIC_GROUP))
            
        self.sendMetrics(metrics)

if __name__ == "__main__":
    """ parsing port from command line if any """
    usage = "Usage: %prog [-p MYSQL_PORT_NUMBER]"
    parser = OptionParser(usage, version="%prog 1.1")
    parser.add_option( "-p", "--port", type="int", default=3306, help="MySQL Server main port number. Default is: 3306.")
    parser.add_option( "-g", "--gmetric", help="Path to gmetric executable.")
    parser.add_option( "-c", "--config", help="Path to gmond.conf.")
    (options, args) = parser.parse_args()
    gm = GmetricMySQL('gmetric', 'De03I0PxIkCN', '127.0.0.1', options.port, options.gmetric, options.config)
    gm.run()
