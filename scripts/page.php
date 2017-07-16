<?php 
	include "conf.php";
    $link = mysql_connect($search_ip, $search_user, $search_passwd) or die('Could not connect:'.mysql_error());
    mysql_select_db($g_dbname) or die('Could not select database');
    mysql_query("SET NAMES UTF8");

    $link_dup = mysql_connect($search_ip, $search_user, $search_passwd) or die('Could not connect:'.mysql_error());
    mysql_select_db($g_dbname) or die('Could not select database');
    mysql_query("SET NAMES UTF8");

function do_shortcode_tag_keep_escaped_tags($m){
   // var_dump($m);
    $a=htmlspecialchars($m[5],ENT_QUOTES);
    //return sprintf("[%s %s]%s[/%s]",$m[2],$m[3],$a,$m[2]);
    return  sprintf('<pre name="code" class="brush: java;">%s</pre>',$a);
}

function get_nearly_articles($order, $start,$count, &$near_count, &$max_id, &$append_out, $post_date)
{
		global $g_dbname;
		$ret = 0;
		$append_out_array=array();
		//and UNIX_TIMESTAMP( post_date )  >  UNIX_TIMESTAMP( now() ) - 2592000*12 
        $post_date_str = "post_date";
        if ( $post_date != 1 )
        {
			$post_date_str = "post_modified";
        }
		$sql = "SELECT id,post_title as title,post_date,post_modified, term.name as term,t.taxonomy as type,r.term_taxonomy_id as tax FROM {$g_dbname}.blog_posts as p LEFT JOIN {$g_dbname}.blog_term_relationships as r ON p.id=r.object_id LEFT JOIN {$g_dbname}.blog_term_taxonomy t ON r.term_taxonomy_id=t.term_taxonomy_id LEFT JOIN {$g_dbname}.blog_terms term ON t.term_id=term.term_id WHERE id >= {$start} AND p.post_status='publish' and p.post_type='post' ORDER BY {$post_date_str} {$order} " ;
		//echo $sql;
		$r = mysql_query($sql);
        if (!$r){
            echo "执行: ".$sql." 出错:".mysql_error();
			$ret = -1;
        }
		//$append_out = "<br/><br/> 最新文章:<br/>";
		$filterout_repeat=array();
        $near_count = 0;
		$max_id=0;
        while( $row = mysql_fetch_array($r, MYSQL_ASSOC) ) {
            $out_date = $row[ $post_date_str ];
            $out=sprintf("<a href='/p/{$row["id"]}.html' >{$row['title']}_{$row['term']}</a> {$out_date} </br>");
			if ($near_count < $count && !in_array($row["title"], $filterout_repeat) )
			{
				$filterout_repeat[] = $row["title"];
            	//$append_out = $append_out.$out;
				$append_out_array[] = $out;
				$near_count = $near_count + 1;
				if ($row["id"] > $max_id)
				{
					$max_id = $row["id"];
				}
			}

        }

		if ($near_count == 0)
		{
			$max_id = 0;
		}
		else
		{
			if ($order == "asc")
			{
				$append_out_array = array_reverse($append_out_array);
			}
			foreach($append_out_array as $out)
			{
				$append_out = $append_out.$out;
			}
		}
		return $ret;
}

function history_articles($mindex,$maxid,&$arch)
{
	//现在最多100页
    for ($i=0; $i<$mindex; ++$i)
	{
    	$arch[] = "/p/arch{$i}.html";
	}
	global $BASE_PATH,  $public_path, $script_path, $install_path;
	$max_id=$maxid;
	$start=$max_id;
	$index=$mindex;
	for ($i=0; $i<100; $i++)
	{
    	$smarty = new Smarty(); //建立smarty实例对象$smarty 
        $smarty->template_dir = "{$install_path}/{$script_path}/"; //设置模板目录 
        $smarty->compile_dir = $BASE_PATH."templates_c"; //设置编译目录 
        $smarty->cache_dir = $BASE_PATH."cache"; //缓存目录 
        $smarty->cache_lifetime = 600; //缓存时间 
        $smarty->config_dir = $BASE_PATH."configs";
        $smarty->caching = false; //缓存方式 
    
        $smarty->left_delimiter = "{%"; 
        $smarty->right_delimiter = "%}"; 
        $smarty->assign("SiteName", $title); //进行模板变量替换 
        $smarty->assign("Title", "历史文章"); //进行模板变量替换 
		$smarty->assign("PageId", "arch{$index}");
    	$append_out = ""; //"历史文章:<br/>";
    	$content = "";
		$history_batch=100;
		$near_count=0;
		if ($max_id > $start)
		{
			$start = $max_id + 1;
		}
		$post_date = 1;
    	$ret = get_nearly_articles("asc",$start, $history_batch,$near_count,$max_id,$append_out, $post_date);
		echo " create history i:{$i} start:{$start} near_count: {$near_count} max_id: {$max_id}\n";
		if ($ret < 0)
		{
			return $ret;	
		}
		if ($max_id == 0)
		{
			return 0;
		}
    	$content = $content.$append_out;
        $smarty->assign("Content", $content);
        $smarty->assign("PublishDate",$post_date);
        $smarty->assign("ModifyDate",$post_modified);
        $out = $smarty->fetch("page.tpl");

		echo "{$start}_{$max_id}\n";
		$filename = "{$install_path}/{$public_path}/p/arch{$index}.html";
		$arch[] = "/p/arch{$index}.html";
		$index = $index + 1;
		echo $filename."\n";
        $handle = fopen($filename, "w");
        if ($handle){
            fwrite($handle,$out);
            fclose($handle);
        }
	}
}

ini_set('date.timezone','Asia/Shanghai');
error_reporting(7);

global $BASE_PATH, $SMARTY_PATH;
global $site_name, $site_desc;
#echo $BASE_PATH.$SMARTY_PATH."libs/Smarty.class.php";
#exit(0);
include_once( $BASE_PATH.$SMARTY_PATH."libs/Smarty.class.php"); //包含smarty类文件 


$params = getopt("p:r:");

$sql = "SELECT id,post_name,post_title as title ,post_content as content ,post_date,guid,post_type,post_status,post_modified, post_excerpt  FROM {$g_dbname}.blog_posts as p  WHERE  post_status='publish' and post_type='post' ";

$pageid=0;
if($params['p']){
        if(!is_numeric($params['p'])){
                exit ('wrong id');
        }
        $pageid = intval($params['p']);
        $sql = $sql." AND id={$pageid}";
}

$r = mysql_query($sql);
if (!$r){
    echo "执行: ".$sql." 出错:".mysql_error();
    exit(0);
}
//var_dump($r);

while( $result = mysql_fetch_array($r, MYSQL_ASSOC) ) {
    $title = $result['title'];
    $content = $result['content'];
    $content = preg_replace("/(?<!>)\\r\\n/","<br />",$content);
    $pattern ='\[(\[?)(sourcecode|source|code|as3|actionscript3|bash|shell|coldfusion|cf|clojure|clj|cpp|c| i|pas|pascal|diff|patch|erl|erlang|fsharp|groovy|java|jfx|javafx|js|jscript|javascript|tex|matlab|objc|obj t|ps|powershell|py|python|splus|rails|rb|ror|ruby|scala|sql|vb|vbnet|xml|xhtml|xslt|html)(?![\w-])([^\]\/] ?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
    $content=preg_replace_callback('/'.$pattern.'/s', 'do_shortcode_tag_keep_escaped_tags', $content);
    $post_modified = "最后更新日期:".$result['post_modified'];
    $post_date = "日期:".$result['post_date'];
    $id = $result['id'];
    $pageid = $id;

	$seo = $result['post_excerpt'];
    $smarty = new Smarty(); //建立smarty实例对象$smarty 
    $smarty->template_dir = "{$install_path}/{$script_path}/"; //设置模板目录 

    $smarty->compile_dir = $BASE_PATH."templates_c"; //设置编译目录 
    $smarty->cache_dir = $BASE_PATH."cache"; //缓存目录 
    $smarty->cache_lifetime = 600; //缓存时间 
    $smarty->config_dir = $BASE_PATH."configs";
    $smarty->caching = false; //缓存方式 

    $smarty->left_delimiter = "{%"; 
    $smarty->right_delimiter = "%}"; 
    $smarty->assign("SiteName", $title); //进行模板变量替换 
    $smarty->assign("Title", $title); //进行模板变量替换 
	$smarty->assign("SEO", $seo);
	$smarty->assign("Site_name", $site_name);
	$smarty->assign("Site_desc",$site_desc);

	$near_count=0;
	if ( $pageid == $index_page_id )
	{
		$append_out = "<br/><br/> 最新文章:<br/>";
		$near_count=0;
		$max_id=0;
		//最近文章或者最近修改过文章
        $post_date=1;
		$ret = get_nearly_articles("desc",0,6,$near_count, $max_id,$append_out, $post_date);
		if ($ret<0)
		{
			exit(0);
		}
		$content = $content.$append_out;
		$append_out = "<br/> 最近修改文章:<br/>";
        $post_date=0;
		$ret = get_nearly_articles("desc",0,6,$near_count, $max_id,$append_out, $post_date);
		if ($ret<0)
		{
			exit(0);
		}
		$content = $content.$append_out;
		$append_out = "<br/>历史归档:<br/>";
		$arch_array = array();
		history_articles(2,1064,$arch_array);
//		history_articles(0,0,$arch_array);

		//var_dump($arch_array);
		$arch_array =	array_reverse($arch_array);
		//var_dump($arch_array);
		foreach ($arch_array as $arch_url)
		{
			$append_out = $append_out."<a href={$arch_url} target='_blank'>{$arch_url} </a><br/> ";
		}
		$content = $content.$append_out.'<br/><a href="/p/879.html">关于</a>';
	}

    $smarty->assign("Content", $content);
    $smarty->assign("PublishDate",$post_date);
    $smarty->assign("ModifyDate",$post_modified);
	$smarty->assign("PageId", $id);
    $smarty->assign("Url","cosx.me/p/{$id}.html");
	$smarty->assign("Site_name", $site_name);
	$smarty->assign("Site_desc",$site_desc);
    //$smarty->display("page.tpl");
	//检查是否需要插入支持latex的mathjax插件js，因为mathjax加载js较大
    //这里判断当文章在math_latex标签下时，则需要插入
    $sql = "SELECT id,post_title as title,term.name,t.taxonomy as type,r.term_taxonomy_id as tax  FROM blog_posts as p LEFT JOIN blog_term_relationships as r     ON p.id=r.object_id LEFT JOIN blog_term_taxonomy t ON r.term_taxonomy_id=t.term_taxonomy_id LEFT JOIN blog_terms term ON  t.term_id=term.term_id  WHERE p.post_status='publish' AND p.post_type='post' AND p.id = {$id} AND term.name ='math_latex' ";
	$r_dup = mysql_query($sql);
	if (!$r_dup){
   		echo "执行: ".$sql." 出错:".mysql_error();
	    exit(0);
	}
	while( $result_tmp = mysql_fetch_array($r_dup, MYSQL_ASSOC) ) {
		if ( count($result_tmp) )
		{
			$smarty->assign("HeadAppend", '<script type="text/x-mathjax-config">
  MathJax.Hub.Config({
  jax: ["input/TeX","output/HTML-CSS", "output/PreviewHTML"],
  extensions: ["tex2jax.js","MathMenu.js","MathZoom.js", "fast-preview.js", "AssistiveMML.js"],
  TeX: {
    extensions: ["AMSmath.js","AMSsymbols.js","noErrors.js","noUndefined.js"]
  }
});

</script> ');
			$smarty->assign("JSAppend", '<script type="text/javascript" async
  src="/Public/Document/js/MathJax/MathJax.js?config=TeX-AMS_CHTML">
</script>');
		}	
	} 

	$template_name = "page.tpl";
	if ( $pageid == $index_page_id )
	{
		$template_name = "index.tpl";
	}
    $out = $smarty->fetch($template_name);
    $filename = "{$install_path}/{$public_path}/p/{$id}.html";
    $handle = fopen($filename, "w+");
    if ($handle){
        fwrite($handle,$out);
        fclose($handle);
    } else {
        exit(1);
    }
    if ($pageid== $index_page_id ){
        $filename = "{$install_path}/{$public_path}/wiki.html";
        $handle = fopen($filename, "w");
        if ($handle){
            fwrite($handle,$out);
            fclose($handle);
        } else {
            exit(1);
        }
    }
    echo $filename."\n";
    exec("cd ../{$public_path}/p; chown apache -R ./");
}


?> 
