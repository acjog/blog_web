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

    function topN($d_s, $d_e)
    {
			 $sql_f = "SELECT pageid, count(*) as num FROM %s.blogstat WHERE viewtime > '%s' AND viewtime <= '%s' GROUP BY pageid order by num desc limit 10";
			//echo $sql_f;exit(0);
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
				$tr_s = sprintf('<tr><td> <a href="/p/%s.html">%s</a> </td> <td>%s</td> <td>%s</td></tr>', $row["pageid"], $row["pageid"], $title, $row["num"]);
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
		.table-c table{border-right:1px solid #F00;border-bottom:1px solid #F00}
		.table-c table td{border-left:1px solid #F00;border-top:1px solid #F00}
	</style>
   </head>
   <body>
   <center>
<?php
	echo "今日访问量前十:</br>";
    $d = strtotime("yesterday");
    $d_s = date("Y-m-d 00:00:00", $d); 
    $d_e = date("Y-m-d 00:00:00"); 

    topN($d_s, $d_e);
	echo "一周访问量前十:</br>";
    $d = strtotime("last week");
    $d_s = date("Y-m-d 00:00:00", $d); 
    $d_e = date("Y-m-d 00:00:00"); 
    topN($d_s, $d_e);
	echo "一月访问量前十:</br>";
    $d = strtotime("-1 month");
    $d_s = date("Y-m-d 00:00:00", $d); 
    $d_e = date("Y-m-d 00:00:00"); 
    topN($d_s, $d_e);
	echo "三月访问量前十:</br>";
    $d = strtotime("-3 month");
    $d_s = date("Y-m-d 00:00:00", $d); 
    $d_e = date("Y-m-d 00:00:00"); 
    topN($d_s, $d_e);
	echo "历史访问量前十:</br>";
    $d = strtotime("-300 month");
    $d_s = date("Y-m-d 00:00:00", $d); 
    $d_e = date("Y-m-d 00:00:00"); 
    topN($d_s, $d_e);
?>
   </center>
   </body>
   </html> 

<?php
    mysql_close($link);    
?>
