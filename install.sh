#create nginx dir
# http server log dir
mkdir -p logs
#proxy dir
mkdir -p cache/proxy
mkdir -p cache/tmp

#create dir for blog
mkdir -p  public_html/Public/Document/imgcdn/
mkdir -p  public_html/Public/p
mkdir -p  public_html/Public/man

#replace the install path of the nginx conf file
sed 's/install_path/\/usr\/local\/src\/\/blog_web/' ./conf/nginx.conf.def > ./conf/nginx.conf
