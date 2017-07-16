#!/usr/bin/python
import  MySQLdb
import traceback
import json
import sys
import getopt
import conf
from conf import * 

remote_db=MySQLdb.connect(host=remote_movie_host,user=remote_movie_user,passwd=remote_movie_passwd,db=remote_movie_dbname,port=remote_movie_port)
remote_cur=remote_db.cursor()
sql = "select version()"
remote_cur.execute(sql)
results=remote_cur.fetchall()
for row in results:
	print row
 


