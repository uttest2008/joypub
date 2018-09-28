<?php
/* PPK JoyPub DEMO based Bytom Blockchain */
/*         PPkPub.org  20180925           */  
/*    Released under the MIT License.     */
require_once "ppk_joypub.inc.php";

$user_odin=$_REQUEST['user_odin'];
if(stripos($user_odin,ODIN_JOYPUB_BTM_RESOURCE)!==0){
  echo '无效的用户ODIN标识. Invalid User ODIN.';
  exit(-1);
}

$tmp_user_info=getPubUserInfo($user_odin);

$str_created_time = formatTimestampForView($tmp_user_info['block_time'],false);

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>JoyPub-趣吧（基于比原链+PPk开放协议的DAPP Demo）</title>
<link rel="stylesheet" href="css/joypub.css" />
<style type="text/css">

  
  #user_info {
      left: 80px;
      top:  100px;
      width: 90%;
      height: 80%;
      margin: 0px auto;
      position: absolute;
      overflow:auto;
      color: #000;
      background-color: #eee;
  }
  
</style>
</head>
<body>
<div id="web_bg"></div>

<div id='pub_top'>
  <table width="100%" border="0">
  <tr>
  <td align="left" width="100">
  <img  style="float:left"  src="<?php echo $tmp_user_info['avtar'];?>" width=64 height=64>
  </td>
  <td>
  <h1><?php echo $tmp_user_info['name']?></h1>
  </td>
  </tr>
  </table>
</div>

<div id='user_info'>
<h2>用户信息</h2>
<hr>
<P>ODIN标识: <?php echo $tmp_user_info['user_odin']; ?></p>
<P>电子邮件: <?php echo $tmp_user_info['email']; ?></p>
<P>创建时间: <?php echo $str_created_time; ?></p>
<?php

if($tmp_user_info['user_odin']==$g_currentUserODIN){ 
  echo '<p><br><a href="logout.php">退出登录状态</a></p>'; 
}

echo '<p><br><a href="./">回到首页</a></p>';
?>
</div>

<script src="../js/common_func.js"></script>
<script type="text/javascript">
window.addEventListener('load', function() {
  var user_info = document.getElementById("user_info");
  user_info.scrollTop = user_info.scrollHeight;   
});
</script>
</body>
</html>
