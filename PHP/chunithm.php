<?php
/*
  +---------------------------------------------------+
  | CHUNITHM Rate Calculator           [chunithm.php] |
  +---------------------------------------------------+
  | Copyright (c) 2015-2016 Akashi_SN                 |
  +---------------------------------------------------+
  | This software is released under the MIT License.  |
  | http://opensource.org/licenses/mit-license.php    |
  +---------------------------------------------------+
  | Author: Akashi_SN <info@akashisn.info>            |
  +---------------------------------------------------+
*/

//-----------------------------------------------------
// header
//-----------------------------------------------------

require("common.php");
session_start();

/*エラー判定(直接アクセス)*/
if(isset($_GET['user'])){

}
else if(isset($_POST['userid'])){
  if(userid_get($_POST['userid'])){
    $userid = userid_get($_POST['userid']);
    $_SESSION['userid'] = $userid;
  }
  else{
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: https://akashisn.info/?page_id=52");
  exit();
  }
}
else{
  header("HTTP/1.1 301 Moved Permanently");
  header("Location: https://akashisn.info/?page_id=52");
  exit();
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja" dir="ltr">

<head>

  <meta charset="UTF-8" />
  <meta name="viewport" id="viewport" content="width=320,user-scalable=yes" />
  <meta name="format-detection" content="telephone=no">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="Pragma" CONTENT="no-cache">
  <meta http-equiv="Cache-Control" CONTENT="no-cache">
  <meta http-equiv="Expires" CONTENT="-1">
  <meta name="robots" content="INDEX, FOLLOW" />
  <meta property="og:title" content="CHUNITHM Rate Calculator" />
  <meta property="og:type" content="tools" />
  <meta property="og:description" content="チュウニズムのレートを計算するものです。" />
  <meta property="og:url" content="https://akashisn.info/?page_id=52" />
  <meta property="og:image" content="/common/images/chunithm/img.jpg" />
  <meta property="og:site_name" content="Akashi_SNの日記" />
  <title>CHUNITHM Rate Calculator</title>
  <link rel="stylesheet" href="https://chunithm-net.com/mobile/common/css/common.css" />
  <link rel="stylesheet" href="https://chunithm-net.com/mobile/common/css/contents.css" />
  <link rel="stylesheet" href="lib/chunithm.css?var=3.1.0" />
  <script src="/common/js/jquery-1.12.4.min.js" ></script>
  <script src="https://platform.twitter.com/widgets.js"></script>
  <script src="lib/chunithm.js?var=3.1.0" ></script>
  <script type="text/javascript">
    // DOMを全て読み込んだあとに実行される
    $(function() {
      var req = location.search.replace(/^\?(.*)$/, '$1');
      if(req == ""){ 
        <?php 
          if(isset($_SESSION['userid'])){
        ?>
            var Userid = <?php echo $_SESSION['userid'];?>;
            JsonPost(Userid);            
        <?php    
          }        
        ?>          
      }else{
        var data = req.split("=");
        var hash = data[1];
        UserHash(hash);
        // 「#best」をクリックしたとき
        $('#best').click(function(){
          BestRateDisp();
        });
        // 「#recent」をクリックしたとき
        $('#recent').click(function(){
          RecentRateDisp();
        });
      }
    });
  </script>

</head>

<body>
  <div id="overlay" style="display: block;">
    <div id="loading" align="center">
      <img src="lib/gif-load.gif" alt="Now Loading..." />
      <p style="text-align:center;">Loading･･･</p>
    </div>
  </div>
  <div id="sub_title">CHUNITHM Rate Calculator</div>
  <div id="wrap">
    <div style="margin-top:10px;margin-bottom:0px;padding-bottom:0px;" id="inner" >
      <div class="frame01 w460">
        <div style="padding-bottom:0px;" class="frame01_inside w450">
          <h2 style="margin-top:10px;" id="page_title">ユーザー</h2>
          <hr class="line_dot_black w420">
          <div id="userInfo_result">
            <div class="w420 box_player clearfix">
              <div id="UserCharacter" class="player_chara" style="">
                <img id="characterFileName">
              </div>
              <div class="box07 player_data">
                <div id="UserHonor" class="player_honor" style="">
                  <div class="player_honer_text_view">
                    <div id="HonerText" class="player_honer_text"></div>
                  </div>
                </div>
                <div id="UserReborn" class="player_reborn_0"></div>
                <div class="player_name">
                  <div class="player_lv"><span class="font_small mr_5">Lv.</span><span id="UserLv"></span></div><span id="UserName"></span>
                </div>
                <div class="player_rating" id="player_rating"></div>
              </div>
              <div id="tweet" style="margin-top: 10px;"></div>
              <div style="margin-top: 0px" class="more w400" onclick="window.open('https://akashisn.info/?page_id=52', '_blank');"><a href="JavaScript:void(0);">使い方</a></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <input class="best" type="button" value="Best枠" id="best"/>
  <input class="best" type="button" value="Recent枠" id="recent"/>
  <div id="wrap">
    <div style="margin-bottom:0px;padding-bottom:0px;" id="rate" id="inner">
      <div class="frame01 w460">
        <div class="frame01_inside w450">
          <h2 style="margin-top:10px;" id="page_title">BEST枠</h2>
          <hr class="line_dot_black w420">
          <div id="userPlaylog_result">
            <div class="box01 w420">
              <div class="mt_10">
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="frame01 w460">
        <div class="frame01_inside w450">
          <h2 style="margin-top:10px;" id="page_title">BEST枠外</h2>
          <hr class="line_dot_black w420">
          <div id="userPlaylog_result">
            <div class="box01 w420">
              <div class="mt_10">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div>CHUNITHM Rate Calculator by Akashi_SN <a href="https://twitter.com/Akashi_SN" class="twitter-follow-button" data-show-count="false">Follow @Akashi_SN</a></div>
</body>

</html>
