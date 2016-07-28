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
if(!isset($_POST['userid']) && !isset($_SESSION['userid'])){
  //header("HTTP/1.1 301 Moved Permanently");
  //header("Location: https://akashisn.info?article=4");
  //exit();
}

if(isset($_SESSION['userid'])){
  $userid = $_SESSION['userid'];
}
if(isset($_POST['userid'])){
  $userid = userid_get($_POST['userid']);
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

  <title>CHUNITHM Rate Calculator</title>
  <link rel="stylesheet" href="https://chunithm-net.com/mobile/common/css/common.css" />
  <link rel="stylesheet" href="https://chunithm-net.com/mobile/common/css/contents.css" />
  <link rel="stylesheet" href="lib/chunithm.css" />
  <script src="/common/js/jquery-1.12.4.min.js" ></script>
  <script src="https://platform.twitter.com/widgets.js"></script>
  <script src="lib/chunithm.js" ></script>
  <script type="text/javascript">
    var Userid = <?php echo $userid;?>;
    // DOMを全て読み込んだあとに実行される
    $(function() {
      JsonPost(Userid);
      // 「#best」をクリックしたとき
      $('#best').click(function(){
        BestRateDisp();
      });
      // 「#recent」をクリックしたとき
      $('#recent').click(function(){
        RecentRateDisp();
      });
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
  <h2 id="rate">
    <p id="best-max">BEST枠平均: /最大レート: </p>
    <p id="recent-disp">RECENT枠平均: /表示レート: </p>
  </h2>
  <p><a style="font-size:18pt;" href="https://akashisn.info/?article=4" target=_brank>使い方</a><div id="tweet"></div></p><!--tweetボタン-->
  
  <input class="best" type="button" value="Best枠" id="best"/>
  <input class="best" type="button" value="Recent枠" id="recent"/>
  <div id="wrap">    
    <div id="inner">
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
