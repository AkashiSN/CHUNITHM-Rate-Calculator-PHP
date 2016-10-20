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
  <link rel="stylesheet" href="lib/chunithm.css?var=3.6.1" />  
  <script src="/common/js/jquery-1.12.4.min.js" ></script>
  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script src="https://code.highcharts.com/modules/exporting.js"></script>
  <script src="https://platform.twitter.com/widgets.js"></script>
  <script src="lib/chunithm.js?var=3.6.1" ></script>
  <script type="text/javascript">
    // DOMを全て読み込んだあとに実行される
    $(document).ready(function(){
      // 「Best_Button」をクリックしたとき
      $('#Best_Button').click(function(){
        location = '?user=<?php echo $_GET['user'];?>&frame=Best'
      });
      // 「Sort_Rate_Button」をクリックしたとき
      $('#Sort_Rate_Button').click(function(){
        location = '?user=<?php echo $_GET['user'];?>&frame=Best&sort=Sort_Rate'
      });
      // 「Sort_Score_Button」をクリックしたとき
      $('#Sort_Score_Button').click(function(){
        location = '?user=<?php echo $_GET['user'];?>&frame=Best&sort=Sort_Score'
      });
      // 「Sort_Diff_Button」をクリックしたとき
      $('#Sort_Diff_Button').click(function(){        
        location = '?user=<?php echo $_GET['user'];?>&frame=Best&sort=Sort_Diff'
      });
      // 「Recent_Button」をクリックしたとき
      $('#Recent_Button').click(function(){   
        location = '?user=<?php echo $_GET['user'];?>&frame=Recent'
      });
      // 「Graph_Button」をクリックしたとき
      $('#Graph_Button').click(function(){
        location = '?user=<?php echo $_GET['user'];?>&frame=Graph'
      });
    });
  </script>
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

</html>
