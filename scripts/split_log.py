#encoding:utf-8
import os
import sys
import MySQLdb
import re
import conf
from conf import *
from datetime import *
import time

remote_db=MySQLdb.connect(host=remote_movie_host,user=remote_movie_user,passwd=remote_movie_passwd,db=remote_movie_dbname,port=remote_movie_port)
remote_cur=remote_db.cursor()
sql = "set names utf8"
remote_cur.execute(sql)


'''
提取nginx日志统计分析
'''
if __name__ == '__main__':
   if len(sys.argv) < 3:
        print "Usage: %s log_path nginx_pidfile" % sys.argv[0]
        sys.exit(0)

     
   #backup logpath
   log_path = sys.argv[1]
   bak_file="%s.%f" % (log_path,time.time())
   os.system("mv %s %s" % (log_path, bak_file))
   
 
   nginx_pid_path = sys.argv[2]  
   fp = open(nginx_pid_path)
   for line in fp:
        line = line.split("\n")[0]
        nginx_pid = line
        os.system("kill -USR1 %s" % nginx_pid)

   #insert db
   re_id = re.compile("\"/p/([0-9]+).html\"$", re.U)
   fp = open(bak_file)
   for line in fp:
        line = line.strip()
        li = line.split(" - ")
        #print li[4]
        id_str=li[4].split("#")[0]
        #print id_str
        if id_str == "\"/\"":
           pageid = 1050;
        else:
           pageid_m = re_id.match(id_str)
           if not pageid_m:
               continue
           pageid = pageid_m.group(1)
        remote_cur=remote_db.cursor()
        t_str = datetime.strptime(li[0],"\"%d/%b/%Y:%H:%M:%S +0800\"").strftime("%Y-%m-%d %H:%M:%S")
        ip=li[1].strip().split("\"")[1]
        agent=li[2].strip().split("\"")[1]
        status_s=li[3].strip().split("\"")[1]
        #print pageid, t_str, agent , li[0]
        sql = "INSERT INTO blogstat set pageid=%s, viewtime='%s',viewip='%s', viewagent='%s', viewstatus=%s  " % (pageid, t_str, ip, agent,status_s)
        #print sql
        remote_cur.execute(sql)
 
