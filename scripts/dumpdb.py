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
sql = "set names utf8"
remote_cur.execute(sql)

def createjson(id,path):
    global remote_db
    remote_cur=remote_db.cursor()
    sql = "set names utf8"
    remote_cur.execute(sql)
    sql = "SELECT id,post_name,post_title as title ,post_content as content ,UNIX_TIMESTAMP(post_date) as post_date,guid,post_type,post_status,UNIX_TIMESTAMP(post_modified) as post_modified  FROM zjwdb_110668.blog_posts as p  WHERE  post_status='publish' and post_type='post' "
    if id :
        sql += " AND id=%d" % id
    #print sql
    remote_cur.execute(sql)
    results=remote_cur.fetchall()
    row_dict={}
    key_dict=['id','post_name','title','content','post_date','guid','post_type','post_status','post_modified']
    for row in results:
        for i in range(len(row)):
           tmp={key_dict[i]:row[i]}
           row_dict.update(tmp)
        dumpobj=json.dumps(row_dict)
        file = open('%s/man/%s.json' % (path,row[0]),'w')
        file.write(dumpobj)
        print 'dump man/%s.json' % row[0]
 
def createtaxonmy(path):
    global remote_db
    remote_cur=remote_db.cursor()
    sql = "SELECT count(*)  FROM zjwdb_110668.blog_posts as p  WHERE p.id != 1050 and p.post_status='publish' and p.post_type='post' "
    remote_cur.execute(sql)
    results=remote_cur.fetchall()
    taxonmy={}
    taxonomy_type={}
    for row in results:   
         print 'total:%d' % row[0]
    sql = "SELECT id,post_title as title,term.name,t.taxonomy as type,r.term_taxonomy_id as tax  FROM zjwdb_110668.blog_posts as p LEFT JOIN zjwdb_110668.blog_term_relationships as r ON p.id=r.object_id LEFT JOIN zjwdb_110668.blog_term_taxonomy t ON r.term_taxonomy_id=t.term_taxonomy_id LEFT JOIN zjwdb_110668.blog_terms term ON  t.term_id=term.term_id  WHERE p.id != 1050 and p.post_status='publish'  and p.post_type='post' order by term.name asc "   
    remote_cur.execute(sql)
    results=remote_cur.fetchall()
    for row in results:
        #print row[0],row[1],row[2]
        cat=row[4]
        tmp={'name':row[1],'url':'/p/%s.html'%row[0],'id':'%s_%s'%(cat,row[0])}
        if taxonmy.has_key(row[2]):
            dir_tmp=taxonmy[row[2]]
            #print dir_tmp
            dir_tmp.append(tmp)
            tmp={row[2]:dir_tmp}
            taxonmy.update(tmp)
        else:
            tmp_list=[tmp]
            dir_tmp1={row[2]:tmp_list}
            taxonmy.update(dir_tmp1)

        if taxonomy_type.has_key(row[2]):
            if taxonomy_type[row[2]]!=row[3]:
                print "please check"
                print row
                exit(0)
        else:
            tmp={row[2]:row[3]}
            taxonomy_type.update(tmp)

#   print taxonmy
    out=[]
    for (k, v) in taxonmy.items():
        tmp={'name':k,'data':v,'type':taxonomy_type[k]}
        out.append(tmp)
    tmp={'data':out}
    import codecs
    file = codecs.open('%s/man/list.json.test'%path, 'w' ,"utf-8")
    dumpobj=json.dumps(tmp)
    file.write('initTree(%s)' % dumpobj)
#    print tmp

def  getparent(obsolute_path):
    path=obsolute_path.split('/')
    parent_path='/'
    for i in range(len(path)-1):
        if path[i]!='':
            parent_path += path[i] + '/'
    return parent_path
    
if __name__=='__main__':
    path=getparent(sys.argv[0])+"/../public_html"
    if sys.argv[0][0]=='.':
        print "use obsolute_path"
        sys.exit(0)
    try:
        options,args = getopt.getopt(sys.argv[1:],"hd:p:",["help","directory=","page="])
        print options
    except getopt.GetoptError:
        print "get wrong"
        sys.exit()
    for name,value in options:
        if name in ('-h','-help'):
            print "%s -[help|dirtory(d)|page(p)]" % sys.argv[0]
            sys.exit(0)
        if name in ('-p','-page'):
            id=int(value);
            createjson(id,path)
            print 'create json success'
            sys.exit(0)
        if name in ('-directory','-d'):
            createtaxonmy(path)
            sys.exit(0)

    print "%s -[help|dirtory(d)|page(p)]" % sys.argv[0]
    sys.exit(0)
 
