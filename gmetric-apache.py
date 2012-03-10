#!/usr/bin/env python

from gmetric import Gmetric, MetricInfo
import os, time, urllib2, re
from optparse import OptionParser

class GmetricApache(Gmetric):
    """ collects basic apache web sever metrics """
    """
# Next section needs to be enabled in apache configuration
<IfModule mod_status.c>
ExtendedStatus On
<Location /server-status>
    SetHandler server-status
    Order deny,allow
    Deny from all
    Allow from localhost ip6-localhost 192.168.
</Location>

</IfModule>
"""
    GMETRIC_GROUP = 'apache'
    APACHE_CONFIG_FILENAME = '/etc/httpd/conf/httpd.conf'
    SERVER_TIMEOUT = 1
    METRIC_SEPARATOR = ': '
    METRIC_TOTAL_ACCESSES = 'Total Accesses'
    METRIC_TIMESTAMP = 'Timestamp'
    STATIC_METRICS = [ "BusyWorkers", "IdleWorkers" ]
    currentMetricsText = None
    port = 80
    hostname = 'localhost'

    def __init__(self, port = 80, hostname = 'localhost', gmetric = None, config = None):
        if port is None:
            self.port = 80
        else:
            self.port = port
        
        logFileName = 'gmetric-apache-' + hostname + '-' + str(self.port) + '.log'

        Gmetric.__init__(self, logFileName, gmetric, config)

        if hostname is not None:
            self.hostname = hostname

    def getMetricsTextFromServer(self):
        req = urllib2.Request("http://"  + self.hostname + ":" + str(self.port) + "/server-status?auto")
        try:
            response = urllib2.urlopen(req)
            text = response.read()
            self.currentMetricsText = self.METRIC_TIMESTAMP + self.METRIC_SEPARATOR + str(time.time()) + "\n" + text
            # TODO: check for error text here
            return self.currentMetricsText
        except Exception, e:
            print e
            raise

    def getMetricsFromServer(self):
        """ get metrics metrics dictionary format """
        if (self.currentMetricsText is None):
            self.getMetricsTextFromServer()
        if (self.currentMetricsText is not None):
            self.currentMetrics = self.parseValues(self.currentMetricsText)

    def getMaxClients(self):
        scoreboard = self.currentMetrics["Scoreboard"]
        if scoreboard is not None:
            return len(scoreboard)
        else:
            return None

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

    def getGmetricPrefix(self):
        """ creates prefix """
        return "apache-" + str(self.port) + "_"

    def run(self):
        """ main business """
        self.getMetricsFromFile()

        self.getMetricsFromServer()

        """ save metrics now """
        self.saveMetricsToFile()

        deltaTime = self.currentTimestamp - self.previousTimestamp

        prefix = self.getGmetricPrefix()
        metrics = []
        for metricName in self.STATIC_METRICS:
            mi = MetricInfo(prefix + metricName.lower(), self.currentMetrics[metricName], MetricInfo.GMETRIC_UINT32, None, self.GMETRIC_GROUP)
            metrics.append(mi)

        if deltaTime > 0:
            requestRate = self.calculateMetricRate(self.METRIC_TOTAL_ACCESSES, deltaTime)
            if requestRate is not None:
                metrics.append(MetricInfo(prefix + "rps", requestRate, MetricInfo.GMETRIC_DOUBLE, None, self.GMETRIC_GROUP))

        maxClients = self.getMaxClients()
        if maxClients is not None:
            metrics.append(MetricInfo(prefix + 'maxclients', maxClients, MetricInfo.GMETRIC_UINT32, None, self.GMETRIC_GROUP))

        self.sendMetrics(metrics)

if __name__ == "__main__":
    """ parsing port from command line if any """
    usage = "Usage: %prog -p <APACHE_PORT_NUMBER>"
    parser = OptionParser(usage, version="%prog 1.1")
    parser.add_option( "-p", "--port", type="int", default=80, help="Apache Web Server main port number. Default is: 80.")
    parser.add_option( "-g", "--gmetric", help="Path to gmetric executable.")
    parser.add_option( "-c", "--config", help="Path to gmond.conf.")
    (options, args) = parser.parse_args()
    ga = GmetricApache(options.port, 'localhost', options.gmetric, options.config)
#    ga.debug = 1
    ga.run()
