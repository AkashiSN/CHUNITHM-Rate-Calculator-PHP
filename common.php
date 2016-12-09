<?php
/*
  +---------------------------------------------------+
  | CHUNITHM Rate Calculator             [common.php] |
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
// ヘッダー
//-----------------------------------------------------

  require('define.php');

//-----------------------------------------------------
//  切り捨て
//-----------------------------------------------------

  function Truncation($num,$n){
    $z = pow( 10 , $n );
    return ( floor(  $num* $z ) / $z );
  }

//-----------------------------------------------------
//  UserID取得
//-----------------------------------------------------

  function userid_get($u){
    $a = explode(';',$u);
    $b = '';
    for($i = 0;$i< count($a);$i++){
      $pos = strpos($a[$i],'userId');
      if ($pos !== false) {
        $b = $i;
      }
    }
    $c = explode('=',$a[$b]);
    if(isset($c[1])){
      return $c[1];
    }
    else{
      return null;
    }
  }

//-----------------------------------------------------
//  楽曲のベストスコア取得
//-----------------------------------------------------

  //level = 19902:expert,level = 19903:master
  function BestScore_get($userid,$level){
    $url = 'https://chunithm-net.com/ChuniNet/GetUserMusicApi';
    $data = array(
      'level' => $level,
      'userId' => $userid,
    );
    $options = array(
    'http' => array(
      'method'  => 'POST',
      'content' => json_encode( $data ),
      'header'=>  "Content-Type: application/json\r\n" .
                  "Accept: application/json\r\n"
      )
    );
    $context  = stream_context_create( $options );
    $score = file_get_contents( $url, false, $context );
    return json_decode( $score , true );
  }

//-----------------------------------------------------
//  最近のプレイ履歴の取得
//-----------------------------------------------------

  function Recent_score_get($userid){
    $url = 'https://chunithm-net.com/ChuniNet/GetUserPlaylogApi';
    $data = array(
      'userId' => $userid,
    );
    $options = array(
    'http' => array(
      'method'  => 'POST',
      'content' => json_encode( $data ),
      'header'=>  "Content-Type: application/json\r\n" .
                  "Accept: application/json\r\n"
      )
    );
    $context  = stream_context_create( $options );
    $score = file_get_contents( $url, false, $context );
    return json_decode( $score , true );
  }

//-----------------------------------------------------
//  ユーザーデータの詳細取得
//-----------------------------------------------------

  function Rate_get($userid){
    $url = 'https://chunithm-net.com/ChuniNet/GetUserInfoApi';
    $data = array(
      'userId' => $userid,
      'friendCode' => 0,
      'fileLevel' => 1,
    );
    $options = array(
    'http' => array(
      'method'  => 'POST',
      'content' => json_encode( $data ),
      'header'=>  "Content-Type: application/json\r\n" .
                  "Accept: application/json\r\n"
      )
    );
    $context  = stream_context_create( $options );
    $rate = file_get_contents( $url, false, $context );
    return json_decode( $rate , true );
  }

//-----------------------------------------------------
//  フレンドコード取得
//-----------------------------------------------------

  function friendCode_get($userid){
    $url = 'https://chunithm-net.com/ChuniNet/GetUserFriendlistApi';
    $data = array(
      'userId' => $userid,
      'state' => 4,
    );
    $options = array(
    'http' => array(
      'method'  => 'POST',
      'content' => json_encode( $data ),
      'header'=>  "Content-Type: application/json\r\n" .
                  "Accept: application/json\r\n"
      )
    );
    $context  = stream_context_create( $options );
    $rate = file_get_contents( $url, false, $context );
    return json_decode( $rate , true );
  }

//-----------------------------------------------------
//  main.phpにポスト
//-----------------------------------------------------

  function api($userid){
    $url = 'main.php';
    $data = array(
      'userid' => $userid,
    );
    $options = array(
    'http' => array(
      'method'  => 'POST',
      'content' => http_build_query( $data )
    ));
    $context  = stream_context_create( $options );
    $hash = file_get_contents( $url, false, $context );
  }

//-----------------------------------------------------
//  データーベースに登録
//-----------------------------------------------------

  function UserData_set($FriendCode,$UserName,$Json){
    try {
      $count = 0;
      $hash =  hash_hmac('sha256', $FriendCode, false);
      $pdo = new PDO(DNS,USER,PASS,array(PDO::ATTR_EMULATE_PREPARES => false));
      $sql = 'SELECT * FROM `User` WHERE `Hash` = :Hash';
      $stmt = $pdo -> prepare($sql);
      $stmt->bindParam(':Hash', $hash, PDO::PARAM_STR);
      $stmt->execute();
      foreach ($stmt as $row) {
        $count++;
      }
      if($count === 0){
        $sql = 'INSERT INTO `User`(`Hash`, `FriendCode`, `UserName`, `Json`) VALUES (:Hash, :FriendCode, :UserName, :Json)';
        $stmt = $pdo -> prepare($sql);
        $stmt->bindParam(':Hash', $hash, PDO::PARAM_STR);
        $stmt->bindParam(':FriendCode', $FriendCode, PDO::PARAM_STR);
        $stmt->bindParam(':UserName', $UserName, PDO::PARAM_STR);
        $stmt->bindParam(':Json', $Json, PDO::PARAM_STR);
        $stmt->execute();
      }else{
        $sql = 'DELETE FROM `User` WHERE `Hash` =:Hash;';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':Hash', $hash, PDO::PARAM_STR);
        $stmt->execute();

        $sql = 'INSERT INTO `User`(`Hash`, `FriendCode`, `UserName`, `Json`)  VALUES (:Hash, :FriendCode, :UserName, :Json)';
        $stmt = $pdo -> prepare($sql);
        $stmt->bindParam(':Hash', $hash, PDO::PARAM_STR);
        $stmt->bindParam(':FriendCode', $FriendCode, PDO::PARAM_STR);
        $stmt->bindParam(':UserName', $UserName, PDO::PARAM_STR);
        $stmt->bindParam(':Json', $Json, PDO::PARAM_STR);
        $stmt->execute();
      }
    } catch (PDOException $e) {
      exit('データベース接続失敗。'.$e->getMessage());
    }
  }

//-----------------------------------------------------
//  データーベースから参照
//-----------------------------------------------------

  function UserData_show($Hash){
    $pdo = new PDO(DNS,USER,PASS,array(PDO::ATTR_EMULATE_PREPARES => false));
    $sql = 'SELECT * FROM `User` WHERE `Hash` = :Hash';
    $stmt = $pdo -> prepare($sql);
    $stmt->bindParam(':Hash', $Hash, PDO::PARAM_STR);
    $stmt->execute();
    foreach ($stmt as $row) {
      return $row['Json'];
    }
    return null;
  }

//-----------------------------------------------------
//  スコアからランク
//-----------------------------------------------------

  function Score_to_rank($score){
    if($score >= 1007500){
      return 'sss';
    }
    else if($score >= 1000000){
      return 'ss';
    }
    else if($score >= 975000){
      return 's';
    }
    else if($score >= 950000){
      return 'aaa';
    }
    else if($score >= 925000){
      return 'aa';
    }
    else if($score >= 900000){
      return 'a';
    }
    else if($score >= 800000){
      return 'bbb';
    }
    else if($score >= 700000){
      return 'bb';
    }
    else if($score >= 600000){
      return 'b';
    }
    else if($score >= 500000){
      return 'c';
    }
    else if($score >= 0){
      return 'd';
    }
    else{
      return null;
    }
  }

//-----------------------------------------------------
//  スコアからレート
//-----------------------------------------------------

  function score_to_rate($score,$base_rate){
    if($score >= 1007500){
      return Truncation((double)($base_rate+2),2);
    }
    else if($score >= 1005000){
      return Truncation((double)($base_rate+1.5+($score-1005000)*10/50000),2);
    }
    else if($score >= 1000000){
      return Truncation((double)($base_rate+1+($score-1000000)*5/50000),2);
    }
    else if($score >= 975000){
      return Truncation((double)($base_rate+($score-975000)*2/50000),2);
    }
    else if($score >= 950000){
      return Truncation((double)($base_rate-1.5+($score-950000)*3/50000),2);
    }
    else if($score >= 925000){
      return Truncation((double)($base_rate-3+($score-925000)*3/50000),2);
    }
    else if($score >= 900000){
      return Truncation((double)($base_rate-5+($score-900000)*4/50000),2);
    }
    else if($score >= 800000){
      return Truncation((double)($base_rate-7.5+($score-800000)*1.25/50000),2);
    }
    else if($score >= 700000){
      return Truncation((double)($base_rate-8.5+($score-700000)*0.5/50000),2);
    }
    else if($score >= 600000){
      return Truncation((double)($base_rate-9+($score-600000)*0.25/50000),2);
    }
    else if($score >= 500000){
      return Truncation((double)($base_rate-13.7+($score-500000)*2.35/50000),2);
    }
    else{
      return null;
    }
  }

//-----------------------------------------------------
//  レートからスコア
//-----------------------------------------------------

  function rate_to_score($rate,$base_rate){
    if($rate-$base_rate >= 2){
      return 0;
    }
    else if($rate-$base_rate >= 1.5){
      return floor(-50000/10*($base_rate+1.5-$rate)+1005000);
    }
    else if($rate-$base_rate >= 1){
      return floor(-50000/5*($base_rate+1-$rate)+1000000);
    }
    else if($rate-$base_rate >= 0){
      return floor(-50000/2*($base_rate-$rate)+975000);
    }
    else if($rate-$base_rate >= -1.5){
      return floor(-50000/3*($base_rate-1.5-$rate)+950000);
    }
    else if($rate-$base_rate >= -3){
      return floor(-50000/3*($base_rate-3-$rate)+925000);
    }
    else if($rate-$base_rate >= -5){
      return floor(-50000/4*($base_rate-5-$rate)+900000);
    }
    else if($rate-$base_rate >= -7.5){
      return floor(-50000/1.25*($base_rate-7.5-$rate)+800000);
    }
    else if($rate-$base_rate >= -8.5){
      return floor(-50000/0.5*($base_rate-8.5-$rate)+700000);
    }
    else if($rate-$base_rate >= -9){
      return floor(-50000/0.25*($base_rate-9-$rate)+600000);
    }
    else if($rate-$base_rate >= -13.7){
      return floor(-50000/2.35*($base_rate-13.7-$rate)+500000);
    }
    else{
      return null;
    }
  }

//--------------------------------------------------------------------
// ユーザーデータの表示
//--------------------------------------------------------------------

function UserRateDisp($UserData){
  $UserRate = $UserData['User'];
  $UserInfo = $UserData['Userinfo'];
  $frame = ['normal', 'copper', 'silver', 'gold', 'platina'];
  $characterFrame = ['normal', 'copper', 'silver', 'gold', 'gold', 'platina'];
  $characterFrameFile = 'charaframe_'.$characterFrame[$UserInfo['characterLevel']/5].'.png';
  $elements = '
  <div id="wrap">
    <div style="margin-top:10px;margin-bottom:0px;padding-bottom:0px;">
      <div class="frame01 w460">
        <div style="padding-bottom:0px;" class="frame01_inside w450">
          <h2 style="margin-top:10px;" id="page_title">ユーザー</h2>
          <hr class="line_dot_black w420">
          <div id="userInfo_result">
            <div class="w420 box_player clearfix">
              <div id="UserCharacter" class="player_chara" style=\'background-image:url("https://chunithm-net.com/mobile/common/images/'.$characterFrameFile.'");margin-top: 10px;\'>
                <img id="characterFileName" src="https://chunithm-net.com/mobile/'.$UserInfo["characterFileName"].'">
              </div>
              <div class="box07 player_data">
                <div id="UserHonor" class="player_honor" style=\'background-image:url("https://chunithm-net.com/mobile/common/images/honor_bg_'.$frame[(int)$UserInfo["trophyType"]]. '.png")\'>
                  <div class="player_honer_text_view">
                    <div id="HonerText" class="player_honer_text">'.$UserInfo["trophyName"].'</div>
                  </div>
                </div>';
  if($UserInfo['reincarnationNum'] > 0){
    $elements .= '
                <div id="UserReborn" class="player_reborn">';
    $elements .= $UserInfo['reincarnationNum'];
  }else{
    $elements .= '
                <div id="UserReborn" class="player_reborn_0">';                  
  }
  $elements .= '
              </div>
              <div class="player_name">
                <div class="player_lv">
                  <span class="font_small mr_5">Lv.</span><span id="UserLv">'.$UserInfo["level"].'</span></div><span id="UserName">'.$UserInfo["userName"].'</span>
                </div>
                <div class="player_rating" id="player_rating">BEST枠 : <span id="UserRating">'.$UserRate["BestRate"].'</span> / <span>MAX</span> <span id="UserRating">'.$UserRate["MaxRate"].'</span><br><div style="margin-top:5px;">RECENT枠 :<span id="UserRating">'.$UserRate["RecentRate-1"].'</span> / <span>表示レート</span><span id="UserRating">'.$UserRate["DispRate"].'</span></div>
              </div>
            </div>
            <div id="tweet" class="text_b" style="margin-top: 10px;"></div>
            <div class="more w400" ><a href="/#notice">注意</a></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://platform.twitter.com/widgets.js" ></script>
<script type="text/javascript">
//tweetボタン
  twttr.ready(function() {
    var rate = "BEST枠平均: "+'.$UserRate["BestRate"].'+" 最大レート: "+'.$UserRate["MaxRate"].'+"\n" + "RECENT枠平均: "+'.$UserRate["RecentRate-1"].'+" 表示レート: "+'.$UserRate["DispRate"].'+"\n";
    twttr.widgets.createShareButton(
      document.URL,
      location.href,
      document.getElementById(\'tweet\'),
      {
        lang: \'ja\',
        size: \'normal\',
        text:  rate,
        hashtags : \'CHUNITHMRateCalculator\'
      }
    )
  });
</script>';
  return $elements;
}

//--------------------------------------------------------------------
// Best枠の表示
//--------------------------------------------------------------------

function BestRateDisp($UserData){
  $element = '';
  $Best = '
  <div id="wrap">
    <div id="disp">
      <div style="margin-bottom:0px;padding-bottom:0px;" id="inner">
        <div class="frame01 w460">
          <div class="frame01_inside w450">
            <h2 style="margin-top:10px;" id="page_title">BEST枠</h2>
            <hr class="line_dot_black w420">
            <div class="box01 w420">
              <div class="mt_10">
                <div id="userPlaylog_result">';
      //Best枠の数だけ繰り返す
  for($i = 0; $i < sizeof($UserData['Best']); $i++){
    $MusicDeteil = $UserData['Best'][$i];
    $MusicName = $MusicDeteil['MusicName'];
    $MusicImg = $MusicDeteil['Images'];
    $BaseRate = $MusicDeteil['BaseRate'];
    $Score = $MusicDeteil['Score'];
    $Rank = $MusicDeteil['Rank'];
    $BestRate = $MusicDeteil['BestRate'];
    $level = $MusicDeteil['level'];
    $BestScore = $MusicDeteil['ScoreBest'];
      
    if($i == 30){
        $element .= '
                </div>
              </div>
            </div>
          </div>
        </div>
        <p style="font-size:15px">スポンサーリンク</p>
        <!-- Chunical.5 -->
        <ins class="adsbygoogle"
             style="display:inline-block;width:320px;height:100px"
             data-ad-client="ca-pub-9431951784509175"
             data-ad-slot="4179909243"></ins>
        <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
        <div class="frame01 w460">
          <div class="frame01_inside w450">
            <h2 style="margin-top:10px;" id="page_title">BEST枠外</h2>
            <hr class="line_dot_black w420">
            <div class="box01 w420">
              <div class="mt_10">
                <div id="userPlaylog_result">';
    }
    $element .= '
                  <div class="frame02 w400">
                    <div class="play_jacket_side">
                      <div class="play_jacket_area">
                        <div id="Jacket" class="play_jacket_img">
                          <img src="https://chunithm-net.com/mobile/'.$MusicImg.'">
                        </div>
                      </div>
                    </div>
                    <div class="play_data_side01">
                      <div class="box02 play_track_block">
                        <div id="TrackLevel" class="play_track_result">
                          <img src="https://chunithm-net.com/mobile/common/images/icon_'.$level.'.png">
                        </div>
                      </div>
                      <div class="box02 play_musicdata_block">
                        <div id="MusicTitle" class="play_musicdata_title">'.$MusicName.'</div>
                        <div class="play_musicdata_score clearfix">
                          <div class="play_musicdata_score_text">譜面定数:<span id="Score">'.$BaseRate.'</span></div><br>
                          <div class="play_musicdata_score_text">RATING:<span id="Score">'.$BestRate.'</span></div><br>
                          <div class="play_musicdata_score_text">Score:<span id="Score">'.$Score.'</span></div>
                          <div id="rank"><img src="https://chunithm-net.com/mobile/common/images/icon_'.$Rank.'.png"></div>';
    if($i > 29 && $BestScore != 0){
      $element .= '
                          <div class="play_musicdata_score_text">Best枠入りまで : <span id="Score">'.($BestScore-$Score).'('.$BestScore.')</span></div>';
    }
    $element .= '
                        </div>
                      </div>
                    </div>
                  </div>
                ';            
  }
  $element .='</div>
              </div>
            </div>
            </div>
            </div>';
  $Best .= $element;
  return $Best;
}

//スコア順にソート
function Sort_Score($UserData){
  $Score_array = $UserData['Best'];
  array_multisort(array_column($Score_array, 'Score'), SORT_DESC, $Score_array);  
  $element = '';
  $rank = $Score_array[0]['Rank'];
  $sort_Score = '
  <div id="wrap">
    <div id="disp">
      <div style="margin-bottom:0px;padding-bottom:0px;" id="inner">
        <div class="frame01 w460">
          <div class="frame01_inside w450">
            <h2 style="margin-top:10px;" id="page_title">'.strtoupper($rank).'</h2>
            <hr class="line_dot_black w420">
            <div class="box01 w420">
              <div class="mt_10">
                <div id="userPlaylog_result">';
    //Best枠の数だけ繰り返す
  for($i = 0; $i < sizeof($Score_array); $i++){
    $MusicDeteil = $Score_array[$i];
    $MusicName = $MusicDeteil['MusicName'];
    $MusicImg = $MusicDeteil['Images'];
    $BaseRate = $MusicDeteil['BaseRate'];
    $Score = $MusicDeteil['Score'];
    $Rank = $MusicDeteil['Rank'];
    $BestRate = $MusicDeteil['BestRate'];
    $level = $MusicDeteil['level'];      
    if($rank != $Rank){
      $rank = $Rank;
      $element .= '
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="frame01 w460">
          <div class="frame01_inside w450">
            <h2 style="margin-top:10px;" id="page_title">'.strtoupper($rank).'</h2>
            <hr class="line_dot_black w420">
            <div class="box01 w420">
              <div class="mt_10">
                <div id="userPlaylog_result">';
    }
    $element .= '
                  <div class="frame02 w400">
                    <div class="play_jacket_side">
                      <div class="play_jacket_area">
                        <div id="Jacket" class="play_jacket_img">
                          <img src="https://chunithm-net.com/mobile/'.$MusicImg.'">
                        </div>  
                      </div>
                    </div>
                    <div class="play_data_side01">
                      <div class="box02 play_track_block">
                        <div id="TrackLevel" class="play_track_result">
                          <img src="https://chunithm-net.com/mobile/common/images/icon_'.$level.'.png">
                        </div>
                      </div>
                      <div class="box02 play_musicdata_block">
                        <div id="MusicTitle" class="play_musicdata_title">'.$MusicName.'</div>
                        <div class="play_musicdata_score clearfix">
                          <div class="play_musicdata_score_text">譜面定数:<span id="Score">'.$BaseRate.'</span></div><br>
                          <div class="play_musicdata_score_text">RATING:<span id="Score">'.$BestRate.'</span></div><br>
                          <div class="play_musicdata_score_text">Score:<span id="Score">'.$Score.'</span></div>
                          <img src="https://chunithm-net.com/mobile/common/images/icon_'.$Rank.'.png">
                        </div>
                      </div>
                    </div>
                  </div>';
  }
  $element .='</div>
              </div>
            </div>
          </div>
        </div>';  
  $sort_Score .= $element;
  return $sort_Score;
}

//難易度を返す
function difficult($n){
  if($n >= 13.7) return '13+';
  if($n >= 13) return '13';
  if($n >= 12.7) return '12+';
  if($n >= 12) return '12';
  if($n >= 11.7) return '11+';
  if($n >= 11) return '11';
}

//難易度順にソート
function Sort_Diff($UserData){
  $Diff_array = $UserData['Best'];
  array_multisort(array_column($Diff_array, 'BaseRate'), SORT_DESC, $Diff_array);
  $element = '';
  $Diff = difficult($Diff_array[0]['BaseRate']);
  $sort_Diff = '
  <div id="wrap">
    <div id="disp">
      <div style="margin-bottom:0px;padding-bottom:0px;" id="inner">
        <div class="frame01 w460">
          <div class="frame01_inside w450">
            <h2 style="margin-top:10px;" id="page_title">'.$Diff.'</h2>
            <hr class="line_dot_black w420">
            <div class="box01 w420">
              <div class="mt_10">
                <div id="userPlaylog_result">';
  //Best枠の数だけ繰り返す
  for($i = 0; $i < sizeof($Diff_array); $i++){
    $MusicDeteil = $Diff_array[$i];
    $MusicName = $MusicDeteil['MusicName'];
    $MusicImg = $MusicDeteil['Images'];
    $BaseRate = $MusicDeteil['BaseRate'];
    $Score = $MusicDeteil['Score'];
    $Rank = $MusicDeteil['Rank'];
    $BestRate = $MusicDeteil['BestRate'];
    $level = $MusicDeteil['level'];      
    if($Diff != difficult($BaseRate)){
      $Diff = difficult($BaseRate);
      $element .= '
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="frame01 w460">
          <div class="frame01_inside w450">
            <h2 style="margin-top:10px;" id="page_title">'.$Diff.'</h2>
            <hr class="line_dot_black w420">
            <div class="box01 w420">
              <div class="mt_10">
                <div id="userPlaylog_result">';
    }
    $element .= '
                  <div class="frame02 w400">
                    <div class="play_jacket_side">
                      <div class="play_jacket_area">
                        <div id="Jacket" class="play_jacket_img">
                          <img src="https://chunithm-net.com/mobile/'.$MusicImg.'">
                        </div>  
                      </div>
                    </div>
                    <div class="play_data_side01">
                      <div class="box02 play_track_block">
                        <div id="TrackLevel" class="play_track_result">
                          <img src="https://chunithm-net.com/mobile/common/images/icon_'.$level.'.png">
                        </div>
                      </div>
                      <div class="box02 play_musicdata_block">
                        <div id="MusicTitle" class="play_musicdata_title">'.$MusicName.'</div>
                        <div class="play_musicdata_score clearfix">
                          <div class="play_musicdata_score_text">譜面定数:<span id="Score">'.$BaseRate.'</span></div><br>
                          <div class="play_musicdata_score_text">RATING:<span id="Score">'.$BestRate.'</span></div><br>
                          <div class="play_musicdata_score_text">Score:<span id="Score">'.$Score.'</span></div>
                          <img src="https://chunithm-net.com/mobile/common/images/icon_'.$Rank.'.png">
                        </div>
                      </div>
                    </div>
                  </div>';
  } 
  $element .='</div>
              </div>
            </div>
          </div>
        </div>';  
  $sort_Diff .= $element;
  return $sort_Diff;
}

//--------------------------------------------------------------------
// Recent枠の表示
//--------------------------------------------------------------------

function RecentRateDisp($UserData){
  $element = "";
  $Recent = '
  <div id="wrap">
    <div id="disp">
      <div style="margin-bottom:0px;padding-bottom:0px;" id="inner">
        <div class="frame01 w460">
          <div class="frame01_inside w450">
            <h2 style="margin-top:10px;" id="page_title">RECENT枠</h2>
            <hr class="line_dot_black w420">
            <div class="box01 w420">
              <div class="mt_10">
                <div id="userPlaylog_result">';
  //Best枠の数だけ繰り返す
  for($i = 0; $i < sizeof($UserData["Recent"]); $i++){
    $MusicDeteil = $UserData["Recent"][$i];
    $MusicName = $MusicDeteil["MusicName"];
    $MusicImg = $MusicDeteil["Images"];
    $BaseRate = $MusicDeteil["BaseRate"];
    $Score = $MusicDeteil["Score"];
    $Rank = $MusicDeteil["Rank"];
    $BestRate = $MusicDeteil["BestRate"];
    $level = $MusicDeteil["level"];
    if($i == 10){
      $element .= '
          </div>
        </div>
      </div>
    </div>
  </div>
  <p style="font-size:15px">スポンサーリンク</p>
    <!-- Chunical.5 -->
    <ins class="adsbygoogle"
      style="display:inline-block;width:320px;height:100px"
      data-ad-client="ca-pub-9431951784509175"
      data-ad-slot="4179909243"></ins>
    <script>
      (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
  <div class="frame01 w460">
    <div class="frame01_inside w450">
      <h2 style="margin-top:10px;" id="page_title">RECENT枠外</h2>
      <hr class="line_dot_black w420">
      <div class="box01 w420">
        <div class="mt_10">
          <div id="userPlaylog_result">';
    }
    $element .= '
              <div class="frame02 w400">
                <div class="play_jacket_side">
                  <div class="play_jacket_area">
                    <div id="Jacket" class="play_jacket_img">
                      <img src="https://chunithm-net.com/mobile/'.$MusicImg.'">
                    </div>
                  </div>
                </div>
                <div class="play_data_side01">
                  <div class="box02 play_track_block">
                    <div id="TrackLevel" class="play_track_result">
                      <img src="https://chunithm-net.com/mobile/common/images/icon_'.$level.'.png">
                    </div>
                  </div>
                  <div class="box02 play_musicdata_block">
                    <div id="MusicTitle" class="play_musicdata_title">'.$MusicName.'</div>
                    <div class="play_musicdata_score clearfix">
                      <div class="play_musicdata_score_text">譜面定数:<span id="Score">'.$BaseRate.'</span></div><br>
                      <div class="play_musicdata_score_text">RATING:<span id="Score">'.$BestRate.'</span></div><br>
                      <div class="play_musicdata_score_text">Score:<span id="Score">'.$Score.'</span></div>
                      <img src="https://chunithm-net.com/mobile/common/images/icon_'.$Rank.'.png">
                    </div>
                  </div>
                </div>
              </div>';            
  }
  $element .='</div>
              </div>
            </div>
          </div>
        </div>';  
  $Recent .= $element;
  return $Recent;
}

//ボタンの表示
function button_show(){
  echo '
  <div id="Buttons">
    <a class="buttons" href="?user='.$_GET['user'].'&frame=Best">Best枠</a>
    <a class="buttons" href="?user='.$_GET['user'].'&frame=Recent">Recent枠</a>
    <a class="buttons" href="?user='.$_GET['user'].'&frame=Graph">グラフ</a>
    <a class="buttons" href="?user='.$_GET['user'].'&frame=Tools">ツール</a>
  </div>';
}
function sort_button_show(){
  echo '
  <div id="Sorts_Button">
    <hr class="line_dot_black w420"/>
    <a class="buttons" href="?user='.$_GET['user'].'&frame=Best&sort=Sort_Rate">レート順</a>
    <a class="buttons" href="?user='.$_GET['user'].'&frame=Best&sort=Sort_Score">スコア順</a>
    <a class="buttons" href="?user='.$_GET['user'].'&frame=Best&sort=Sort_Diff">難易度順</a>
  </div>';
}

//--------------------------------------------------------------------
// ツール
//--------------------------------------------------------------------

function Tools($tools){
  if(isset($tools['score'])){
    if($tools['score'] !== -1){      
      $score = $tools['score'];
      $baserate = $tools['baserate'];
      $recentrate = Truncation(score_to_rate($score,$baserate),2);
      $bestrate = $tools['bestrate'];
      $maxrate = Truncation(($bestrate*30+$recentrate*10)/40,2);
    }
  }
  else{
    $score = 0;
    $baserate = 0;
    $bestrate = 0;
    $maxrate = 0;
  }
  
  $tool = '
  <script type="text/javascript">
  function GetQueryString(){
    var result = {};
    if( 1 < window.location.search.length ){
      var query = window.location.search.substring( 1 );
      var parameters = query.split( \'&\' );
      for( var i = 0; i < parameters.length; i++ ){
        var element = parameters[ i ].split( \'=\' );
        var paramName = decodeURIComponent( element[ 0 ] );
        var paramValue = decodeURIComponent( element[ 1 ] );
        result[ paramName ] = paramValue;
      }
    }
    return result;
  }
    function get(){
      var baserate = document.getElementsByTagName("input")[0].value;
      var score = document.getElementsByTagName("input")[1].value;
      var user = GetQueryString();
      user = user["user"];
      url = "?user="+user+"&frame=Tools&score="+score+"&baserate="+baserate;
      location.href = url;
    }
  </script>
  <div id="wrap">
    <div id="disp">
      <div style="margin-bottom:0px;padding-bottom:0px;" id="inner">
        <div class="frame01 w460">
          <div class="frame01_inside w450">
            <h2 id="page_title" style="margin-top:10px">ツール</h2>
            <hr class="line_dot_black w420">
            <div class="box02 w420 mb_20">
              <div class="text_c mt_15">シミュレータ</div>
              <hr>
              <div class="narrow_block clearfix">
                <div id="simue">
                  <span>譜面定数</span>
                  <input id="baserate" type="text" style="width:70px" value="'.$baserate.'"/>
                  <span>の曲をスコア</span>
                  <input id="score" type="text" style="width:90px" value="'.$score.'"/>
                  <br>
                  <p>でRECENT枠を埋めたとき<button class="buttons" style="margin-left:10px" onClick="get();">計算</button></p>
                  <span>到達可能レート</span>
                  <input id="maxrate" type="text" style="width:70px" value="'.$maxrate.'">
                </div>                
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>';
  return $tool;
}

//--------------------------------------------------------------------
// グラフ描画
//--------------------------------------------------------------------

function graph($UserData) {
  
  if(sizeof($UserData["Date"]["date"]) === 1){
    $MaxRate = '[['.$UserData["Date"]["date"][0].','.$UserData["Date"]["MaxRate"][0].']]';
    $DispRate = '[['.$UserData["Date"]["date"][0].','.$UserData["Date"]["DispRate"][0].']]';
    $BestRate = '[['.$UserData["Date"]["date"][0].','.$UserData["Date"]["BestRate"][0].']]';
    $RecentRate = '[['.$UserData["Date"]["date"][0].','.$UserData["Date"]["RecentRate"][0].']]';
  }
  else{
    $tmp = [];
    for($i = 0; $i < sizeof($UserData["Date"]["MaxRate"]); $i++){
      $tmp[$i] = '['.$UserData["Date"]["date"][$i].','.$UserData["Date"]["MaxRate"][$i].']';
    }
    $MaxRate = '[';
    for($i = 0; $i < sizeof($tmp); $i++){
      $MaxRate .= $tmp[$i].',';
    }
    $MaxRate .= ']';

    $tmp = [];
    for($i = 0; $i < sizeof($UserData["Date"]["DispRate"]); $i++){
      $tmp[$i] = '['.$UserData["Date"]["date"][$i].','.$UserData["Date"]["DispRate"][$i].']';
    }
    $DispRate = '[';
    for($i = 0; $i < sizeof($tmp); $i++){
      $DispRate .= $tmp[$i].',';
    }
    $DispRate .= ']';

    $tmp = [];
    for($i = 0; $i < sizeof($UserData["Date"]["BestRate"]); $i++){
      $tmp[$i] = '['.$UserData["Date"]["date"][$i].','.$UserData["Date"]["BestRate"][$i].']';
    }
    $BestRate = '[';
    for($i = 0; $i < sizeof($tmp); $i++){
      $BestRate .= $tmp[$i].',';
    }
    $BestRate .= ']';

    $tmp = [];
    for($i = 0; $i < sizeof($UserData["Date"]["RecentRate"]); $i++){
      $tmp[$i] = '['.$UserData["Date"]["date"][$i].','.$UserData["Date"]["RecentRate"][$i].']';
    }
    $RecentRate = '[';
    for($i = 0; $i < sizeof($tmp); $i++){
      $RecentRate .= $tmp[$i].',';
    }
    $RecentRate .= ']';
  }
  $graph = '
<div id="Graph" style="margin-bottom:10px;width:650px"></div>
<script src="/common/js/jquery-3.1.1.min.js" ></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $(\'#Graph\')
    .highcharts({
      title: {
        text: \'レート推移\'
        , x: -20 //center
      }
      , xAxis: {
        title: {
          text: \'クレジット\'
        },
          tickInterval: 10
      }
      , yAxis: {
        title: {
          text: \'レート\'
        }
        , plotLines: [{
          value: 0
          , width: 1
          , color: \'#808080\'
            }]
            ,
            tickInterval: 0.1
      }
      , legend: {
        layout: \'vertical\'
        , align: \'right\'
        , verticalAlign: \'middle\'
        , borderWidth: 0
      }
      , series: [{
          name: \'最大レート\',
          data: '.$MaxRate.'
        }, {
        name: \'表示レート\'
        , data: '.$DispRate.'
        }, {
        name: \'BEST枠\'
        , data: '.$BestRate.'
        }, {
        name: \'RECENT枠\'
        , data: '.$RecentRate.'
        }]
    });
  });
</script>';

    return $graph;
}

?>
