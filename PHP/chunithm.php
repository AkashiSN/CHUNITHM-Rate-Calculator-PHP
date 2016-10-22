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
      header("HTTP/1.1 301 Moved Permanently");
      header("Location: main.php");
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
//user(hash)が指定されてる
else{
  $json = UserData_show($_GET['user']);
  if($json === null){      
    http_response_code(403);
    exit();
  }
  $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
  $UserData = json_decode($json,true);
  $UserRateDisp = UserRateDisp($UserData);
  $BestRateDisp = BestRateDisp($UserData);  
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja" dir="ltr">

<head>

  <meta charset="UTF-8" />
  <meta name="viewport" id="viewport" content="width=device-width,user-scalable=yes" />
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
  <link rel="stylesheet" href="lib/common.css" />
  <link rel="stylesheet" href="lib/contents.css" />
  <link rel="stylesheet" href="lib/chunithm.css?var=3.6.3" />
  <title>CHUNITHM Rate Calculator</title>
</head>

<body>
  <div id="sub_title">CHUNITHM Rate Calculator</div>
<?php
    echo $UserRateDisp;
    button_show();
  if(isset($_GET['frame'])){
    if($_GET['frame'] === 'Best'){
      if(isset($_GET['sort'])){
        if($_GET['sort'] ===  'Sort_Score'){
          $Sort_Score = Sort_Score($UserData);
          echo $Sort_Score;
        }
        else if($_GET['sort'] ===  'Sort_Rate'){
          echo $BestRateDisp;
        }
        else if($_GET['sort'] ===  'Sort_Diff'){
          $Sort_Diff = Sort_Diff($UserData);
          echo $Sort_Diff;
        }
      }
      echo $BestRateDisp;
    }
    else if($_GET['frame'] === 'Recent'){
      $RecentRateDisp = RecentRateDisp($UserData);
      echo $RecentRateDisp;
    }
    else if($_GET['frame'] === 'Graph'){
      $Graph = Graph($UserData);
      echo $Graph;
    }
  }
  else{
    echo $BestRateDisp;
  }
?>
  <div style="font-size:15px">CHUNITHM Rate Calculator by Akashi_SN <a href="https://twitter.com/Akashi_SN" class="twitter-follow-button" data-show-count="false">Follow @Akashi_SN</a></div>
</body>
  <script type="text/javascript">
    (function(d,e,j,h,f,c,b){d.GoogleAnalyticsObject=f;d[f]=d[f]||function(){(d[f].q=d[f].q||[]).push(arguments)},d[f].l=1*new Date();c=e.createElement(j),b=e.getElementsByTagName(j)[0];c.async=1;c.src=h;b.parentNode.insertBefore(c,b)})(window,document,"script","//www.google-analytics.com/analytics.js","ga");ga("create","UA-74926268-1","auto");ga("send","pageview");!function(f,a,g){var e,b=f.getElementsByTagName(a)[0],c=/^http:/.test(f.location)?"http":"https";if(!f.getElementById(g)){e=f.createElement(a);e.id=g;e.async=true;e.src=c+"://platform.twitter.com/widgets.js";b.parentNode.insertBefore(e,b)}}(document,"script","twitter-wjs");
  </script>
</html>
