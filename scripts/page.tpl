<!DOCTYPE html>
<html>
<head>
	<script>
		var d = new Date();
		var begin_time = d.getTime();
	</script>
    <title>{%$SiteName%}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="description" content="{%$SEO%}" /> 
    <!-- <meta name="description" content="个人知识管理工具"/> -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Public/Document/css/layout.css">
    <link rel="stylesheet" href="/Public/Document/css/style.css">
    <link type="text/css" rel="stylesheet" href="/Public/Document/css/styles/shCore.css"/> 
    <link type="text/css" rel="stylesheet" href="/Public/Document/css/styles/shThemeDefault.css"/> 
    <link rel="stylesheet" href="/Public/static/thinktree/skin/default/style.css"> 
	{%$HeadAppend%}
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
            <header>
                <h1>{%$Title%}</h1>
                <h5 id='post_date' style="float:right">{%$PublishDate%}</h5>
                <div style="float:right">&nbsp;&nbsp;</div>
                <h5 id='post_modified'style="float:right">{%$ModifyDate%}</h5>
                <div class="description"></div>
            </header>
            <!-- <section class="url">本文链接地址:&nbsp;&nbsp;{%$Url%}</section> -->
            <section class="content">
                {%$Content%}
            </section>
		<!-- 畅言 PC和WAP自适应版-->
		<div id="SOHUCS" sid= "{%$PageId%}" ></div> 
		<script type="text/javascript"> 
		(function(){ 
		var appid = 'cysUE9swy'; 
		var conf = 'prod_af8a42e481949af8b55ce177849227e1'; 
		var width = window.innerWidth || document.documentElement.clientWidth; 
		if (width < 960) { 
		window.document.write('<script id="changyan_mobile_js" charset="utf-8" type="text/javascript" src="https://cosx.me/proxy/changyan.sohu.com/upload/mobile/wap-js/changyan_mobile.js?client_id=' + appid + '&conf=' + conf + '"><\/script>'); } else { var loadJs=function(d,a){var c=document.getElementsByTagName("head")[0]||document.head||document.documentElement;var b=document.createElement("script");b.setAttribute("type","text/javascript");b.setAttribute("charset","UTF-8");b.setAttribute("src",d);if(typeof a==="function"){if(window.attachEvent){b.onreadystatechange=function(){var e=b.readyState;if(e==="loaded"||e==="complete"){b.onreadystatechange=null;a()}}}else{b.onload=a}}c.appendChild(b)};loadJs("https://cosx.me/proxy/changyan.sohu.com/upload/changyan.js",function(){window.changyan.api.config({appid:appid,conf:conf})}); } })(); </script> 
		<!-- 畅言 PC和WAP自适应版 end-->
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
        <p class="copyright">&copy;2013-2017 网站:{%$Site_name%}<a href="http://www.miitbeian.gov.cn/" target="_blank">粤ICP备13068535号-2</a> 友情链接:<a href="http://www.klion26.com/" target="_blank" >klion26</a> <i id = "load"></i> <script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_5097651'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "v1.cnzz.com/stat.php%3Fid%3D5097651%26show%3Dpic' type='text/javascript'%3E%3C/script%3E"));</script>  </p>
     </footer>
	{%$JSAppend%}
	<script>
		var dd = new Date();
		var end_time = dd.getTime();
		var numobj = new Number( end_time - begin_time);
		document.getElementById("load").innerHTML  = "加载:" + numobj.toString() + " ms";

	</script>
</body>
</html>
