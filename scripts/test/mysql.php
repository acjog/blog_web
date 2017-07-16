<?php
    include "conf.php";
    $link = mysql_connect($man_ip, $man_user, $man_passwd) or die('Could not connect:'.mysql_error());
    mysql_select_db($g_dbname, $link) or die('Could not select database');
    $sql = "SELECT VERSION()";
    $r = mysql_query($sql, $link);
    if (!$r){
        echo "执行: ".$sql." 出错:".mysql_error();
        exit(0);
    }
    while( $row = mysql_fetch_array($r, MYSQL_ASSOC) ) {
	var_dump( $row );
    } 
?>
