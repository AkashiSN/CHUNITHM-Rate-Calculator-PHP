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

//userが指定されていない
if(!isset($_GET['user'])){
  //cookieがPOSTされている
  if(isset($_POST['userid'])){
    //UserIdが見つかった
    if(userid_get($_POST['userid'])){
      $userid = userid_get($_POST['userid']);
      $_SESSION['userid'] = $userid;
    }
    //UserIdが見つからない
    else{
      setcookie("errorCode",100001);
      header("HTTP/1.1 301 Moved Permanently");
      header("Location: error.html");
      exit();
    }
  }
  //cookieがPOSTされていない
  else{
    setcookie("errorCode",100001);
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: error.html");
    exit();
  }
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
  <link rel="stylesheet" href="lib/chunithm.css?var=3.5.0" />  
  <script src="/common/js/jquery-1.12.4.min.js" ></script>
  <script src="https://code.highcharts.com/highcharts.js"></script>
	<script src="https://code.highcharts.com/modules/exporting.js"></script>
  <script src="https://platform.twitter.com/widgets.js"></script>
  <script src="lib/chunithm.js?var=3.5.0" ></script>
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
        var i = 0;
        UserHash(hash);
        // 「best」をクリックしたとき
        $('#best').click(function(){
          $("#container").fadeOut();
          $("#disp").fadeIn();
          BestRateDisp();
          i = 0;
        });
        // 「rate」をクリックしたとき
        $('#Rate').click(function(){
          BestRateDisp();
        });
        // 「graph」をクリックしたとき
        $('#graph').click(function(){
        	if(i == 0){
            $("#container").fadeIn();
            $("#disp").fadeOut();
        		$("#sort").fadeOut();
        		i = 1;
        	}
        	else{        		
        		$("#container").fadeOut();
            $("#disp").fadeIn();
            $("#sort").fadeIn();
        		i = 0;
        	}
        });
        // 「score」をクリックしたとき
        $('#score').click(function(){
          Sort_Score();
        });
        // 「diff」をクリックしたとき
        $('#diff').click(function(){
          Sort_Diff();
        });        
        // 「recent」をクリックしたとき
        $('#recent').click(function(){          
          $("#container").fadeOut();
          $("#disp").fadeIn();
          RecentRateDisp();
          i = 0;
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
              <div style="margin-top: 0px" class="more w400" onclick="window.open('https://akashisn.info/?page_id=52#Air', '_blank');"><a href="JavaScript:void(0);">Air対応について</a></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="button">
    <input class="buttons" type="button" value="Best枠" id="best"/>
    <input class="buttons" type="button" value="Recent枠" id="recent"/>
    <input class="buttons" type="button" value="グラフ" id="graph"/>
    <div id="sort" style="display:none">
      <hr class="line_dot_black w420"/>
      <input class="buttons" type="button" value="レート順" id="Rate"/>
      <input class="buttons" type="button" value="スコア順" id="score"/>
      <input class="buttons" type="button" value="難易度順" id="diff"/>
    </div>
  </div>
  <div id="container" style="display:none;margin-bottom:10px;"></div>
  <div id="wrap">
    <div id="disp">
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
  </div>
  <div>CHUNITHM Rate Calculator by Akashi_SN <a href="https://twitter.com/Akashi_SN" class="twitter-follow-button" data-show-count="false">Follow @Akashi_SN</a></div>
</body>

</html>
