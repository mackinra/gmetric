#!/usr/bin/env python

import sys, os, time

class MetricInfo:
    """ defines simple metric holder """

    GMETRIC_UINT32 = 'uint32'
    GMETRIC_DOUBLE = 'double'
    GMETRIC_FLOAT  = 'float'
    GMETRIC_MAX_LIFETIME = 3600 * 24
    
    def __init__(self, name, value, type, unit=None, group=None):
        self.name = name
        self.value = str(value)
        self.type = type
        self.unit = unit
        self.group = group

class GmetricBase:
    """ Parent gmetric script """
    debug = None
    gmetric_bin = "/usr/local/bin/gmetric"
    gmetric_config = "/usr/local/etc/gmond.conf"

    def __init__(self, gmetric_bin = "gmetric", gmetric_conf = "/etc/gmond.conf"):
        """ Init class with alternate gmetric bin """
        if (gmetric_bin is not None):
            self.gmetric_bin = gmetric_bin
        if (gmetric_conf is not None):
            self.gmetric_config = gmetric_conf

    def sendMetric(self, metric):
        """ writes gmetric data 
        @param metric: single instance of MetricInfo"""
        if metric is None:
            self.logMessage("None metric given")
            return None

        gmetric_cmd = self.gmetric_bin + " -d " + str(MetricInfo.GMETRIC_MAX_LIFETIME) + " -c "  + self.gmetric_config + " -n " + metric.name.replace(" ", "_") + " -v " + metric.value + " -t " + metric.type
        if (metric.unit is not None):
            gmetric_cmd += (" -u " +  metric.unit.replace(" ","_"))
        if (metric.group is not None):
            gmetric_cmd += (" -g " +  metric.group.replace(" ","_"))
        self.logMessage(gmetric_cmd)
        if self.debug is None:
            try:
                result = os.system(gmetric_cmd)
                if (result != 0):
                    self.logMessage(gmetric_cmd + " returned " + str(result))
                    return result
            except OSError, e:
                self.logMessage("Unable to send metrics ")

    def sendMetrics(self, metrics):
        """ Sends multiple metrics 
        @param metric_infos: array of MetricInfo"""
        count = 0
        for metric in metrics:
            self.sendMetric(metric)
            count += 1
        print str(count) + ' metrics sent'

    def logMessage(self, message):
        """ Logs messages to STDUOT 
        @param message: message to log """
        
        if (self.debug is not None):
            sys.stdout.write(message + "\n")

class Gmetric(GmetricBase):
    STATUS_DIRECTORY = '/tmp/gmetric'
    statusFilePath = None
    METRIC_TIMESTAMP = 'Timestamp'
    METRIC_SEPARATOR = ': '
    currentMetricsText = None
    
    def __init__(self, statusFileName, gmetric_bin = "/usr/bin/gmetric", gmetric_conf = "/etc/gmond.conf"):
        GmetricBase.__init__(self, gmetric_bin, gmetric_conf)
        if not os.path.isdir(self.STATUS_DIRECTORY):
            os.makedirs(self.STATUS_DIRECTORY)
        self.statusFilePath = self.STATUS_DIRECTORY + '/' + statusFileName;
        self.currentTimestamp = time.time()

    def parseValues(self, text):
        """ Parses values either from response OR file data """
        if text is None:
            return None

        lines = text.split("\n")
        result = dict()
        for line in lines:
            parts = line.split(self.METRIC_SEPARATOR)
            if len(parts) == 2:
                try:
                    result[parts[0]] = float(parts[1])
                except ValueError, ve:
                    result[parts[0]] = parts[1]

        if (len(result) == 0):
            return None
        return result

    def getMetricsFromFile(self):
        """ get metrics list from file """
        self.previousTimestamp = self.currentTimestamp
        if os.path.isfile(self.statusFilePath):
            f = open(self.statusFilePath, 'r')
            text = f.read()
            self.previousMetrics = self.parseValues(text)
            if self.METRIC_TIMESTAMP in self.previousMetrics:
                self.previousTimestamp = self.previousMetrics[self.METRIC_TIMESTAMP]
            else:
                self.logMessage("No previous timestamp in %s" % self.statusFilePath)
            return self.previousMetrics

    def saveMetricsToFile(self):
        """ writes metrics to file, if possible """
        if (self.currentMetricsText is not None):
            try:
                f = open(self.statusFilePath, 'w')
                f.write(self.currentMetricsText)
                f.close()
            except Exception, e:
                self.logMessage("Unable to save metrics")
                
if __name__ == "__main__":
    """ this is simple unit test """
    metric1 = MetricInfo("apache_rps", 1 , "double")
    metric2 = MetricInfo("tomcat_rps", 2 , "double")
    metrics = [ metric1, metric2 ]
    
    gmetric = Gmetric()
    gmetric.debug = 1
    gmetric.sendMetrics(metrics)
