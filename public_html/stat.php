<?php 
    set_time_limit(0);
    include "conf.php";
    $link = mysql_connect($man_ip, $man_user, $man_passwd) or die('Could not connect:'.mysql_error());
    mysql_select_db($g_dbname) or die('Could not select database');
    mysql_query("SET NAMES UTF8");
    header("Content-type: text/html; charset=utf-8"); 

    function getTitle($pageid)
    {
       global $g_dbname, $link;
       $sql=sprintf("SELECT post_title FROM %s.blog_posts  WHERE id=%s ",$g_dbname,$pageid );
       $r = mysql_query($sql, $link);
       if (!$r){
           echo "执行: ".$sql." 出错:".mysql_error();
           exit(0);
       }
       $title_s = "N";
       while( $row = mysql_fetch_array($r, MYSQL_ASSOC) ) {
            $title_s = $row["post_title"];
       } 
       return $title_s;
    }

    function ViewDetail($id, $d_s, $d_e, $filter)
    {
             global $g_dbname;
             $search_str="";
             if ( $filter )
             {
                 $search_str=" AND searchengine is null ";
             }
             else
             {
                 $search_str=" AND searchengine is not null ";
             }
             $sql = "SELECT pageid,viewtime, viewagent,viewip,viewstatus FROM {$g_dbname}.blogstat WHERE viewtime > '{$d_s}' AND viewtime <= '{$d_e}' AND pageid = {$id}  {$search_str} order by viewip desc ";
            //$sql=sprintf($sql_f,$g_dbname, $d_s, $d_e);
            $r = mysql_query($sql);
            if (!$r){
                   echo "执行: ".$sql." 出错:".mysql_error();
                   exit(0);
            }
            $title = getTitle($id);
            echo  "{$title}";
            echo '<div  class="table-c"> <table border=0 width="800px">';
            while( $row = mysql_fetch_array($r, MYSQL_ASSOC) ) {
                $tr_s = "<tr><td> <a href=\"/p/{$id}.html\">{$id}</a> </td> <td>{$row['viewip']}</td> <td>{$row['viewagent']}</td><td>{$row['viewtime']}</td><td>{$row['viewstatus']}</td></tr>";
                echo $tr_s;
            } 
           echo "</table> </div> </br></br>";     

    }

    function topN($d_s, $d_e, $N, $filter)
    {
             if ( $filter )
             {
                 $search_str=" AND searchengine is null ";
             }
             else
             {
                 $search_str=" AND searchengine is not null ";
             }
             $sql_f = "SELECT pageid, count(*) as num FROM %s.blogstat WHERE viewtime > '%s' AND viewtime <= '%s' {$search_str}  GROUP BY pageid order by num desc limit {$N}";
            $sql=sprintf($sql_f,$g_dbname, $d_s, $d_e);
            //echo $sql;exit(0);
            $r = mysql_query($sql);
            if (!$r){
                   echo "执行: ".$sql." 出错:".mysql_error();
                   exit(0);
            }
            echo '<div  class="table-c"> <table border=0 width="600px">';
            while( $row = mysql_fetch_array($r, MYSQL_ASSOC) ) {
                $title = getTitle($row["pageid"]);
                $tr_s = sprintf('<tr><td> <a href="/p/%s.html">%s</a> </td> <td>%s</td> <td> <a href="/stat.php?pageid=%s&filter=%d">%s</a></td></tr>', $row["pageid"], $row["pageid"], $title, $row["pageid"],$filter, $row["num"]);
                echo $tr_s;
            } 
           echo "</table> </div> </br></br>";     
    }
?>
   <html>
   <head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
   <title>后台统计</title>
    <style>
        .table-c table{border-right:1px solid #000;border-bottom:1px solid #000}
        .table-c table td{border-left:1px solid #000;border-top:1px solid #000}
    </style>
   </head>
   <body>
   <center>
<?php
    if ( isset($_GET['pageid']) )
    {

        $pageid = intval($_GET['pageid']);

    }
    if ( isset($_GET['period']) )
    {
        $period = intval($_GET['period']);
    }
    $filter = 0;
    if ( isset($_GET['filter']) )
    {
        $filter = intval( $_GET['filter'] );
    }
    echo '<a href="/stat.php?filter=1">过滤搜索引擎</a><br/>';
    echo '<a href="/stat.php?filter=0">搜索引擎访问</a><br/>';
    if ($pageid != 0)
    {
        echo "详细数据 - {$pageid} <br/>";
        $d = strtotime("yesterday");
        $d_s = date("Y-m-d 00:00:00", $d); 
        $d_e = date("Y-m-d 00:00:00"); 
        ViewDetail($pageid,$d_s, $d_e, $filter);
    }
    else
    {
            echo "今日",date("Y-m-d"),"报表</br>";
            echo "昨日访问量:</br>";
            $d = strtotime("yesterday");
            $d_s = date("Y-m-d 00:00:00", $d); 
            $d_e = date("Y-m-d 00:00:00"); 

            topN($d_s, $d_e, 1000, $filter);
            echo "一周访问量前十:</br>";
            $d = strtotime("last week");
            $d_s = date("Y-m-d 00:00:00", $d); 
            $d_e = date("Y-m-d 00:00:00"); 
            topN($d_s, $d_e, 10, $filter);
            echo "一月访问量前十:</br>";
            $d = strtotime("-1 month");
            $d_s = date("Y-m-d 00:00:00", $d); 
            $d_e = date("Y-m-d 00:00:00"); 
            topN($d_s, $d_e, 10, $filter);
            echo "三月访问量前十:</br>";
            $d = strtotime("-3 month");
            $d_s = date("Y-m-d 00:00:00", $d); 
            $d_e = date("Y-m-d 00:00:00"); 
            topN($d_s, $d_e, 10, $filter);
            echo "历史访问量前十:</br>";
            $d = strtotime("-300 month");
            $d_s = date("Y-m-d 00:00:00", $d); 
            $d_e = date("Y-m-d 00:00:00"); 
            topN($d_s, $d_e, 10, $filter);
    }
?>
   </center>
   </body>
   </html> 

<?php
    mysql_close($link);    
?>
