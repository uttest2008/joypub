<?php
/* PPK JoyBlock DEMO based Bytom Blockchain */
require_once "ppk_joypub.inc.php";

if(strlen($g_currentUserODIN)==0){
  Header("Location: new_user.php?backurl=new_pub.php");
  exit(-1);
}

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>JoyPub创建一个趣吧</title>
<link rel="stylesheet" href="css/joypub.css" />
<style type="text/css">
  #yourpub {
      left: 100px;
      top:  100px;
      width: 400px;
      height: 500px;
      margin: 20px auto;
      position: absolute;
  }
</style>
</head>
<body>
<div id="web_bg"></div>
<div id="navibar">
<p>PPkPub.org 20180927 V0.2a , <?php echo  '(Bytom network id: ',$gStrBtmNetworkId,')';?></p>
<h2 align="right">返回<a href="./">趣吧主页</a></h2>
</div>
<div id="yourpub">
<!--
<p>你的比原链账户:（待比原链类似Metamask浏览器插件出来完善）</p>
<p id='you_btm_address'>PPk2018...</p>
-->
<p>你的ODIN标识：<input type=text id="pub_manager_odin" value="<?php echo $g_currentUserODIN; ?>" size=20 readonly="true"  style="background:#CCCCCC"  ></p>
<hr>
<p>
趣吧名称：<input type=text id="pub_title" value="" size=25 onchange="updateTransData();"  ><br>
关联比原资产ID：<input type=text id="btm_asset_id" value="<?php echo JOYBLOCK_TOEKN_ASSET_ID;?>" size=20 onchange="updateTransData();"  ><br>
关联以太坊地址：<input type=text id="eth_contract_address" value="" size=20 onchange="updateTransData();"  ><br>
（填入有效的以太坊Rinkeby测试网络的地址，配合Metamask插件可以支持通过以太坊交易发送消息）<br><br></p>
<p>标志图片URL：<input type=text id="pub_logo_url" value="http://ppkpub.org/images/pub.png" size=20 onchange="updateTransData();"  ></p>
<p>预览：</p>
<center>
<img id="pub_logo_img" width="128" height="128" src="http://ppkpub.org/images/pub.png">
</center>
<p>
<form name="form_pub" id="form_pub" action="send_pub.php" method="post">
转账GAS费用：<input type="text" name="game_trans_fee_btm" id="game_trans_fee_btm" value="<?php echo TX_GAS_AMOUNT_mBTM/1000; ?>" size=10 readonly="true" style="background:#CCCCCC"> BTM<br>
Retire附加数据：<input type="text" name="pub_trans_data_hex" id="pub_trans_data_hex" value="" size=20 readonly="true" style="background:#CCCCCC" ><br>
<br>
　　　　　<input type='button' id="send_trans_btn" value=' 确认发布到比原链上 ' onclick='sendBtmTX();'> 
</form>
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
<script type="text/javascript">

function sendBtmTX() {
  if(document.getElementById('pub_manager_odin').value.length == 0 ){
    alert("请输入有效的创建者对应ODIN标识！");
    return false;
  }
  
  if(document.getElementById('pub_title').value.length == 0 ){
    alert("请输入有效的趣吧名称！");
    return false;
  }
  
  if(document.getElementById('btm_asset_id').value.length == 0 ){
    alert("请输入有效的比原链资产ID！");
    return false;
  }
  
  if(document.getElementById('pub_logo_url').value.length == 0 ){
    alert("请输入有效的标志图片URL！");
    return false;
  }
  
  if(document.getElementById('game_trans_fee_btm').value.length == 0 ){
    alert('请输入有效的转账GAS费用，缺省为 <?php echo TX_GAS_AMOUNT_mBTM/1000; ?> BTM！');
    return false;
  }
  updateTransData();
  document.getElementById('form_pub').submit();
}

function updateTransData(){
  var game_trans_fee_btm = <?php echo TX_GAS_AMOUNT_mBTM/1000; ?>;

  var pub_logo_url=document.getElementById('pub_logo_url').value;
  if(pub_logo_url.length == 0 ){
    return false;
  }
  document.getElementById('pub_logo_img').src=pub_logo_url;
  
  var pub_manager_odin=document.getElementById('pub_manager_odin').value;
  if(pub_manager_odin.length == 0 ){
    return false;
  }
  
  var pub_title=document.getElementById('pub_title').value;
  if(pub_title.length == 0 ){
    return false;
  }
  
  var btm_asset_id=document.getElementById('btm_asset_id').value;
  if(btm_asset_id.length == 0 ){
    return false;
  }
  if(btm_asset_id.toLowerCase().indexOf('ppk:')!=0){
    btm_asset_id="<?php echo ODIN_BTM_ASSET;?>"+btm_asset_id;
  }
  
  var eth_contract_address=document.getElementById('eth_contract_address').value;
  if(eth_contract_address.length > 0 ){
    if(eth_contract_address.toLowerCase().indexOf('ppk:')!=0){
      eth_contract_address="<?php echo ODIN_JOYPUB_ETH_RESOURCE;?>"+eth_contract_address;
    }  
  }
  
  var str_setting='{"@context":["https://schema.org/", "https://ppkpub.org/peerpub/v1" ],"@type":"PeerPub","title":'+JSON.stringify(encodeURI(pub_title,"utf-8"))+', "manager_odin":"'+pub_manager_odin+'", "pub_logo_url":'+JSON.stringify(pub_logo_url)+',"gas_asset_uris":["'+btm_asset_id+'","'+eth_contract_address+'"]}';
                         
 
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
  //document.getElementById('game_trans_qrcode').src='image/star.png';

}


function stringToHex(str){
  var val="";
  for(var i = 0; i < str.length; i++){
      if(val == "")
          val = str.charCodeAt(i).toString(16);
      else
          val += str.charCodeAt(i).toString(16);
  }
  return val;
}


function utf16ToUtf8(s){
	if(!s){
		return;
	}
	
	var i, code, ret = [], len = s.length;
	for(i = 0; i < len; i++){
		code = s.charCodeAt(i);
		if(code > 0x0 && code <= 0x7f){
			//单字节
			//UTF-16 0000 - 007F
			//UTF-8  0xxxxxxx
			ret.push(s.charAt(i));
		}else if(code >= 0x80 && code <= 0x7ff){
			//双字节
			//UTF-16 0080 - 07FF
			//UTF-8  110xxxxx 10xxxxxx
			ret.push(
				//110xxxxx
				String.fromCharCode(0xc0 | ((code >> 6) & 0x1f)),
				//10xxxxxx
				String.fromCharCode(0x80 | (code & 0x3f))
			);
		}else if(code >= 0x800 && code <= 0xffff){
			//三字节
			//UTF-16 0800 - FFFF
			//UTF-8  1110xxxx 10xxxxxx 10xxxxxx
			ret.push(
				//1110xxxx
				String.fromCharCode(0xe0 | ((code >> 12) & 0xf)),
				//10xxxxxx
				String.fromCharCode(0x80 | ((code >> 6) & 0x3f)),
				//10xxxxxx
				String.fromCharCode(0x80 | (code & 0x3f))
			);
		}
	}
	
	return ret.join('');
}

function utf8ToUtf16(s){
	if(!s){
		return;
	}
	
	var i, codes, bytes, ret = [], len = s.length;
	for(i = 0; i < len; i++){
		codes = [];
		codes.push(s.charCodeAt(i));
		if(((codes[0] >> 7) & 0xff) == 0x0){
			//单字节  0xxxxxxx
			ret.push(s.charAt(i));
		}else if(((codes[0] >> 5) & 0xff) == 0x6){
			//双字节  110xxxxx 10xxxxxx
			codes.push(s.charCodeAt(++i));
			bytes = [];
			bytes.push(codes[0] & 0x1f);
			bytes.push(codes[1] & 0x3f);
			ret.push(String.fromCharCode((bytes[0] << 6) | bytes[1]));
		}else if(((codes[0] >> 4) & 0xff) == 0xe){
			//三字节  1110xxxx 10xxxxxx 10xxxxxx
			codes.push(s.charCodeAt(++i));
			codes.push(s.charCodeAt(++i));
			bytes = [];
			bytes.push((codes[0] << 4) | ((codes[1] >> 2) & 0xf));
			bytes.push(((codes[1] & 0x3) << 6) | (codes[2] & 0x3f));			
			ret.push(String.fromCharCode((bytes[0] << 8) | bytes[1]));
		}
	}
	return ret.join('');
}
</script>
</body>
</html>
