<?php
    set_time_limit(0);
    $link = mysql_connect('127.0.0.1','greatroad','fdncjkfdhh,.-=fdfjdkfd') or die('Could not connect:'.mysql_error());
    mysql_select_db('zjwdb_110668') or die('Could not select database');
    mysql_query("SET NAMES UTF8");

    $g_dbname='zjwdb_110668';

    $uri= $_SERVER['REQUEST_URI'];
    $uri_a=split('/',$uri);
    $html=$uri_a[4];
    $uri_a=split('\.',$html);
    $articleid=intval($uri_a[0]);
 //   echo $articleid;
    if ($articleid){
        $sql=sprintf("SELECT id,post_title as title,term.name,t.taxonomy as type,r.term_taxonomy_id as tax  FROM zjwdb_110668.blog_posts as p LEFT JOIN zjwdb_110668.blog_term_relationships as r ON p.id=r.object_id LEFT JOIN zjwdb_110668.blog_term_taxonomy t ON r.term_taxonomy_id=t.term_taxonomy_id LEFT JOIN zjwdb_110668.blog_terms term ON  t.term_id=term.term_id  WHERE p.post_status='publish'  and p.post_type='post' AND id=%d LIMIT 1",$articleid);
        $r = mysql_query($sql);
        if (!$r){
            echo "执行: ".$sql." 出错:".mysql_error();
            exit(0);
        }
        $row = mysql_fetch_array($r, MYSQL_ASSOC);
        if (!empty($row)){
            $url=sprintf("Location: http://www.thinks-bz.com/articles/%d.html",$row['id']);
            header('HTTP/1.1 301 Moved Permanently');//发出301头部
            header($url);
        } else {
            $url=sprintf("Location: http://www.thinks-bz.com/wiki.html");
            header('HTTP/1.1 301 Moved Permanently');//发出301头部
            header($url);
        }
    } else {
       echo '<script>window.location="http:\/\/www.thinks-bz.com/wiki.html";</script>';
    }
    mysql_close($link);    
?>
