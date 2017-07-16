blog_web
将博客后台代码，提交git，便于维护和更新
public_html 主要为nginx http目录
scripts  脚本目录，主要静态化和建立搜索索引
需要安装软件:
jieba分词 python工具包
smarty php模板，静态化
mysql 文章存储数据库及分词表
测试https提交
安装步骤:
1. 解压后的conf目录，使用openssl命令创建ssl证书.　这里ssl证书也可以在https://www.sslforfree.com/申请免费的浏览器认同的证书.
2. 在解压后的conf目录，创建后台密码文件，一般不要用带有password相关的文件名
	htpasswd -c pass_file.txt test
3. 安装一个nginx, python, php-fpm(支持mysql的版本)
4. 安装python库jieba分词库
5. 解压后的根目录，执行python脚本．　python ./install.py 解压目录绝对地址　解压目录绝对地址/conf.　生成nginx的配置文件
6. 登录mysql，创建test用户访问test数据库，　第一个test为数据库，第二个test为用户名.
	grant all on test.* to test@localhost identified by "明文密码";
	use test
	source ./conf/db.sql
7. 打开首页，是否正确显示．然后点击记笔记，写日志．

注意事项:
1. php运行用户是否存在
2. 若不需要搜索，则可以去掉jieba分词库
3. 若运行不了mysql，则可以不需要mysql启动，使用本地json存储．该项支持需要更改部分代码
