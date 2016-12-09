<?php 
    set_time_limit(0);
	include "conf.php";
    $link = mysql_connect($man_ip, $man_user, $man_passwd) or die('Could not connect:'.mysql_error());
    mysql_select_db($g_dbname) or die('Could not select database');
    mysql_query("SET NAMES UTF8");

    function do_shortcode_tag_keep_escaped_tags($m){
      //$a=$m[5];
      $a=htmlspecialchars($m[5],ENT_QUOTES); //这里显示的字符，需要htmlspecialchars处理
      if ($m[2]=='code'){
          return sprintf("[%s %s]%s[/%s]",$m[2],$m[3],$a,$m[2]);
      } else {
          return $a;
      }
    }

    function  exepython($articleid)
    {
        global $install_path, $script_path, $public_html;
        $articleid=intval($articleid);
        $out=array();
        $r=0;
        $cmd = "cd {$install_path}/{$script_path}; /usr/bin/python {$install_path}/{$script_path}/dumpdb.py -d {$install_path}/{$public_html}";
        #echo $cmd;
        #exit(0);
        exec($cmd,$out,$r);
        if ($r!=0){
            print_r($r);
            print_r($out);
            echo "python dump directory出错";
            exit(0);
        }
        $r=0;
        unset($out);
        $out=array();
        exec("/usr/bin/python  {$install_path}/{$script_path}/dumpdb.py  -p {$articleid} "  ,$out,$r);
        if ($r!=0){
            print_r($r);
            print_r($out);
            echo "python dump article出错";
            exit(0);
        }
        $cmd="cd {$install_path}/{$script_path}; /usr/bin/php {$install_path}/{$script_path}/page.php -p {$articleid} ";
        $r=0;
        unset($out);
        $out=array();
        exec($cmd ,$out,$r);
        if ($r!=0){
            print_r($r);
            print_r($out);
            echo "php page.php article出错";
            exit(0);
        }
        $r=0;
        unset($out);
        $out=array();
        exec("/usr/bin/python {$install_path}/{$script_path}/makeindex.py  -p {$articleid} "  ,$out,$r);
        if ($r!=0){
            print_r($r);
            print_r($out);
            echo "python makeindex 出错";
            exit(0);
        }
        echo "r:{$r}<br/>";
        print_r($out);
        unset($out);
        //重新生成首页
        $out=array();
        exec("cd {$install_path}/{$script_path}; /usr/bin/php {$install_path}/{$script_path}/page.php -p 1050" ,$out,$r);
        if ($r!=0){
            print_r($r);
            print_r($out);
            echo "php page.php article出错";
            exit(0);
        }

    }

    function getIdbyCategory($cat)
    {
        global $g_dbname;
        $cat=trim($cat);
        $sql=sprintf("SELECT term_taxonomy_id as id FROM {$g_dbname}.blog_term_taxonomy t LEFT JOIN {$g_dbname}.blog_terms tax ON t.term_id=tax.term_id  WHERE tax.name='%s'",$cat);
        $r = mysql_query($sql);
        if (!$r){
            echo "执行: ".$sql." 出错:".mysql_error();
            exit(0);
        }
        $row = mysql_fetch_array($r, MYSQL_ASSOC);
        return $row['id'];
    }

    function updateCatetory_clear($articleid){
        global $g_dbname;
        $articleid=intval($articleid);
        if ($articleid){
             $sql=sprintf("DELETE FROM {$g_dbname}.blog_term_relationships WHERE object_id=%d",$articleid);
            //echo $sql;
            $r = mysql_query($sql);
            if (!$r){
                 echo "执行: ".$sql." 出错:".mysql_error();
                 exit(0);
            }
        }
    }

    function  updateCatetory_impl($articleid,$category){
        global $g_dbname;
        $articleid=intval($articleid);
        foreach ($category as $c)
        {
            $catid=getIdbyCategory($c);
            if ($catid)
            {
                $sql=sprintf("INSERT INTO {$g_dbname}.blog_term_relationships SET object_id=%d,term_taxonomy_id=%d ON DUPLICATE KEY UPDATE term_taxonomy_id=%d ",$articleid,$catid,$catid);
                $r = mysql_query($sql);
                if (!$r){
                    echo "执行: ".$sql." 出错:".mysql_error();
                    exit(0);
                }
            }
        } 
    }

    function updateCatetory($articleid,$category,$tags)
    {
       $articleid=intval($articleid);
       updateCatetory_clear($articleid);
       updateCatetory_impl($articleid,$category);
       updateCatetory_impl($articleid,$tags);
    }
 
    // cat = post_tag or category
    function getCatbyId($articleid,$cat)
    {
       global $g_dbname;
       $articleid=intval($articleid);
       $obj=array();
       $sql="";
       if ($articleid<=0){ //小于id表示查询所有的分类或tag
            $sql=sprintf("SELECT DISTINCT tax.name as tag FROM {$g_dbname}.blog_term_taxonomy t LEFT JOIN {$g_dbname}.blog_terms tax ON t.term_id=tax.term_id WHERE t.taxonomy='%s'",$cat);    
       } else {
            $sql=sprintf("SELECT DISTINCT tax.name as tag FROM  {$g_dbname}.blog_posts p LEFT JOIN {$g_dbname}.blog_term_relationships r ON p.id=r.object_id LEFT JOIN {$g_dbname}.blog_term_taxonomy t ON r.term_taxonomy_id=t.term_taxonomy_id LEFT JOIN {$g_dbname}.blog_terms tax ON t.term_id=tax.term_id WHERE t.taxonomy='%s' AND p.id=%d",$cat,$articleid);    
       }
//       echo $sql."<br/>";

       $r = mysql_query($sql);
       if (!$r){
            echo "执行: ".$sql." 出错:".mysql_error();
            exit(0);
       }

       while( $row = mysql_fetch_array($r, MYSQL_ASSOC) ) {
           $obj[]=$row['tag'];
       } 

       return $obj;      
    }



    $action=$_POST['action'];
    //echo "</br>action:".$action;exit(0);
    if ($action=='write'){
        $content=addslashes($_POST['content']);
        $title=addslashes(htmlspecialchars($_POST['title']));
	    $excerpt = addslashes( $_POST['seo'] );
        /*$aaa=$_POST['aaa'];
        if (!isset($aaa)){
           echo "true";
        }
        exit(0);*/
        if (!isset($title) || $title=="" || $content=="" || !isset($content)){
            echo "写毛线，连标题和内容都空，别浪费笔墨纸砚了";
            exit(0);
        }

        $category=$_POST['category'];
        $tags=$_POST['tags'];
        if ( !isset($category) && !isset($tags))
        {
            echo "文章既没有目录也没有标签";
            exit(0);
        }
 
        $pattern ='\[(\[?)(html|code)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
        $content=preg_replace_callback('/'.$pattern.'/s', 'do_shortcode_tag_keep_escaped_tags', $content);
//        echo $content."<br/>";
        $sql=sprintf("INSERT INTO %s.blog_posts SET post_title='%s',post_content='%s',post_date=now(),post_modified=now(),post_status='publish',post_type='post', post_excerpt='%s' ",$g_dbname,$title,$content, $excerpt);
 //       echo $sql;
        $r = mysql_query($sql);
        if (!$r){
            echo "文章插入数据库出错:".mysql_error();
            exit(0);
        }

       
        $sql = "SELECT LAST_INSERT_ID() AS id";
        $r = mysql_query($sql);
        if (!$r){
            echo "执行: ".$sql." 出错:".mysql_error();
            exit(0);
        }
        $row = mysql_fetch_array($r, MYSQL_ASSOC);
        $articleid=intval($row['id']);
        if ($articleid){
            updateCatetory($articleid,$category,$tags);
            exepython($articleid);
            echo "<script>window.location='/manage.php?p={$articleid}'</script>";
        } else {
            echo "数据库出错";
        }
    } else if ($action=='addtag')
    {
        $_POST['termname']=addslashes($_POST['termname']);
        $slug=urlencode($_POST['termname']);
        $sql="INSERT INTO blog_terms(name,slug,term_group) value('{$_POST["termname"]}','{$slug}',0)";
        $r = mysql_query($sql);
        if (!$r){
            echo "执行: ".$sql." 出错:".mysql_error();
            exit(0);
        }

        $sql = "SELECT LAST_INSERT_ID() AS id";
        $r = mysql_query($sql);
        if (!$r){
            echo "执行: ".$sql." 出错:".mysql_error();
            exit(0);
        }
        $row = mysql_fetch_array($r, MYSQL_ASSOC);
        $id=intval($row['id']);
        if ($id){
            foreach ($_POST["terms"] as $tag){
                $sql="INSERT INTO blog_term_taxonomy(term_id,taxonomy,description,parent,count) value({$id},'{$tag}','{$_POST["termname"]}',0,0)";
                $r = mysql_query($sql);
                if (!$r){
                    echo "执行: ".$sql." 出错:".mysql_error();
                    exit(0);
                }
            }
        }
        echo "success";
    } else if ($action=='upload')
    { 
         $allowedExts = array("gif", "jpeg", "jpg", "png","bmp");
         $temp = explode(".", $_FILES["file"]["name"]);
         $extension = end($temp);
         echo "<textarea>";
         if ((($_FILES["file"]["type"] == "image/gif")
			|| ($_FILES["file"]["type"] == "image/jpeg")
			|| ($_FILES["file"]["type"] == "image/jpg")
			|| ($_FILES["file"]["type"] == "image/pjpeg")
			|| ($_FILES["file"]["type"] == "image/x-png")
			|| ($_FILES["file"]["type"] == "image/png")
			|| ($_FILES["file"]["type"] == "image/bmp"))
		&& ($_FILES["file"]["size"] < 2000000)
		&& in_array($extension, $allowedExts))
        {
	    if ($_FILES["file"]["error"] > 0)
	    {
		echo "Return Code: " . $_FILES["file"]["error"] . "";
	    }
	    else
	    {
                  date_default_timezone_set("PRC");
                  $year_dir="Public/Document/imgcdn/". date("Y");
		  if (!file_exists($date_dir))
		  {
                      mkdir($year_dir);
		  }
                  $month_dir=$year_dir."/".date("m");
		  if (!file_exists($month_dir))
		  {
                      mkdir($month_dir);
                  }
                  $img_path=$month_dir."/".md5($_FILES["file"]["tmp_name"]).".".$extension;
                  //echo $img_path;exit(0);
                  if (file_exists($img_path))
                  {
                      echo "why,md5 have two?";
                      exit(0);
                  }
                  else
                  {
			$r=move_uploaded_file($_FILES["file"]["tmp_name"],
					$img_path);
                        if ($r){
			    echo $img_path;
                        } else {
                            echo "move img file wrong";
                        }
	          }
	     }
        }
        else
        {
	    echo '上传准许的格式为"gif", "jpeg", "jpg", "png"';
        }
        echo "</textarea>";
    } else if ($action=='modify'){
       $articleid=$_POST['p'];
       $content=$_POST['content'];
       $excerpt=addslashes( $_POST['seo'] );
       $pattern ='\[(\[?)(html|code)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
       $content=preg_replace_callback('/'.$pattern.'/s', 'do_shortcode_tag_keep_escaped_tags', $content);
       $content=addslashes($content);
       $title=addslashes(htmlspecialchars($_POST['title']));
       if (isset($articleid))
       {
           $articleid=intval($articleid); 
           if ($articleid>0){
               $sql=sprintf("UPDATE %s.blog_posts SET post_title='%s' ,post_content='%s',post_modified=now(),post_excerpt='%s'   WHERE id=%d ",$g_dbname,$title,$content,$excerpt, $articleid );
               $r = mysql_query($sql);
               if (!$r){
                   echo "执行: ".$sql." 出错:".mysql_error();
                   exit(0);
               }

               $category=$_POST['category'];
               $tags=$_POST['tags'];
               //print_r($category);
               //print_r($tags);
               if ( !empty($category) || !empty($tags))
               {
                    updateCatetory($articleid,$category,$tags);
               }
               exepython($articleid);
               echo "<script>window.location='/manage.php?p={$articleid}'</script>";
           }
       }
    }else {
       //show pages 
       $title='';
       $content='';
       $allcategory=getCatbyId(0,'category');        
       $alltags=getCatbyId(0,'post_tag');
       $mycategory=array();
       $mytags=array();
       $articleid=$_GET['p'];
       if (isset($articleid))
       {
           $articleid=intval($articleid); 
           if ($articleid>0){
               $sql=sprintf("SELECT post_title as title ,post_content as content, post_excerpt as excerpt FROM %s.blog_posts WHERE id=%d ",$g_dbname,$articleid);
               //echo $sql;
               $r = mysql_query($sql);
               if (!$r){
                   echo "执行: ".$sql." 出错:".mysql_error();
                   exit(0);
               }

               $row = mysql_fetch_array($r, MYSQL_ASSOC);
               $title=$row['title'];
               $content=$row['content'];
			   $seo = $row['excerpt'];
               $mycategory=getCatbyId($articleid,'category');
               $mytags=getCatbyId($articleid,'post_tag');               
           }
       }
?>
       <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
       <html>
       <head>
       <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
       <title>bz余弦后台</title>
       <style>
           .container {
               width:1000px;
               text-align: left;
               line-height: 36px; 
               margin:0 auto ; 
           }
           body {
               background-color:#BBC7D6;
               font-size:18px;
              <!--   background-image:url("./Public/Document/images/1.jpg"); -->
               color:#90DBFF;
           }
		textarea#styled {
			<!-- width: 600px; -->
			<!-- height: 120px; -->
			border: 3px solid #cccccc;
			padding: 5px;
			font-family: Tahoma, sans-serif;
			background-image: url(bg.jpg);
			background-position: bottom right;
			background-repeat: no-repeat;
		}
       </style>
       <script type="text/javascript">
		var edit_state = 1;
		var file_name = "bg1.jpg";
		function setbg(myid,color)
		{
			if (myid === "styled") 
            {
				if (2 < 1)
				{
					return;
				}
				edit_state = 0;
				now = new Date();
				var aa = now.getMinutes() % 10;
				console.log(aa);
				if ( aa < 5 )
				{
					tmp_file_name = "bg.jpg";
				}
				else
				{
					tmp_file_name = "bg1.jpg";
				}
				if (tmp_file_name == file_name)
				{
					return;
				}
				file_name = tmp_file_name;
				document.getElementById(myid).style.background=color;
				document.getElementById(myid).style.backgroundImage = "url("+file_name+")";
				document.getElementById(myid).style.backgroundPosition = "bottom right";
				document.getElementById(myid).style.backgroundRepeat = "no-repeat";
            }else
			{
				document.getElementById(myid).style.background=color;
			}
		}
       function uploadimg(obj){
           window.open("/upload.html?id="+obj,null,"height=600,width=600");
       }
       function  addtag()
       {
           var termname=document.getElementById("termname");
           if (undefined==termname || ""==termname.value){
               window.alert("你点击的很happy啊,标签目录名不能为空"); 
               return;
           }
           var termvalue=termname.value;
           var input = document.getElementsByName("terms[]");
           var data="action=addtag&termname="+termvalue;
           for (var i=0; i<input.length; i++){
               if(input[i].type == "checkbox" && input[i].checked){
                   data += "&terms[]="+input[i].value;
               }
           }
//           window.alert(data);
           var xmlhttp;
           if (window.XMLHttpRequest){//code for IE7+,Chrome
		  xmlhttp=new XMLHttpRequest();
           } else {// code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	   }
           var url="/manage.php";
           xmlhttp.open("POST",url,false);
           xmlhttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8");
           xmlhttp.send(data);  
           var resp=xmlhttp.responseText.split('\n')[0];
           if ( "success"==resp ){
              var input = document.getElementsByName("terms[]");
              for (var i=0; i<input.length; i++){
                  if(input[i].type == "checkbox" && input[i].checked){
                      if ("post_tag"==input[i].value){
                          var obj=document.getElementById("tag");
                          obj.innerHTML=obj.innerHTML+'<input type="checkbox" name="tags[]" value="'+termvalue+'">'+termvalue;
                      }
                      if ("category"==input[i].value){
                          var obj=document.getElementById("category");
                          obj.innerHTML=obj.innerHTML+'<input type="checkbox" name="category[]" value="'+termvalue+'">'+termvalue;
                      }
                  }
              }
              window.alert("执行成功");
          } else {
              window.alert(xmlhttp.responseText); 
          }
       }
       </script>
       </head>
       <body>
       <div class="container">
		<?php echo htmlspecialchars(' <span style="color:#000000;   font-size:0.83em;line-height:1.5em; font-weight:bold;">1.目录样式 </span> ' ); ?>
       <form method="post" action="manage.php">
       <table>
       <tr><td><input name="title" id="title" type="text"  onfocus="setbg('title','#e5fff3');" style="width:600px"  value="<?php echo $title; ?>" /></td></tr>
       <tr><td><textarea name="content" id="styled" onfocus="setbg('styled','#e5fff3');" rows="30" cols="80"><?php echo $content; ?></textarea></td></tr>
       <tr><td><textarea name="seo" id="seo" rows="6" cols="80"><?php echo $seo; ?></textarea></td></tr>
       <?php if (!empty($allcategory)){?>
           <tr><td  id='category'>目录:
           <?php foreach ($allcategory as $cat){ ?>
               <input type="checkbox" name="category[]" value="<?php echo $cat; ?>" <?php if (in_array($cat,$mycategory)){ echo ' checked="checked "';}?> ><?php echo $cat; ?>
           <?php } ?>
           </td></tr>
       <?php } ?>

       <?php if (!empty($alltags)){?>
       <tr ><td  id='tag'>标签云:
       <?php $index=0; foreach ($alltags as $tag) { ?>
                <?php if ($index%5==0){ echo '<br />';} $index++; ?>
                <input type="checkbox" name="tags[]" value="<?php echo $tag; ?>" <?php if (in_array($tag,$mytags)){ echo ' checked="checked "';}?> ><?php echo $tag; ?>
       <?php } ?>
       </td></tr>
       <?php } ?>
       <tr><td><input type="text" id="termname"/><input type="checkbox" name="terms[]" value="category">目录<input type="checkbox" value="post_tag" name="terms[]">标签<input type="button"   value="增加标签或目录" onclick=addtag()></td></tr>
       <tr><td><input type="button" value="上传图片" onclick=uploadimg('img')><div id='img'></div></td></tr>
       <?php
           if ($articleid=='' || !isset($articleid)){
       ?>
                <tr><td><input type="submit" name="action" value="write"></td></tr>
       <?php }else { ?>
                <tr><td><input type="submit" name="action" value="modify"></td></tr>
                <input type="hidden" name="p"  value="<?php echo $articleid; ?>">
       <?php } ?>
       <tr><td></td></tr>
       </table>
       </form>
       </div>
       </body>
       </html>
<?php
    }  // defautl action
?>

<?php
    mysql_close($link);    
?>
