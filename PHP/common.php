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
//  エラーハンドラ
//-----------------------------------------------------

  function errorHandler($errno, $errstr, $errfile, $errline)
  {
    if($errno === E_NOTICE) {
      header("HTTP/1.1 301 Moved Permanently");
      header("Location: https://akashisn.info/chunithm/error.html");
      exit();
    }
  }

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
    $a = explode(";",$u);
    $b = "";
    for($i = 0;$i< count($a);$i++){
      $pos = strpos($a[$i],'userId');
      if ($pos !== false) {
        $b = $i;
      }
    }
    $c = explode("=",$a[$b]);
    if(isset($c[1])){
      return $c[1];
    }
    else{
      return null;
    }
  }

//-----------------------------------------------------
//  楽曲の詳細データ取得
//-----------------------------------------------------

  function score_get($userid,$musicId){
    $url = 'https://chunithm-net.com/ChuniNet/GetUserMusicDetailApi';
    $data = array(
      'userId' => $userid,
      'musicId' => $musicId,
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
//  データーベースに登録
//-----------------------------------------------------

  function UserData_set($FriendCode,$UserName,$Json){
    try {
      $count = 0;
      $hash =  hash_hmac('sha256', $FriendCode, false);
      $pdo = new PDO(DNS,array(PDO::ATTR_EMULATE_PREPARES => false));
      $sql = 'SELECT * from User';
        foreach ($pdo ->query($sql) as $row) {
          if($hash == $row['Hash']){
              $count++;
          }
        }
        if($count == 0){
          $sql = "INSERT INTO User (Hash, FriendCode, UserName, Json) VALUES (:Hash, :FriendCode, :UserName, :Json)";
        $stmt = $pdo -> prepare($sql);
        $stmt->bindParam(':Hash', $hash, PDO::PARAM_STR);
        $stmt->bindParam(':FriendCode', $FriendCode, PDO::PARAM_STR);
        $stmt->bindParam(':UserName', $UserName, PDO::PARAM_STR);
        $stmt->bindParam(':Json', $Json, PDO::PARAM_STR);
        $stmt->execute();
      }else{
        $sql = "UPDATE User SET UserName = :UserName, Json = :Json WHERE Hash = :Hash";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':UserName', $UserName, PDO::PARAM_STR);
        $stmt->bindParam(':Json', $Json, PDO::PARAM_STR);
        $stmt->bindParam(':Hash', $Hash, PDO::PARAM_STR);
        $stmt->execute($params);
      }
    } catch (PDOException $e) {
      exit('データベース接続失敗。'.$e->getMessage());
    }
  }

//-----------------------------------------------------
//  データーベースから参照
//-----------------------------------------------------

  function UserData_show($Hash){
    $pdo = new PDO(DNS,array(PDO::ATTR_EMULATE_PREPARES => false));
    $sql = 'SELECT * from User';
    foreach ($pdo ->query($sql) as $row) {
      if($Hash == $row['Hash']){
        return $row['Json'];
      }
    }
  }

//-----------------------------------------------------
//  スコアからランク
//-----------------------------------------------------

  function Score_to_rank($score){
    if($score >= 1007500){
      return "sss";
    }
    else if($score >= 1000000){
      return "ss";
    }
    else if($score >= 975000){
      return "s";
    }
    else if($score >= 950000){
      return "aaa";
    }
    else if($score >= 925000){
      return "aa";
    }
    else if($score >= 900000){
      return "a";
    }
    else if($score >= 800000){
      return "bbb";
    }
    else if($score >= 700000){
      return "bb";
    }
    else if($score >= 600000){
      return "b";
    }
    else if($score >= 500000){
      return "c";
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
      return (double)($base_rate+2);
    }
    else if($score >= 1005000){
      return (double)($base_rate+1.5+($score-1005000)*10/50000);
    }
    else if($score >= 1000000){
      return (double)($base_rate+1+($score-1000000)*5/50000);
    }
    else if($score >= 975000){
      return (double)($base_rate+($score-975000)*2/50000);
    }
    else if($score >= 950000){
      return (double)($base_rate-1.5+($score-950000)*3/50000);
    }
    else if($score >= 925000){
      return (double)($base_rate-3+($score-925000)*3/50000);
    }
    else if($score >= 900000){
      return (double)($base_rate-5+($score-900000)*4/50000);
    }
    else if($score >= 800000){
      return (double)($base_rate-7.5+($score-800000)*1.25/50000);
    }
    else if($score >= 700000){
      return (double)($base_rate-8.5+($score-700000)*0.5/50000);
    }
    else if($score >= 600000){
      return (double)($base_rate-9+($score-600000)*0.25/50000);
    }
    else if($score >= 500000){
      return (double)($base_rate-13.7+($score-500000)*2.35/50000);
    }
    else{
      return null;
    }
  }
?>
