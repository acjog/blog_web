<?php 
    set_time_limit(0);
    include 'conf.php';
    $absolute_path = "{$install_path}/{$script_path}";
    $link = mysql_connect($search_ip, $search_user,$search_passwd) or die('Could not connect:'.mysql_error());
    mysql_select_db($g_dbname) or die('Could not select database');
    mysql_query("SET NAMES UTF8");

    function utf8_strlen($string = null) {
		// 将字符串分解为单元
		preg_match_all('/./u', $string, $match);
		// 返回单元个数
		return count($match[0]);
	}

    $words = trim($_POST['keywords']);
    if (!isset($words) || empty($words) ){
        print_r('请传入正确的关键字');
        exit(0);
    }
    $r=0;
    $out=array();
    $vowels = array("/", "|", "%", "&", "#", "(",")" );
    $words = str_replace($vowels, "", $words);	
    $words = escapeshellcmd($words);
    $cmd=sprintf("/usr/bin/python ${absolute_path}/makeindex.py -q'%s' 2>/dev/null",$words);
	//print_r($cmd);exit(0);
    exec($cmd,$out,$r);
    if ($r!=0){ 
        print_r("分词失败,错误码:{$r}");
		var_dump( $out );
        exit(0);
    }
    $tmp=split(', ',$out[0]);
    $words=array();
    foreach ( $tmp as $word ){
        $words[]="'".$word."'";
    
    }
    if (empty($words)){
       print_r("没有找到您要搜索的内容");
       exit(0);
    } 
    $sql=sprintf("SELECT id,IDF,content FROM %s WHERE content in (%s) ","{$g_dbname}.v_word", implode(',',$words));
    $r = mysql_query($sql);
    if (!$r){
        if ($debug_flag) {
            echo "执行: ".$sql." 出错:".mysql_error();
        }
		else
        {
            echo "执行SQL查询出错";
        }
        exit(0);
    }
    $ids=array();
	$id_wordlen = array();
    $word_idf=array();
    while( $row = mysql_fetch_array($r, MYSQL_ASSOC) ) {
           $ids[]=$row['id'];
           $word_idf[$row['id']]=$row['IDF'];
           $id_wordlen[ $row['id'] ] = utf8_strlen( $row['content'] );
    }
    if (empty($ids)){
       print_r("没有找到您要搜索的内容");
       exit(0);
    } 
    $sql=sprintf("SELECT wordId,pageId,TF,istitle FROM %s WHERE wordId in(%s)","{$g_dbname}.v_index",implode(',',$ids));
    $r = mysql_query($sql);
    if (!$r){
        if ($debug_flag) {
            echo "执行: ".$sql." 出错:".mysql_error();
        }
		else
        {
            echo "执行SQL查询出错";
        }
        exit(0);
    }
    $pageid=array();
    $page_maxmatch_word = array();
    while( $row = mysql_fetch_array($r, MYSQL_ASSOC) ) {
         $init_val=0;
         if ($row['istitle']){
             $init_val=10;
         }
         if (array_key_exists($row['pageId'],$page_maxmatch_word) )
         {
			if ( $id_wordlen[ $page_maxmatch_word[ $row['pageId'] ] ]< $id_wordlen[ $row['wordId'] ] )
			{
				$page_maxmatch_word[ $row['pageId'] ] = $row['wordId'];
			}
         }
		 else{
				$page_maxmatch_word[ $row['pageId'] ] = $row['wordId'];
		 }
         if (array_key_exists($row['pageId'],$pageid)){
                 $pageid[$row['pageId']] = $init_val +  $pageid[$row['pageId']] + $word_idf[$row['wordId']]*$row["TF"];
         } else {
                 $pageid[$row['pageId']] = $init_val + $word_idf[$row['wordId']]*$row["TF"];
         }
    }
    //匹配词的最大长度修正权重 , 比如搜索－正则表达式时，没有返还完全匹配的文章
	foreach ( $pageid as $k => $v )
	{
         $pageid[$k]  = $v + $id_wordlen[ $page_maxmatch_word[$k] ] /10.0;
	}
    arsort($pageid);
    //var_dump($pageid);
    $index=1;
    foreach ($pageid as $k=>$v){
        if ($index>10){
            break;
        }
        $index=$index+1;
        $sql="SELECT id,post_title as title,term.name as term,t.taxonomy as type,r.term_taxonomy_id as tax  FROM {$g_dbname}.blog_posts as p LEFT JOIN {$g_dbname}.blog_term_relationships as r ON p.id=r.object_id LEFT JOIN {$g_dbname}.blog_term_taxonomy t ON r.term_taxonomy_id=t.term_taxonomy_id LEFT JOIN {$g_dbname}.blog_terms term ON  t.term_id=term.term_id  WHERE p.post_status='publish'  and p.post_type='post'"." AND id={$k}"." LIMIT 1";
        $r = mysql_query($sql);
        if (!$r){
            if ($debug_flag) {
                echo "执行: ".$sql." 出错:".mysql_error();
            }
		    else
            {
                echo "执行SQL查询出错";
            }
            exit(0);
        }
        while( $row = mysql_fetch_array($r, MYSQL_ASSOC) ) {
            $out=sprintf("<a href='/p/{$k}.html#{$row["tax"]}_{$k}' target='_blank' >{$row['title']}_{$row['term']}</a></br>");
            echo $out;
        }
    }
?>
