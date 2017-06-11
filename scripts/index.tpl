<!DOCTYPE html>
<html>
<head>
	<script>
		var d = new Date();
		var begin_time = d.getTime();
	</script>
    <title>{%$Site_name%}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="keywords" content="探索学习，知识管理" /> 
    <meta name="description" content="玄之弦，个人知识管理工具"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Public/Document/css/layout.css">
    <link rel="stylesheet" href="/Public/Document/css/style.css">
    <link type="text/css" rel="stylesheet" href="/Public/Document/css/styles/shCore.css"/> 
    <link type="text/css" rel="stylesheet" href="/Public/Document/css/styles/shThemeDefault.css"/> 
    <link rel="stylesheet" href="/Public/static/thinktree/skin/default/style.css"> 
</head>
<body>
    <header class="layout">
           <section class="header">
              <hgroup>
                 <h1><a href="https://cosx.me">{%$Site_name%}</a></h1>
                 <h2>{%$Site_desc%}</h2>
              </hgroup>
           </section>
    </header>
    <aside class="control" >
          <div>站内搜索:&nbsp; <input id="search" type="search" value="" placeholder="欢迎使用该功能" /></div>
			<div style="float:left">快捷方式:&nbsp; </div>
          <input type="button" id="fold" style="float:left" value="全部展开" />
		  <input type="button" id="new" style="float:left" value="写笔记" />
    </aside>
    <aside class="layout">
		  <div class="control">
			导航浏览:
          <select id="select">
              <option>目录</option>
              <option>标签</option>
          </select>
			<br/>
		</div>
        <nav class="thinktree-default">
        </nav>
    </aside>
    <section class="layout">
        <article class="book">
            <section class="content">
                {%$Content%}
            </section>
            <footer>
            </footer>
        </article>
     </section>
     <!-- http://apps.bdimg.com/libs/jquery/1.6.4/jquery.min.js -->
     <script type="text/javascript" src="/Public/Document/js/jquery.min.js"></script>
     <script type="text/javascript" src="/Public/Document/js/brush.js"></script>
     <script type="text/javascript" src="/Public/Document/js/document.js" ></script>
     <script type="text/javascript" src="/man/list.json.test"></script>
     <script>
         main();
     </script>

     <footer class="layout">
        <p class="copyright">&copy;2013-2017 网站:玄之弦 站长:玄之弦 <a href="http://www.miitbeian.gov.cn/" target="_blank">粤ICP备13068535号-2</a> 友情链接:<a href="http://www.klion26.com/" target="_blank" >klion26</a><i id = "load"></i> <script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_5097651'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "v1.cnzz.com/stat.php%3Fid%3D5097651%26show%3Dpic' type='text/javascript'%3E%3C/script%3E"));</script>  </p>
     </footer>

	<script>
		var dd = new Date();
		var end_time = dd.getTime();
		var numobj = new Number( end_time - begin_time);
		document.getElementById("load").innerHTML  = "加载:" + numobj.toString() + " ms";

	</script>
</body>
</html>
