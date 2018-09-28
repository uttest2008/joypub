<?php
/* PPK JoyPub DEMO based Bytom Blockchain */
/*         PPkPub.org  20180925           */  
/*    Released under the MIT License.     */
require_once "ppk_joypub.inc.php";

$pub_odin=$_REQUEST['pub_odin'];
if(stripos($pub_odin,ODIN_JOYPUB_BTM_RESOURCE)!==0){
  echo '无效的趣吧ODIN标识. Invalid JoyPub ODIN.';
  exit(-1);
}

if(strlen($g_currentUserODIN)==0){
  Header("Location: new_user.php?backurl=".urlencode('go.php?pub_odin='.$pub_odin));
  exir(-1);
}

$btm_tx_id=substr($pub_odin,strlen(ODIN_JOYPUB_BTM_RESOURCE));
//echo '$btm_tx_id=',$btm_tx_id;

$tmp_tx_data=getBtmTransactionDetail($btm_tx_id);
if($tmp_tx_data==null){
  echo '无效的比原交易ID. Invalid Bytom Transaction ID.';
  exit(-1);
} 
        
$obj_set=parsePubRecordFromBtmTransaction($tmp_tx_data);
if($obj_set==null){
  echo '无效的趣吧记录. Invalid JoyPub Record.';
  exit(-1);
} 

//print_r($obj_set);

$str_title=$obj_set['title'];
$str_manager_odin_uri=$obj_set['manager_odin'];
$str_img_data_url= (isset($obj_set['pub_logo_url']) && strlen($obj_set['pub_logo_url'])>0 ) ? $obj_set['pub_logo_url'] :'image/ppk.png';
$str_pub_time = formatTimestampForView($obj_set['block_time'],false);
$array_gas_asset_uris=$obj_set['gas_asset_uris'];
$eth_contract_address="";

//print_r($array_gas_asset_uris);

$array_posts=array();
for($aa=0;$aa<count($array_gas_asset_uris);$aa++){
  $str_gas_asset_uri=$array_gas_asset_uris[$aa];
  if(stripos($str_gas_asset_uri,ODIN_BTM_ASSET)===0){
    $btm_asset_id=substr($str_gas_asset_uri,strlen(ODIN_BTM_ASSET));
    $tmp_url=BTM_NODE_API_URL.'list-transactions';
    $tmp_post_data='{"unconfirmed":true}';

    $obj_resp=commonCallBtmApi($tmp_url,$tmp_post_data);

    if(strcmp($obj_resp['status'],'success')===0){
      for($kk=0;$kk<count($obj_resp['data']);$kk++){
        //echo "<!-- ",$obj_resp['data'][$kk]['tx_id'],"-->\n";
        for($pp=0;$pp<count($obj_resp['data'][$kk]['outputs']);$pp++){
          $tmp_out=$obj_resp['data'][$kk]['outputs'][$pp];
          if($tmp_out['type']=='retire' && $tmp_out['asset_id']==$btm_asset_id ){
            $tmp_tx_data=getBtmTransactionDetail($obj_resp['data'][$kk]['tx_id']);
            //print_r($tmp_tx_data);
            if($tmp_tx_data!=null){
              $obj_set=parsePubPostRecordFromBtmTransaction($tmp_tx_data);
              if($obj_set!=null && isset($obj_set['post']['pub_uri']) && $obj_set['post']['pub_uri']==$pub_odin)
                $array_posts[$obj_set['block_time'].'_BYTOM_'.$obj_set['block_index']]=$obj_set;
            } 
          }
        }
      }
    }
  }else if(stripos($str_gas_asset_uri,ODIN_JOYPUB_ETH_RESOURCE)===0){
    $eth_contract_address=substr($str_gas_asset_uri,strlen(ODIN_JOYPUB_ETH_RESOURCE));
    //echo '$eth_contract_address=',$eth_contract_address;
    $tmp_url=ETH_EXPLORER_API_URL.'api?module=account&action=txlist&address='.$eth_contract_address.'&startblock=0&endblock=99999999&sort=desc&apikey=YourApiKeyToken';
    $obj_resp=@json_decode(file_get_contents($tmp_url),true);
    //print_r($obj_resp);
    if(isset($obj_resp) && $obj_resp['status']==1 ){
      for($kk=0;$kk<count($obj_resp['result']);$kk++){
        $obj_set=parsePubPostRecordFromEthTransaction($obj_resp['result'][$kk]);
        if($obj_set!=null && $obj_set['post']['pub_uri']==$pub_odin)
            $array_posts[$obj_set['block_time'].'_ETH_'.$obj_set['block_index']]=$obj_set;
      }
    }
  }
}

ksort($array_posts); //按时间排序从旧到新

$enableSendEthTX = strlen($eth_contract_address)>0 && isBrowserSupportEthWeb3Plugin() ;

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>JoyPub-趣吧（基于比原链+PPk开放协议的DAPP Demo）</title>
<link rel="stylesheet" href="css/joypub.css" />
<style type="text/css">
  #pub_post {
      left: 0px;
      top:  100px;
      width: 100%;
      height: 80%;
      margin: 0px auto;
      position: absolute;
      overflow:auto;
  }
  
  #popwin {
      left: -600px;
      top:  0px;
      width: 400px;
      height: 400px;
      color: #000;
      background-color: #eee;
      margin: 10px;
      position: absolute;
      font-size:9px;
      
      border: 5px solid #dddddd;
      box-shadow: 5px 5px 6px rgba(50, 50, 50, 0.4);
      -webkit-transition: all 0.5s ease-in;
      -moz-transition: all 0.5s ease-in;
      -ms-transition: all 0.5s ease-in;
      -o-transition: all 0.5s ease-in;
      transition: all 0.5s ease-in;
  }
  
  /*消息输入框*/
	.putIn{width:100%;height:50px;left:0px;padding:6px 12px;position:fixed;bottom:0;background-color: #000;}
	.putIn input{width:83%;float:left;background:#fff;border:0;height:38px;margin:0;}
	.mui-btn-fast{background:#b2e281;height:38px;border:1px solid #b2e281;}
  
  .putFunc{width:15%;height:100%;float:right;}
  .putFunc button{width:30%;float:left;}
  .putFunc img{width:38px;height:38px;float:center;}
  
	/*对话框*/
	.chatlist{margin-top:44px;padding:12px;}
	.chatlist p.time{margin:0;text-align: center;}
	.chatlist p.time span{padding:0 18px;display:inline-block;font-size:9pt;color: #fff;border-radius: 2px;background-color: #dcdcdc;color:#fff;}
	.chatlist .chatout,.chatlist .chatin{margin:10px 0;}
	.chatlist .chatout{text-align:right;}
	.chatlist .chatout img{float:right;width:40px;height:40px;border-radius:50%;margin: 0 0 0 10px;}
	.chatlist .chatout span{display: inline-block;position:relative;padding:10px;max-width: calc(100% - 90px);min-height: 40px;line-height:20px;font-size: 13px;word-break: break-all;border-radius: 4px;background-color: #b2e281;color:#000;}
	.chatlist .chatout span:before{content: " ";position: absolute;top: 9px;left: 100%;border: 6px solid transparent;border-left-color: #b2e281;}
	.chatlist .chatin img{float:left;width:40px;height:40px;border-radius:50%;margin: 0 10px 0 0;}
	.chatlist .chatin span{display: inline-block;position:relative;padding:10px;max-width: calc(100% - 90px);min-height: 40px;line-height:20px;font-size: 13px;word-break: break-all;border-radius: 4px;text-align: left;background-color: #fafafa;color:#000;}
	.chatlist .chatin span:before{content: " ";position: absolute;top: 9px;right: 100%;border: 6px solid transparent;border-right-color: #fafafa;}

</style>
</head>
<body>
<div id="web_bg"></div>

<div id='pub_top'>
  <table width="100%" border="0">
  <tr>
  <td align="left" width="100">
  <img  style="float:left"  src="<?php echo $str_img_data_url;?>" width=64 height=64>
  </td>
  <td>
  <h2 align="right"><a href="./">趣吧首页</a></h2>
  <h1><?php echo $str_title?></h1>
  <p align="right"><?php echo $str_pub_time?><br>
  此趣吧发起人： <a href="user.php?user_odin=<?php echo $str_manager_odin_uri?>"><?php echo $str_manager_odin_uri?><a/>
  </p>
  </td>
  </tr>
  </table>
</div>

<div id='pub_post'>
<div class="chatlist" id="chatlist">
<?php
foreach($array_posts as $tmp_post ){
  echo '<p class="time"><span>',formatTimestampForView($tmp_post['block_time']),'</span></p>';
  $replaced_post_html=str_replace(array("\r", "\n", "\r\n"), '<br>', $tmp_post['post']['text']);
  
  if($tmp_post['post']['author_odin']==$g_currentUserODIN){ 
    echo '<div class="chatout">
			<a href="user.php?user_odin=',$tmp_user_info['user_odin'],'"><img src="',$_COOKIE["joypub_user_avtar_url"],'"><a/>
			<span>',$replaced_post_html,'</span>
		</div>';
  }else{
    $tmp_user_info=getPubUserInfo($tmp_post['post']['author_odin']);
    
    echo '<div class="chatin">
			<a href="user.php?user_odin=',$tmp_user_info['user_odin'],'"><img src="',$tmp_user_info['avtar'],'"><a/>
			<span>',$replaced_post_html,'</span>
		</div>';
  }
  echo '<!--DEBUG: Post URI: ',  $tmp_post['post_uri'] ," -->\n";
}
?>
</div>
</div>

<!-- 输入框 -->
<div class="putIn">
  <input type="text" id="fast_send_text" onkeypress="if(event.keyCode == 13){fastSend()}" value="TEST:<?php echo $_SERVER["HTTP_USER_AGENT"];?>">
  <div class="putFunc">
  <button type="button" id="fast_send_btn" class="mui-btn mui-btn-fast" onclick="fastSend()">发送</button>
  <img src="image/add.png" onclick="popPost(200,200,'')">
  </div>
</div>

<div id='popwin'>
<p><font size="+2"><strong>发送复杂图文</strong></font></p>
<input type=hidden id="pub_uri" value="<?php echo $pub_odin; ?>">
<input type=hidden id="parent_post_uri" value="">

<p>你的ODIN标识：<input type=text id="author_odin" value="<?php echo $g_currentUserODIN; ?>" size=20 onchange="updateTransData();"  ></p>
<p>
消息内容：<br>
　　　　<textarea id="post_text" rows=3 cols=40 onchange="updateTransData();" ></textarea>
<hr width="70%">
花费比原资产ID：<input type=text id="pub_asset_id" value="<?php echo JOYBLOCK_TOEKN_ASSET_ID;?>" size=20 onchange="updateTransData();"   disabled=true  >
<br>
转账GAS费用：　 <input type="text" name="btm_trans_fee" id="btm_trans_fee" value="<?php echo TX_GAS_AMOUNT_mBTM/1000; ?>" size=10  disabled=true> BTM<br>
Retire附加数据：<input type="text" name="pub_trans_data_hex" id="pub_trans_data_hex" value="" size=20  disabled=true ><br>
　　　　　<input type='button' id="send_btm_trans_btn" value=' 选择发布到比原链上 ' onclick='sendBtmTX();'>
</p>
<br>
<?php
if($enableSendEthTX){
?>
<p>你的以太坊账户:</p>
<p id='you_eth_address'>...</p>
<p>
生成以太坊转账交易参数：<br>
合约地址：<input type=text id=eth_contract_address value="<?php echo $eth_contract_address;?>" size=20  disabled=true><br>
转账金额：<input type=text id=eth_trans_fee_eth value="0.01" size=5 disabled=true> ETH<br>
附加数据：<input type=text id=eth_trans_data_hex value="" size=20 disabled=true><br>
　　　　　<input type='button' id='send_eth_trans_btn' value=' 选择发送到以太坊(Rinkeby)上 ' disabled=true onclick='callEthMetamask();'> 
</p>
<?php
}else if(strlen($eth_contract_address)>0){
  echo '<p>当前浏览器不支持以太坊Metamask插件来发送交易！</p>';
}
?>
<p><br>　　　　　<input type='button' onclick="hidePost();" value="　　取　　消　　"></p>

</div> 
<?php
if($enableSendEthTX)  
    echo '<script src="https://cdn.jsdelivr.net/gh/ethereum/web3.js/dist/web3.min.js"></script>';
?>
<script src="../js/common_func.js"></script>
<script type="text/javascript">
var web3js;

window.addEventListener('load', function() {
  var pub_post = document.getElementById("pub_post");
  pub_post.scrollTop = pub_post.scrollHeight;   
  //pub_post.height = document.getElementById("putIn").top - pub_post.top;

  initEthMetamask();
});

function addNewChatOut(out_text){
  var chatout = document.createElement('div');
  var img = document.createElement('img');
  var span = document.createElement('span');

  chatout.className = 'chatout';

  img.src = "<?php echo $_COOKIE["joypub_user_avtar_url"];?>";
  span.innerHTML = out_text;
  
  chatout.appendChild(img);
  chatout.appendChild(span);
  
  document.getElementById('chatlist').appendChild(chatout);
  
  var scrollObj = document.getElementById("pub_post");
  scrollObj.scrollTop = scrollObj.scrollHeight;   
}

function popPost(leftx,topy,parent_post_uri){
  document.getElementById('parent_post_uri').value=parent_post_uri;

  var div=document.getElementById('popwin');
  div.style.left = leftx +'px';
  div.style.top  = topy +'px'; 
}

function hidePost(){
  var div=document.getElementById('popwin');
  div.style.left = '-600px';
}

function fastSend() {
  post_text=document.getElementById('fast_send_text').value
  
  if(post_text.length == 0 ){
    return false;
  }
  
  var btm_trans_fee = document.getElementById('btm_trans_fee').value;;

  var author_odin=document.getElementById('author_odin').value;
  if(author_odin.length == 0 ){
    return false;
  }
  
  var pub_uri=document.getElementById('pub_uri').value;
  if(pub_uri.length == 0 ){
    return false;
  }
  
  var parent_post_uri="";
  
  var pub_asset_id=document.getElementById('pub_asset_id').value;
  if(pub_asset_id.length == 0 ){
    return false;
  }
  
  if(pub_asset_id.toLowerCase().indexOf('ppk:')!=0){
    pub_asset_id="<?php echo ODIN_BTM_ASSET;?>"+pub_asset_id;
  }
  
  var str_post='{"author_odin":"'+author_odin+'","pub_uri":"'+pub_uri+'","parent_post_uri":"'+parent_post_uri+'","text":'+ JSON.stringify(encodeURI(post_text,"utf-8")) + ',"media":[{"@type": "MediaObject"}]}';
  console.log("str_post="+str_post);
  var str_post_hex=stringToHex(str_post);
  var str_sign='RSAwithSHA256:xxxxxxxxxx';

  var str_setting='{"@context":["https://schema.org/", "https://ppkpub.org/peerpub/v1" ],"@type":"PeerPubPost","post_hex":"'+str_post_hex+'", "sign":"'+str_sign+'"}';

  var tmp_trans_data="<?php  echo PPK_JOYPUB_FLAG; ?>"+str_setting;
  console.log("tmp_trans_data="+tmp_trans_data);
  
  var pub_trans_data_hex = stringToHex(tmp_trans_data);
  
  document.getElementById("fast_send_btn").value="发送中";
  document.getElementById("fast_send_btn").disabled=true;
  
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.open("GET","send_tx.php?pub_trans_data_hex="+pub_trans_data_hex);
  xmlhttp.send();
  xmlhttp.onreadystatechange=function()
  {
    document.getElementById("fast_send_btn").value="发送";
    document.getElementById("fast_send_btn").disabled=false;
    
    if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
      document.getElementById('fast_send_text').value="";
      console.log(xmlhttp.responseText);
      var obj_result = JSON.parse(xmlhttp.responseText);
      if( obj_result!=null && obj_result.status=='success'){
        //var msg_uri="<?php echo ODIN_JOYPUB_BTM_RESOURCE;?>"+obj_result.data.tx_id;
        //alert("消息发送成功\nODIN标识："+msg_uri);
        //self.location="go.php?pub_odin=<?php echo $pub_odin;?>";
        addNewChatOut(post_text);
      }else{
        alert("出错了！\n"+xmlhttp.responseText);
      }
    }
  }

}

function sendBtmTX() {
  if(document.getElementById('author_odin').value.length == 0 ){
    alert("请输入有效的用户标识！");
    return false;
  }
  
  if(document.getElementById('post_text').value.length == 0 ){
    alert("请输入有效的消息内容！");
    return false;
  }
  
  if(document.getElementById('pub_asset_id').value.length == 0 ){
    alert("请输入有效的比原链资产ID！");
    return false;
  }
  if(document.getElementById('btm_trans_fee').value.length == 0 ){
    alert('请输入有效的转账GAS费用，缺省为 <?php echo TX_GAS_AMOUNT_mBTM/1000; ?> BTM！');
    return false;
  }
  updateTransData();
  
  document.getElementById("send_btm_trans_btn").disabled=true;
  document.getElementById("send_btm_trans_btn").value="正在发送消息,请稍候...";
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.open("GET","send_tx.php?pub_trans_data_hex="+document.getElementById("pub_trans_data_hex").value);
  xmlhttp.send();
  xmlhttp.onreadystatechange=function()
  {
    if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
      document.getElementById("send_btm_trans_btn").value=" 选择发布到比原链上 ";
      document.getElementById("send_btm_trans_btn").disabled=false;
      console.log(xmlhttp.responseText);
      var obj_result = JSON.parse(xmlhttp.responseText);
      if( obj_result!=null && obj_result.status=='success'){
        //var msg_uri="<?php echo ODIN_JOYPUB_BTM_RESOURCE;?>"+obj_result.data.tx_id;
        //alert("消息发送成功\nODIN标识："+msg_uri);
        //self.location="go.php?pub_odin=<?php echo $pub_odin;?>";
        hidePost();
        addNewChatOut(document.getElementById('post_text').value);
      }else{
        alert("出错了！\n"+xmlhttp.responseText);
      }
    }
  }
}

function updateTransData(){
  var btm_trans_fee = <?php echo TX_GAS_AMOUNT_mBTM/1000; ?>;

  var author_odin=document.getElementById('author_odin').value;
  if(author_odin.length == 0 ){
    return false;
  }
  
  var pub_uri=document.getElementById('pub_uri').value;
  if(pub_uri.length == 0 ){
    return false;
  }
  
  var parent_post_uri=document.getElementById('parent_post_uri').value;
  
  var post_text=document.getElementById('post_text').value;
  if(post_text.length == 0 ){
    return false;
  }
  
  var pub_asset_id=document.getElementById('pub_asset_id').value;
  if(pub_asset_id.length == 0 ){
    return false;
  }
  
  if(pub_asset_id.toLowerCase().indexOf('ppk:')!=0){
    pub_asset_id="<?php echo ODIN_BTM_ASSET;?>"+pub_asset_id;
  }
  
  var str_post='{"author_odin":"'+author_odin+'","pub_uri":"'+pub_uri+'","parent_post_uri":"'+parent_post_uri+'","text":'+JSON.stringify(encodeURI(post_text,"utf-8")) + ',"media":[{"@type": "MediaObject"}]}';
  console.log("str_post="+str_post);
  var str_post_hex=stringToHex(str_post);
  var str_sign='RSAwithSHA256:xxxxxxxxxx';

  var str_setting='{"@context":["https://schema.org/", "https://ppkpub.org/peerpub/v1" ],"@type":"PeerPubPost","post_hex":"'+str_post_hex+'", "sign":"'+str_sign+'"}';

  var tmp_trans_data="<?php  echo PPK_JOYPUB_FLAG; ?>"+str_setting;
  console.log("tmp_trans_data="+tmp_trans_data);
  
  var pub_trans_data_hex = stringToHex(tmp_trans_data);

  document.getElementById('pub_trans_data_hex').value = pub_trans_data_hex;

  updateEthTransData(pub_trans_data_hex);

}

<?php
if( $enableSendEthTX ){ 
?>
function initEthMetamask(){
  // Checking if Web3 has been injected by the browser (Mist/MetaMask)
  if (typeof web3 !== 'undefined') {
    // Use Mist/MetaMask's provider
    web3js = new Web3(web3.currentProvider);
    
    resetEthInputs(web3js);
  } else {
    console.log('No web3? You should consider trying MetaMask!');
    document.getElementById('send_eth_trans_btn').value = '请先安装Metamask插件';
  }
}

function resetEthInputs(web3js){
  var  eth_network_name='';

  web3js.version.getNetwork((err, netId) => {
    switch (netId) {
      case "1":
        console.log('This is mainnet');
        eth_network_name='mainnet';
        break
      case "2":
        console.log('This is the deprecated Morden test network.');
        eth_network_name='Morden';
        break
      case "3":
        console.log('This is the Ropsten test network.');
        eth_network_name='Ropsten';
        break 
      case "4":
        console.log('This is the Rinkeby test network.');
        eth_network_name='Rinkeby';
        break
      case "42":
        console.log('This is the Kovan test network.');
        eth_network_name='Kovan';
        break
      default:
        console.log('This is an unknown network.');
        eth_network_name='unknown';
        
    };
    
    document.getElementById('you_eth_address').innerText=eth_network_name+':'+web3.eth.accounts[0];
  
    if( eth_network_name!='Rinkeby' ){
      document.getElementById('send_eth_trans_btn').value = '请将钱包切换到Rinkeby测试网络';
    }else{
      // Now you can start your app & access web3 freely:
      document.getElementById('send_eth_trans_btn').disabled = false;
      metamask_doTransaction = function(web3, param) {
          const user = web3.eth.accounts[0];
          web3.eth.getTransactionCount(user, (err, nonce) => {
            var transactionObject = {
              from: user,
              to: param.to,
              value: param.value,
              gas:  654321,
              gasPrice: 1000000000,
              data:  param.data,
              nonce: 0,
            };
            web3.eth.sendTransaction(transactionObject, (err, res) => {
              if (err) { alert(err); }
              else { hidePost(); addNewChatOut(document.getElementById('post_text').value); }
            });
        });
      };
    }
  }); 
}

function updateEthTransData(pub_trans_data_hex){
  document.getElementById('eth_trans_data_hex').value = '0x'+pub_trans_data_hex;
}

function callEthMetamask() {
  var transactionParam = {
      to: document.getElementById('eth_contract_address').value,
      value: document.getElementById('eth_trans_fee_eth').value * 1000000000000000000,
      data: document.getElementById('eth_trans_data_hex').value
    };
    
  metamask_doTransaction(web3js, transactionParam, (err, result) => {
    if (err) { console.log('Error doing transaction.') }
    else { 
      dispatch({ type: 'QUEUE_TXHASH', result: result });      
    }
  });
}

<?php
}else{
?>
function initEthMetamask(){
  console.log('本群组不支持通过以太坊发送消息！Eth not supported.');
}

function updateEthTransData(pub_trans_data_hex){
  
}

function callEthMetamask() {
  alert("本群组不支持通过以太坊发送消息！");
}
<?php  
}
?>

</script>
</body>
</html>
