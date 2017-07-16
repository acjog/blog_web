#!/usr/bin/python
import sys
import os

def escaped_str(my_str):
	_my_str = []
	for i in range(len(my_str)):
		if my_str[i] == '/':
			_my_str.append("\\")
			_my_str.append("/")
		else:
			_my_str.append( my_str[i] )
	return "".join(_my_str)



if __name__ == "__main__":
	#print escaped_str("/usr/hello/world/")
	if len(sys.argv) < 3:
		print "Usage: %s http_root passfile_path" % sys.argv[0]
		exit(-1)

	#create http server log and proxy dir
	os.system("mkdir -p logs")
	os.system("mkdir -p cache/proxy")	
	os.system("mkdir -p cache/tmp")
	
	#create core dir for blog
	os.system("mkdir -p  public_html/Public/Document/imgcdn/")
	os.system("mkdir -p  public_html/p")	
	os.system("mkdir -p  public_html/man")

	cmd = 	" sed 's/install_path/%s/' ./conf/nginx.conf.def > ./conf/nginx.conf.1 " % escaped_str( sys.argv[1] )
	#print cmd
	os.system(cmd)
	cmd = " sed 's/passfile_path/%s/' ./conf/nginx.conf.1 > ./conf/nginx.conf " % escaped_str( sys.argv[2] )
	#print cmd
	os.system(cmd)
	os.system("cd ./public_html; ln -s ../scripts/conf.php ./conf.php")
