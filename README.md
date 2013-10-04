gmetric
=======

Test script by running in manually first:
$ sudo su - 
$ /usr/bin/python /usr/local/gmetric/bin/gmetric-mysql.py

Common issues are:

1) If MySQLdb python package is missing:
Traceback (most recent call last):
  File "/usr/local/gmetric/bin/gmetric-mysql.py", line 12, in <module>
    import MySQLdb
ImportError: No module named MySQLdb

Solution:
$ sudo aptitude install python2.5-mysqldb

2) script is unable to connect to mysql server
Traceback (most recent call last):
  File "/usr/local/gmetric/bin/gmetric-mysql.py", line 117, in <module>
    ga = GmetricMySQL('gmetric', 'gm3tr1c', '127.0.0.1', options.port)
  File "/usr/local/gmetric/bin/gmetric-mysql.py", line 35, in __init__
    self.connection = MySQLdb.connect (host = self.mysql_hostname, port = self.mysqlPort, user = mysql_username, passwd = mysql_password)
  File "/var/lib/python-support/python2.5/MySQLdb/__init__.py", line 74, in Connect
    return Connection(*args, **kwargs)
  File "/var/lib/python-support/python2.5/MySQLdb/connections.py", line 170, in __init__
    super(Connection, self).__init__(*args, **kwargs2)
_mysql_exceptions.OperationalError: (1045, "Access denied for user 'gmetric'@'localhost' (using password: YES)")
Exception exceptions.AttributeError: "GmetricMySQL instance has no attribute 'connection'" in <bound method GmetricMySQL.__del__ of <__main__.GmetricMySQL instance at 0x7fba24240368>> ignored

mysql> GRANT USAGE ON *.* to 'gmetric'@'localhost' IDENTIFIED BY '[redacted]';
