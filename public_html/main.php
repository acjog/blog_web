<?php
   $articleid=$_GET['p'];
   if (isset($articleid))
   {
       $articleid = intval($articleid); 
   }
   else
   {
       $articleid = "new";
   }
?>
<html>
<frameset cols="50%,50%">
  <frame src="/manage.php?p=<?php echo $articleid;  ?>">
  <frame src="/p/<?php echo $articleid;  ?>.html?ver=<?php echo date('s');  ?>" name="rsp_frame">
</frameset>
</html>
