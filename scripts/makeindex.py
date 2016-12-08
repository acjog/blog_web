#!/usr/bin/python
#encoding=utf-8
import jieba
import MySQLdb
import traceback
import json
import codecs
import sys
import getopt
import math
import os
import conf
from conf import * 

remote_db=MySQLdb.connect(host=remote_movie_host,user=remote_movie_user,passwd=remote_movie_passwd,db=remote_movie_dbname,port=remote_movie_port)
remote_cur=remote_db.cursor()
sql = "set names utf8"
remote_cur.execute(sql)

reload(sys)
sys.setdefaultencoding('utf8')

def filterWords():
    words_map={'\r':1,'\r\n':1,'\n':1,'\t':1," ":1,"  ":1,"   ":1,"    ":1,"     ":1, \
     "      ":1,"       ":1,"        ":1}
    fp=codecs.open("%s/%s" % (os.path.dirname(sys.argv[0]),"filter.txt"),"r","utf-8")
    if not fp:
        print "log_file could not open,please check\n"
        exit(0)
    for line in fp:
        line="".join(line.split())
        li=line.split("、")
        for i in range(len(li)):
            word=li[i].encode('utf-8')
            if words_map.has_key(word):
                continue
            tmp={word:1}
            words_map.update(tmp)
    return words_map

filter_words={}
def cutWords(str):
    word_map={}
    global filter_words

    it = jieba.cut_for_search(str) #搜索引擎模式
    try:
        while True:
            val = it.next()
            val = val.encode('utf-8')
            #print "=>",type(val)
            if filter_words.has_key(val):
                #print "pass:", val
                continue
            if word_map.has_key(val):
                tmp={val:(word_map[val]+1)}
                word_map.update(tmp)
            else:
                tmp={val:1}
                word_map.update(tmp)
    except StopIteration:
        pass
    return word_map

g_words_map={}

def print_map(m):
    for key in m.keys():
        print key,m[key]


def map_intValue_total(m):
    total_num=0
    if len(m)>0:
        for key in m.keys():
            total_num = total_num+m[key]
    return total_num;

def impl_create_page_index(id,title_map,total_words,istitle):
    tblword="zjwdb_110668.v_word"
    tblindex="zjwdb_110668.v_index"
    global remote_db
    remote_cur=remote_db.cursor()
 
    if len(title_map)>0:
        #遍历关键字数组,更新关键字相关页面数
        for key in title_map.keys():
            sql = "insert into %s(content,relatePageNum) values('%s',1)  ON DUPLICATE KEY UPDATE  relatePageNum=relatePageNum+1" % (tblword,key)
            print sql
            remote_cur.execute(sql)
            sql="SELECT id FROM %s WHERE content='%s' LIMIT 1" % (tblword,key)
            remote_cur.execute(sql)
            row=remote_cur.fetchone()
            keyid=row[0]
            if keyid>0:
                #计算词频,标题和正文的词频分开计算，标题词频乘以10化归统一
                TF=title_map[key]/float(total_words)
                if istitle>0:
                    TF = 10*TF
                    sql = "INSERT INTO %s(wordId,pageId,TF,istitle) VALUE(%d,%d,%f,1) ON DUPLICATE KEY UPDATE istitle=1,TF=%f" % (tblindex,keyid,id,TF,TF)
                else:
                    sql = "INSERT INTO %s(wordId,pageId,TF,istitle) VALUE(%d,%d,%f,0) ON DUPLICATE KEY UPDATE TF=TF+%f" % (tblindex,keyid,id,TF,TF)
#                print sql
                remote_cur.execute(sql)


def clear_page_index(id):
    global remote_db
    remote_cur=remote_db.cursor()
    
    if 0==id:
        return
    
    tbl_index = "zjwdb_110668.v_index"
    tbl_word = "zjwdb_110668.v_word"
    sql = "SELECT wordId FROM %s WHERE pageId=%d " % (tbl_index,id)
    remote_cur.execute(sql)
    wordid_li=[]
    while True:
        row = remote_cur.fetchone()
        if not row:
            break
        wordid_li.append(row[0])

    #减去相关页面
    for wordid in wordid_li:
        sql = "UPDATE %s SET relatePageNum=relatePageNum-1 WHERE id=%s" % (tbl_word,wordid)
        remote_cur.execute(sql)
        
    #清掉当前文章的关键字
    sql = "DELETE FROM %s WHERE pageId=%d" % (tbl_index,id)
    remote_cur.execute(sql)

def create_page_index(id):
    global remote_db
    remote_cur=remote_db.cursor()
    if 0==id:
        return

    sql = "SELECT id,post_name,post_title as title ,post_content as content ,UNIX_TIMESTAMP(post_date) as post_date,guid,post_type,post_status,UNIX_TIMESTAMP(post_modified) as post_modified  FROM zjwdb_110668.blog_posts as p  WHERE  post_status='publish' and post_type='post' AND id=%d " % id
    key_dict=['id','post_name','title','content','post_date','guid','post_type','post_status','post_modified']
    remote_cur.execute(sql)
    
    results=remote_cur.fetchall()
    for row in results:
        title=row[2]
        content=row[3]
        #切割标题和正文
        title_map=cutWords(title)
        content_map=cutWords(content)
        total_words=map_intValue_total(title_map)
        impl_create_page_index(id,title_map,total_words,1)
        total_words=map_intValue_total(content_map)
        impl_create_page_index(id,content_map,total_words,0)


def cal_word_weight():
    tblword="zjwdb_110668.v_word"
    tbloptions="zjwdb_110668.v_options"
    global remote_db
    remote_cur=remote_db.cursor()
    #计算出所有发布页面
    sql = "SELECT count(distinct id) FROM zjwdb_110668.blog_posts as p  WHERE  post_status='publish' and post_type='post' "
    remote_cur.execute(sql)
    row=remote_cur.fetchone()
    total_num=row[0]
    sql="INSERT INTO %s(mkey,value,type) VALUE('%s',%d,'int') ON DUPLICATE KEY UPDATE value=%d,type='int'" % ( tbloptions,'totalpages',total_num,total_num)
#    print sql
    remote_cur.execute(sql)
    
    sql="SELECT id,relatePageNum FROM %s " % tblword
    #遍历计算每个词的IDF,作为每个词的重要性依据
    remote_cur.execute(sql)
    results=remote_cur.fetchall()
    for row in results:
        id=row[0]
        relate=float(row[1])
        sql = "UPDATE %s SET IDF=%f WHERE id=%d" % (tblword,math.log(total_num/(relate+1)),id)
#        print sql
        remote_cur.execute(sql)


def create_index(id):
    tbloptions="zjwdb_110668.v_options"
    global remote_db
    remote_cur=remote_db.cursor()
    sql = "SELECT distinct id FROM zjwdb_110668.blog_posts as p  WHERE  post_status='publish' and post_type='post' "
    if id :
        sql += " AND id=%d" % id
        clear_page_index(id)

    remote_cur.execute(sql)
    results=remote_cur.fetchall()

    for row in results:
        create_page_index(row[0])

    sql = "SELECT count(distinct id) FROM zjwdb_110668.blog_posts as p  WHERE  post_status='publish' and post_type='post' "
    remote_cur.execute(sql)
    row=remote_cur.fetchone()
    total_num=row[0]
    sql="INSERT INTO %s(mkey,value,type) VALUE('%s',%d,'int') ON DUPLICATE KEY UPDATE value=%d,type='int'" % ( tbloptions,'totalpages',total_num,total_num)
    remote_cur.execute(sql)

def _imp_flush(tblname):
    global remote_db
    remote_cur=remote_db.cursor()
    tbl = "zjwdb_110668.%s" % tblname
    sql = "flush table %s " % tbl
    remote_cur.execute(sql)

def flush_search_tables():
    _imp_flush("v_index")
    _imp_flush("v_word")

def test():
    str="python mysql操作"
    seg_list = jieba.cut(str,cut_all=True)
    print "Full Mode:", "/ ".join(seg_list) #全模式

    seg_list = jieba.cut(str,cut_all=False)
    print "Default Mode:", "/ ".join(seg_list) #精确模式

    seg_list = jieba.cut(str) #默认是精确模式
    print ", ".join(seg_list)

    seg_list = jieba.cut_for_search(str) #搜索引擎模式
    print ", ".join(seg_list)

def query(str):
    li=[]
    it = jieba.cut_for_search(str) #搜索引擎模式
    try:
        while True:
            val = it.next()
            val = val.encode('utf-8')
            if val!=' ':
                li.append(val);
    except StopIteration:
        pass
    print ", ".join(li)



if __name__=='__main__':
    try:
        options,args = getopt.getopt(sys.argv[1:],"rhcip:q:",["rebuild","help","create","idf","page=","query="])
        #print options
    except getopt.GetoptError:
        print "get wrong"
        sys.exit()
    for name,value in options:
        if name in ('-h','-help'):
            print "%s -[help|create(c)|idf(i)|page(p)|query(q)]" % sys.argv[0]
            sys.exit(0)
        if name in ('-p','-page'):
            id=int(value)
            if id>0:
                filter_words=filterWords()
                create_index(id)
                cal_word_weight()
                print "index:%d create index and cal weight success ...\n" % id
            sys.exit(0)
        if name in ('-create','-c'):
            filter_words=filterWords()
            create_index(0)
            print "create all index success...\n" % id
            sys.exit(0)
        if name in ('-query','-q'):
            query(value)
            sys.exit(0)
        if name in ('-i','-idf'):
            cal_word_weight()
            sys.exit(0)
        if name in ('-r','-rebuild'):
            #每次文章修改，所有可以定时全部重新建立索引
            flush_search_tables();
            filter_words=filterWords()
            create_index(0);
            cal_word_weight();
            sys.exit(0)
    print "%s -[help|create(c)|idf(i)|page(p)|query(q)]" % sys.argv[0]
    sys.exit(0)
