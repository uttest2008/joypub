<?php
/* PPK JoyBlock DEMO based Bytom Blockchain */
require_once "ppk_joypub.inc.php";

if(isset($_REQUEST['backurl']))
  $back_url=$_REQUEST['backurl'];
else
  $back_url='./';

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>JoyPub创建一个用户ODIN标识</title>
<link rel="stylesheet" href="css/joypub.css" />
<style type="text/css">
  #yourinfo {
      left: 100px;
      top:  100px;
      width: 300px;
      height: 500px;
      margin: 20px auto;
      position: absolute;
  }
</style>
</head>
<body>
<div id="web_bg"></div>
<div id="navibar">
<p align="right">JoyPub@PPkPub.org 20180927 V0.2a , <?php echo  '(Bytom network id: ',$gStrBtmNetworkId,')';?></p>
<h2 align="right">返回<a href="./">趣吧主页</a></h2>
</div>
<div id="yourinfo">
<!--
<p>你的比原链账户:（待比原链类似Metamask浏览器插件出来完善）</p>
<p id='you_btm_address'>PPk2018...</p>
-->
<h2>创建一个新用户ODIN标识</h2>
<hr>
<p>用户名称：<input type=text id="user_name" value="" size=20 onchange="updateTransData();"  ></p>
<p>电子邮件：<input type=text id="user_email" value="" size=20 onchange="updateTransData();"  ></p>
<p>头像URL： <input type=text id="user_avtar_url" value="http://ppkpub.org/images/user.png" size=20 onchange="updateTransData();"  ></p>
<p>头像预览：</p>
<center>
<img id="user_avtar_img" width="128" height="128" src="http://ppkpub.org/images/user.png">
</center>
<p>转账GAS费用：<input type="text" name="game_trans_fee_btm" id="game_trans_fee_btm" value="<?php echo TX_GAS_AMOUNT_mBTM/1000; ?>" size=10 readonly="true" style="background:#CCCCCC"> BTM</p>
<p>Retire附加数据：<input type="text" name="pub_trans_data_hex" id="pub_trans_data_hex" value="" size=20 readonly="true" style="background:#CCCCCC" ></p>

<p><br>
　　　　　<input type='button' id="send_trans_btn" value=' 确认发布到比原链上 ' onclick='sendBtmTX();'> 
</p>

<!--
<p>二维码（可使用比原链钱包APP来扫码发送交易）:</p>
<p><img id="game_trans_qrcode" border=0 width=250 height=250 src="image/star.png" title="qrcode"></p>
<p><input type=text id="qrcode_text" value="..." size=30></p>
<hr>
</p>
<p><a target="_blank" href="https://bytom.io/"><img src="https://bytom.io/wp-content/uploads/2018/04/logo-white-v.png" alt="下载比原链钱包" width=200 height=50></a>
</p> 
-->

<!--
<script src="https://cdn.jsdelivr.net/gh/ethereum/web3.js/dist/web3.min.js"></script>
-->
<script src="../js/common_func.js"></script>
<script type="text/javascript">
function sendBtmTX() {
  if(document.getElementById('user_name').value.length == 0 ){
    alert("请输入有效的用户名称！");
    return false;
  }
  
  if(document.getElementById('user_avtar_url').value.length == 0 ){
    alert("请输入有效的用户头像图片URL！");
    return false;
  }

  if(document.getElementById('game_trans_fee_btm').value.length == 0 ){
    alert('请输入有效的转账GAS费用，缺省为 <?php echo TX_GAS_AMOUNT_mBTM/1000; ?> BTM！');
    return false;
  }
  
  updateTransData();
  
  document.getElementById("send_trans_btn").disabled=true;
  document.getElementById("send_trans_btn").value="正在自动生成用户标识,请稍候...";
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.open("GET","send_tx.php?pub_trans_data_hex="+document.getElementById("pub_trans_data_hex").value);
  xmlhttp.send();
  xmlhttp.onreadystatechange=function()
  {
    if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
      document.getElementById("send_trans_btn").value=" 确认发布到比原链上 ";
      document.getElementById("send_trans_btn").disabled=false;
      console.log(xmlhttp.responseText);
      var obj_result = JSON.parse(xmlhttp.responseText);
      if( obj_result!=null && obj_result.status=='success'){
        var user_uri="<?php echo ODIN_JOYPUB_BTM_RESOURCE;?>"+obj_result.data.tx_id;
        setCookie('joypub_user_uri',user_uri,365);
        setCookie('joypub_user_name',encodeURI(document.getElementById('user_name').value,"utf-8"),365);
        setCookie('joypub_user_avtar_url',document.getElementById('user_avtar_url').value,365);
        alert("创建用户成功\nODIN标识："+user_uri);
        self.location="<?php echo $back_url;?>";
      }else{
        alert("出错了！\n"+xmlhttp.responseText);
      }
    }
  }
}


function updateTransData(){
  var game_trans_fee_btm = <?php echo TX_GAS_AMOUNT_mBTM/1000; ?>;
  
  var user_avtar_url=document.getElementById('user_avtar_url').value;
  if(user_avtar_url.length == 0 ){
    return false;
  }
  document.getElementById('user_avtar_img').src=user_avtar_url;
  
  var user_name=document.getElementById('user_name').value;
  if(user_name.length == 0 ){
    return false;
  }
  
  var user_email=document.getElementById('user_email').value;

  var str_setting='{"@context":["https://schema.org/", "https://ppkpub.org/peerpub/v1" ],"@type":"PeerPub","name":'+JSON.stringify(encodeURI(user_name,"utf-8"))+',"email":'+JSON.stringify(encodeURI(user_email,"utf-8"))+', "avtar":'+JSON.stringify(user_avtar_url)+',"authenticationCredential":[{"type": "RsaCryptographicKey","publicKeyPem": "-----BEGIN PUBLIC KEY...END PUBLIC KEY-----" }]}';
                         
 
  var game_trans_data="<?php  echo PPK_JOYPUB_FLAG; ?>"+str_setting;
  console.log("game_trans_data="+game_trans_data);
  
  var pub_trans_data_hex = stringToHex(game_trans_data);

  document.getElementById('pub_trans_data_hex').value= pub_trans_data_hex;
  
  //var btm_uri='bytom:'+document.getElementById('guess_contract_uri').value+'?value='+game_trans_fee_btm+'&data='+pub_trans_data_hex;
  //document.getElementById('qrcode_text').value= btm_uri;
  //document.getElementById('game_trans_qrcode').src='http://qr.liantu.com/api.php?text='+encodeURIComponent(btm_uri);

}

function resetAll(){
  document.getElementById('game_trans_fee_btm').value=<?php echo TX_GAS_AMOUNT_mBTM/1000; ?>;
  document.getElementById('pub_trans_data_hex').value='';
  
  //document.getElementById('qrcode_text').value= '';
  //document.getElementById('game_trans_qrcode').src='star.png';

}

function setCookie(c_name, value, expiredays){
  var exdate=new Date();
  exdate.setDate(exdate.getDate() + expiredays);
  document.cookie=c_name+ "=" + escape(value) + ((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}

function getCookie(c_name){
  if (document.cookie.length>0){ 
    c_start=document.cookie.indexOf(c_name + "=");
    if (c_start!=-1){ 
      c_start=c_start + c_name.length+1;
      c_end=document.cookie.indexOf(";",c_start);
      if (c_end==-1) 
        c_end=document.cookie.length    
      return unescape(document.cookie.substring(c_start,c_end));
    } 
  }
  return "";
}


</script>
</body>
</html>
