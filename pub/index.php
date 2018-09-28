<?php
/* PPK JoyPub DEMO based Bytom Blockchain */
/*         PPkPub.org  20180925           */  
/*    Released under the MIT License.     */
require_once "ppk_joypub.inc.php";

//查询带有趣吧数据的retire交易
$array_sets=array();

$tmp_url=BTM_NODE_API_URL.'list-transactions';
//$tmp_post_data='{"account_id": "'.BTM_NODE_API_ACCOUNT_ID_PUB.'"}';
$tmp_post_data='{"unconfirmed":true}';

$obj_resp=commonCallBtmApi($tmp_url,$tmp_post_data);

if(strcmp($obj_resp['status'],'success')===0){
  for($kk=0;$kk<count($obj_resp['data']);$kk++){
    //echo "<!-- ",$obj_resp['data'][$kk]['tx_id'],"-->\n";
    for($pp=0;$pp<count($obj_resp['data'][$kk]['outputs']);$pp++){
      $tmp_out=$obj_resp['data'][$kk]['outputs'][$pp];
      if($tmp_out['type']=='retire' && $tmp_out['asset_id']==JOYBLOCK_TOEKN_ASSET_ID ){
        $tmp_tx_data=getBtmTransactionDetail($obj_resp['data'][$kk]['tx_id']);
        //print_r($tmp_tx_data);
        if($tmp_tx_data!=null){
          $obj_set=parsePubRecordFromBtmTransaction($tmp_tx_data);
          if($obj_set!=null)
            $array_sets[]=$obj_set;
        } 
      }
    }
  }
}

//print_r($array_sets);

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>JoyPub-趣吧（PPk开放协议+比原链+以太坊的DAPP Demo）</title>
<link rel="stylesheet" href="css/joypub.css" />
<style type="text/css">
  .square {
      width: 280px;
      height: 300px;
      background-color: #203060; 
      margin: 10px auto;
      font-size:9px;
      position: absolute;
      
      border: -2px solid #dddddd;
      box-shadow: 2px 2px 3px rgba(50, 50, 50, 0.4);
      -webkit-transition: all 0.5s ease-in;
      -moz-transition: all 0.5s ease-in;
      -ms-transition: all 0.5s ease-in;
      -o-transition: all 0.5s ease-in;
      transition: all 0.5s ease-in;
  }
  
  .square a:link,.square a:visited{color:#fff;text-decoration:underline;}
</style>
</head>
<body>
<div id="web_bg"></div>
<img src="image/joypub.png" width=550 height=80 >
<div id="navibar">
<p>PPkPub.org 20180927 V0.2a , <?php echo  '(Bytom network id: ',$gStrBtmNetworkId,')';?></p>
<h2><?php if(isset($_COOKIE["joypub_user_uri"])){echo '<a href="user.php?user_odin=',$_COOKIE["joypub_user_uri"],'"><img src="'.$_COOKIE["joypub_user_avtar_url"].'" width=16 height=16>',urldecode($_COOKIE["joypub_user_name"]),'</a>';} else { echo '<a href="new_user.php">请点击这里注册一个新用户，获得跨链跨平台的ODIN标识</a>';}   ?></h2>
<h2><a href="new_pub.php">马上创建你的趣吧到Bytom比原链上并让大家来开放交流</a></h2>
</div>

<?php
for($ss=0;$ss<count($array_sets) ;$ss++){
  $obj_set=$array_sets[$ss];
  
  $str_title=$obj_set['title'];
  $str_manager_odin_uri=$obj_set['manager_odin'];
  //$array_gas_asset_uris=$obj_set['gas_asset_uris'];
  $str_pub_odin=ODIN_JOYPUB_BTM_RESOURCE.$obj_set['tx_id'];
  
  $str_img_data_url= (isset($obj_set['pub_logo_url']) && strlen($obj_set['pub_logo_url'])>0 ) ? $obj_set['pub_logo_url'] :'image/ppk.png';

  $str_pub_time = formatTimestampForView($obj_set['block_time'],false);
   
  $leftx = 55+($ss % 3)*300 ;
  $topy  = 65+floor($ss / 3)*310;
  
  echo '<div class="square" style="left: ',$leftx,'px;top: ',$topy,'px;" ><center>',$str_pub_time,'<br><a href="go.php?pub_odin=',$str_pub_odin,'"><img width="256" height="256" src="',$str_img_data_url,'" border=0><br>';
  
  echo '<h2>',$str_title,'</h2></a>';

  echo '</center></div>';
}

echo "<!--当前接入比原网络ID：",$gStrBtmNetworkId,"\n";
print_r($btm_netinfo);
echo "\n-->";
?>

<script type="text/javascript">

</script>
</body>
</html>
