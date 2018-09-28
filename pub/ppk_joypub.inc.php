<?php
/* PPK JoyPub DEMO based Bytom Blockchain */
/*         PPkPub.org  20180925           */  
/*    Released under the MIT License.     */

//ini_set("display_errors", "On"); 
//error_reporting(E_ALL | E_STRICT);

require_once "../ppk_joyblock.inc.php";

define('ODIN_JOYPUB_BTM_RESOURCE',ODIN_JOYPUB_PREFIX.'bytom/');
define('ODIN_JOYPUB_ETH_RESOURCE',ODIN_JOYPUB_PREFIX.'eth-rinkeby/');
define('ETH_EXPLORER_API_URL','http://api-rinkeby.etherscan.io/');

define('DEFAULT_ETH_TEST_ADDRESS','0x1372a1f86730d82c176351fbf7b531f8d74adf29');

if(isset($_COOKIE["joypub_user_uri"]))
  $g_currentUserODIN=$_COOKIE["joypub_user_uri"];
else
  $g_currentUserODIN='';

$g_cachedUserInfos=array();




//从交易详情中解析出趣吧定义数据
function parsePubRecordFromBtmTransaction($obj_tx_data){
  $str_hex=parseSpecHexFromBtmTransaction($obj_tx_data,PPK_JOYPUB_FLAG);
  if(strlen($str_hex)>0){
    $obj_set=json_decode(hexToStr($str_hex),true);
    if(isset($obj_set['manager_odin'])>0){ //有效数据
      $obj_set['title']=@urldecode($obj_set['title']); //适配中文编码转换
      
      $obj_set['tx_id'] = $obj_tx_data['tx_id'];
      $obj_set['block_time'] = $obj_tx_data['block_time'];
      $obj_set['block_height'] = $obj_tx_data['block_height'];
      $obj_set['block_hash'] = $obj_tx_data['block_hash'];
      $obj_set['block_index'] = $obj_tx_data['block_index']; //position of the transaction in the block.

      return $obj_set;
    }
  }
  return null;
}

//从BTM交易详情中解析出趣吧消息的定义数据
function parsePubPostRecordFromBtmTransaction($obj_tx_data){
  $str_hex=parseSpecHexFromBtmTransaction($obj_tx_data,PPK_JOYPUB_FLAG);
  if(strlen($str_hex)>0){
    $obj_set=json_decode(hexToStr($str_hex),true);
    if(isset($obj_set['post_hex'])>0 && strlen($obj_set['post_hex'])>0){ //有效数据
    
      $obj_set['post']=@json_decode(hexToStr($obj_set['post_hex']),true);
      $obj_set['post_uri']=ODIN_JOYPUB_BTM_RESOURCE.$obj_tx_data['tx_id'];
      
      $obj_set['post']['text']=@urldecode($obj_set['post']['text']); //适配中文编码转换
      
      $obj_set['tx_id'] = $obj_tx_data['tx_id'];
      $obj_set['block_time'] = $obj_tx_data['block_time'];
      $obj_set['block_height'] = $obj_tx_data['block_height'];
      $obj_set['block_hash'] = $obj_tx_data['block_hash'];
      $obj_set['block_index'] = $obj_tx_data['block_index']; //position of the transaction in the block.

      return $obj_set;
    }
  }
  return null;
}

//从ETH交易详情中解析出趣吧消息的定义数据
function parsePubPostRecordFromEthTransaction($obj_tx_data){
  $tmp_input=$obj_tx_data['input'];
  $str_flag_hex=strtohex(PPK_JOYPUB_FLAG);
  $flag_posn=strpos($tmp_input,$str_flag_hex);
  //echo 'flag_posn=',$flag_posn;
  if($flag_posn>0){ //符合特征
    $str_hex=substr($tmp_input,$flag_posn+strlen($str_flag_hex));
    if(strlen($str_hex)>0){
      $obj_set=json_decode(hexToStr($str_hex),true);
      if(isset($obj_set['post_hex'])>0 && strlen($obj_set['post_hex'])>0){ //有效数据
        $obj_set['post']=@json_decode(hexToStr($obj_set['post_hex']),true);
        
        $obj_set['post']['text']=@urldecode($obj_set['post']['text']); //适配中文编码转换
        
        $obj_set['post_uri']=ODIN_JOYPUB_ETH_RESOURCE.$obj_tx_data['hash'];
        
        $obj_set['tx_id'] = $obj_tx_data['hash'];
        $obj_set['block_time'] = $obj_tx_data['timeStamp'];
        $obj_set['block_height'] = $obj_tx_data['blockNumber'];
        $obj_set['block_hash'] = $obj_tx_data['blockHash'];
        $obj_set['block_index'] = $obj_tx_data['transactionIndex']; //position of the transaction in the block.

        return $obj_set;
      }
    }
  }
  return null;
}

//从BTM交易详情中解析出趣吧用户的定义数据
function parsePubUserRecordFromBtmTransaction($obj_tx_data){
  $str_hex=parseSpecHexFromBtmTransaction($obj_tx_data,PPK_JOYPUB_FLAG);
  if(strlen($str_hex)>0){
    $obj_set=json_decode(hexToStr($str_hex),true);
    if(isset($obj_set['avtar'])>0 && strlen($obj_set['avtar'])>0){ //有效数据
      $obj_set['tx_id'] = $obj_tx_data['tx_id'];
      $obj_set['block_time'] = $obj_tx_data['block_time'];
      $obj_set['block_height'] = $obj_tx_data['block_height'];
      $obj_set['block_hash'] = $obj_tx_data['block_hash'];
      $obj_set['block_index'] = $obj_tx_data['block_index']; //position of the transaction in the block.

      return $obj_set;
    }
  }
  return null;
}

//按标识获取用户信息
function  getPubUserInfo($user_odin){
  if(isset($g_cachedUserInfos[$user_odin]))
    return $g_cachedUserInfos[$user_odin];
  
  $default_user_info=array(
    'user_odin'=> $user_odin,
    'name'=>"",
    'email'=>"",
    'avtar'=>"image/user.jpg"
  );
  
  if(stripos($user_odin,ODIN_JOYPUB_BTM_RESOURCE)!==0){
    $g_cachedUserInfos[$user_odin]=$default_user_info;
    return $default_user_info;
  }
  $btm_tx_id=substr($user_odin,strlen(ODIN_JOYPUB_BTM_RESOURCE));

  $tmp_tx_data=getBtmTransactionDetail($btm_tx_id);
  if($tmp_tx_data==null){
    $g_cachedUserInfos[$user_odin]=$default_user_info;
    return $default_user_info;
  } 
          
  $obj_set=parsePubUserRecordFromBtmTransaction($tmp_tx_data);
  if($obj_set==null){
    $obj_set = $default_user_info;
  } 
  
  $obj_set['user_odin']=$user_odin;
  $obj_set['name']=@urldecode($obj_set['name']); //适配中文编码转换
  
  $g_cachedUserInfos[$user_odin]=$obj_set;
  return $obj_set;
}

