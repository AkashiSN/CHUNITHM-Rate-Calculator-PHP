<?php
/*
  +---------------------------------------------------+
  | CHUNITHM Rate Calculator               [main.php] |
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

  session_start();
  require("common.php");

//-----------------------------------------------------
// 受信部
//-----------------------------------------------------

  if(isset($_POST["hash"])){
    $hash = $_POST["hash"];
    $json = UserData_show($hash);

    if($json === null){      
      http_response_code(403);
      exit();
    }
    
    //出力
    header("Content-Type: text/javascript; charset=utf-8");
    echo $json;
    exit();
  }
  if(isset($_POST["userid"])){
    $userid = $_POST["userid"];
  }
  else{
    //パラメータが不適切
    http_response_code(400);
    exit();
  }

//-----------------------------------------------------
// 宣言部
//-----------------------------------------------------

  $Img_to_MusicID = [];
  $MusicDetail = [];
  $UserBestRate = 0;
  $UserMaxRate = 0;
  $UserRecentRate = 0;
  $UserDisplayRate = 0;
  $MaxRate = 0;

//-----------------------------------------------------
// JSON読み込み
//-----------------------------------------------------

  $json = file_get_contents("lib/chunithm.json");
  $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
  $data = json_decode($json,true);
  $MusicIDArray = $data["MusicIDList"];
  //ソート
  sort($MusicIDArray);

//-----------------------------------------------------
// Best枠
//-----------------------------------------------------

  //宣言
  $BestRate_to_Musicid = [];
  $Score_to_Musicid = [];
  $Musics = 0;

  $MusicBestScore_Mas = BestScore_get($userid,19903); //マスターの取得
  $MusicBestScore_Exp = BestScore_get($userid,19902); //エキスパートの取得

  //エラー判定
  if($MusicBestScore_Mas === null || $MusicBestScore_Exp === null){
    http_response_code(204);
    exit();
  }

  //登録されてる楽曲の数だけ繰り返す
  for($i = 0; $i < sizeof($MusicIDArray); ++$i){
    //ImageからMusicIDの配列
    $Img_to_MusicID[$data[(string)$MusicIDArray[$i]]['Images']] = $MusicIDArray[$i];
    
    //エキスパートを順に確認
    for($j = 0; $j < sizeof($MusicBestScore_Exp['userMusicList']); ++$j){
      //リストに乗っている楽曲の場合
      if($MusicBestScore_Exp['userMusicList'][$j]['musicId'] === $MusicIDArray[$i]){
        //リストにエキスパートがあるか
        if(isset($data[(string)$MusicIDArray[$i]]['BaseRate']['ex'])){
          $BestRate_to_Musicid[-$MusicIDArray[$i]] = score_to_rate($MusicBestScore_Exp['userMusicList'][$j]['scoreMax'],$data[(string)$MusicIDArray[$i]]['BaseRate']['ex']);
          $Score_to_Musicid[-$MusicIDArray[$i]] = $MusicBestScore_Exp['userMusicList'][$j]['scoreMax'];
          ++$Musics;
        }
      }
    }

    //マスターを順に確認
    for($j = 0; $j < sizeof($MusicBestScore_Mas['userMusicList']); ++$j){
      //リストに乗っている楽曲の場合
      if($MusicBestScore_Mas['userMusicList'][$j]['musicId'] === $MusicIDArray[$i]){
        $BestRate_to_Musicid[$MusicIDArray[$i]] = score_to_rate($MusicBestScore_Mas['userMusicList'][$j]['scoreMax'],$data[(string)$MusicIDArray[$i]]['BaseRate']['mas']);
        $Score_to_Musicid[$MusicIDArray[$i]] = $MusicBestScore_Mas['userMusicList'][$j]['scoreMax'];
        ++$Musics;
      }
    }
  }

  //30種類以下だった場合
  if($Musics < 30){
  	http_response_code(204);
    exit();
  }

  //レート値でMusicIDを降順にソート
  arsort($BestRate_to_Musicid);

  //カウンタ変数
  $i = 0;

  //初期化
  $rate = 0;
  $BestRateMin = 0;

  //jsonに書き出し
  foreach ($BestRate_to_Musicid as $musicid => $rate){
    //エキスパートの場合
    if($musicid < 0){
      //連想配列に代入
      $Temp["MusicID"] = -$musicid;
      $Temp["level"] = "expert";
      $Temp["MusicName"] = $data[(string)-$musicid]["MusicName"];
      $Temp["Images"] = $data[(string)-$musicid]["Images"];
      $Temp["BaseRate"] = $data[(string)-$musicid]["BaseRate"]["ex"];
      $Temp["Score"] = $Score_to_Musicid[$musicid];
      $Temp["Rank"] = Score_to_rank($Score_to_Musicid[$musicid]);
      $Temp["BestRate"] = Truncation($rate,2);

      //上位30曲
      if($i < 30){
        $UserBestRate += Truncation($rate,2);
        $Temp["ScoreBest"] = 0;
        //上位1曲
        if($i === 0){
          $MaxRate = Truncation($rate,2);
        }
        if($i === 29){
          $BestRateMin = $rate;
        }
      }
      else{
      	if(Score_to_rank($Score_to_Musicid[$musicid]) === "sss"){
	        $Temp["ScoreBest"] = 0;
	      }
	      else{
	      	$BestScore = rate_to_score($BestRateMin,$Temp["BaseRate"]);    
	        if($BestScore <= 1007500){
	        	$Temp["ScoreBest"] = $BestScore;
	        }
	        else{
	        	$Temp["ScoreBest"] = 0;
	        }
	    	}
	    }
	    $MusicDetail["Best"][] = $Temp;
	    $Temp = "";
	    ++$i;
    }
    //マスターの場合
    else{
      //連想配列に代入
      $Temp["MusicID"] = $musicid;
      $Temp["level"] = "master";
      $Temp["MusicName"] = $data[(string)$musicid]["MusicName"];
      $Temp["Images"] = $data[(string)$musicid]["Images"];
      $Temp["BaseRate"] = $data[(string)$musicid]["BaseRate"]["mas"];
      $Temp["Score"] = $Score_to_Musicid[$musicid];
      $Temp["Rank"] = Score_to_rank($Score_to_Musicid[$musicid]);
      $Temp["BestRate"] = Truncation($rate,2);

      //上位30曲
      if($i < 30){
        $UserBestRate += Truncation($rate,2);
        $Temp["ScoreBest"] = 0;
        //上位1曲
        if($i === 0){
          $MaxRate = Truncation($rate,2);
        }
        if($i === 29){
          $BestRateMin = $rate;
        }
      }
      else{
      	if(Score_to_rank($Score_to_Musicid[$musicid]) === "sss"){
	        $Temp["ScoreBest"] = 0;
	      }
	      else{
	      	$BestScore = rate_to_score($BestRateMin,$Temp["BaseRate"]);    
	        if($BestScore <= 1007500){
	        	$Temp["ScoreBest"] = $BestScore;
	        }
	        else{
	        	$Temp["ScoreBest"] = 0;
	        }
	    	}
	    }
	    $MusicDetail["Best"][] = $Temp;
	    $Temp = "";
	    ++$i;
	  }
  }

//-----------------------------------------------------
// Recent枠
//-----------------------------------------------------

  //最近の楽曲の取得
  $userPlaylogList = Recent_score_get($userid);
  if($userPlaylogList === null){
    http_response_code(204);
    exit();
  }

  //宣言
  $Count_to_Rate = [];
  $Temp = "";

  //カウンタ変数
  $j = 0;
  //リストの数だけ繰り返す
  for($i = 0; $i < sizeof($userPlaylogList["userPlaylogList"]); ++$i){
    //代入
    $img = $userPlaylogList["userPlaylogList"][$i]["musicFileName"];
    $score = $userPlaylogList["userPlaylogList"][$i]["score"];
    //マスターとエキスパートで30曲かどうか
    if($j === 30){
      break;
    }
    //エキスパートの場合
    if($userPlaylogList["userPlaylogList"][$i]["levelName"] === "expert"){
      //musicIDがあるもの
      if(isset($Img_to_MusicID[$img])){
        $musicid = $Img_to_MusicID[$img];
        //データベースにあるもの
        if(isset($data[(string)$musicid]["BaseRate"]["ex"])){
          //計算
          $base_rate = $data[(string)$musicid]["BaseRate"]["ex"];
          $rate = score_to_rate($score,$base_rate);
          $Count_to_Rate[$i] = $rate;
          //SSSはカウントされない
          if($userPlaylogList["userPlaylogList"][$i]["rank"] != 10){
            ++$j;
          }
        }
      }
    }
    //マスターの場合
    else if($userPlaylogList["userPlaylogList"][$i]["levelName"] === "master"){
      //musicIDがあるもの
      if(isset($Img_to_MusicID[$img])){
        $musicid = $Img_to_MusicID[$img];
        //データベースにあるもの
        if(isset($data[(string)$musicid]["BaseRate"]["mas"])){
          //計算
          $base_rate = $data[(string)$musicid]["BaseRate"]["mas"];
          $rate = score_to_rate($score,$base_rate);
          $Count_to_Rate[$i] = $rate;
          //SSSはカウントされない
          if($userPlaylogList["userPlaylogList"][$i]["rank"] != 10){
            ++$j;
          }
        }
      }
    }
    //エキスパートでもマスターでもない場合
    else{
      continue;
    }
  }

  //レート値でカウンターをソート
  arsort($Count_to_Rate);

  //カウンタ変数
  $i = 0;

  //配列の中を上から調べる
  foreach ($Count_to_Rate as $count => $rate){
    $img = $userPlaylogList["userPlaylogList"][$count]["musicFileName"];
    $score = $userPlaylogList["userPlaylogList"][$count]["score"];
    $name = $userPlaylogList["userPlaylogList"][$count]["musicName"];
    //エキスパートの場合
    if($userPlaylogList["userPlaylogList"][$count]["levelName"] === "expert"){
      //計算
      $musicid = $Img_to_MusicID[$img];
      $base_rate = $data[(string)$musicid]["BaseRate"]["ex"];
      //連想配列に代入
      $Temp["MusicID"] = $musicid;
      $Temp["level"] = "expert";
      $Temp["MusicName"] = $name;
      $Temp["Images"] = $img;
      $Temp["BaseRate"] = $base_rate;
      $Temp["Score"] = $score;
      $Temp["Rank"] = Score_to_rank($score);
      $Temp["BestRate"] = Truncation($rate,2);

      $MusicDetail["Recent"][] = $Temp;
      //上位10曲
      if($i < 10){
        $UserRecentRate += Truncation($rate,2);
      }
      ++$i;
      $Temp = "";
    }
    //マスターの場合
    else if($userPlaylogList["userPlaylogList"][$count]["levelName"] === "master"){
      //計算
      $musicid = $Img_to_MusicID[$img];
      $base_rate = $data[(string)$musicid]["BaseRate"]["mas"];
      //連想配列に代入
      $Temp["MusicID"] = $musicid;
      $Temp["level"] = "master";
      $Temp["MusicName"] = $name;
      $Temp["Images"] = $img;
      $Temp["BaseRate"] = $base_rate;
      $Temp["Score"] = $score;
      $Temp["Rank"] = Score_to_rank($score);
      $Temp["BestRate"] = Truncation($rate,2);

      $MusicDetail["Recent"][] = $Temp;
      //上位10曲
      if($i < 10){
        $UserRecentRate += Truncation($rate,2);
      }
      ++$i;
      $Temp = "";
    }
  }


//-----------------------------------------------------
// レート値計算
//-----------------------------------------------------

  // 表示レート取得
  $dispRate = Rate_get($userid);
  if($dispRate === null){
    http_response_code(204);
    exit();
  }
  $DispRate = $dispRate['userInfo']['playerRating'];

  //計算
  $UserDisplayRate = (double)(substr($DispRate, 0,2).'.'.substr($DispRate, 2,4));
  $UserBestRate = Truncation($UserBestRate/30,2);
  $UserMaxRate = Truncation(($MaxRate*10 + $UserBestRate*30)/40,2);
  $UserRecentRate = Truncation($UserRecentRate/10,2);
  $UserRecentRate1 = Truncation(($UserDisplayRate*40 - $UserBestRate*30)/10,2);

  //連想配列に代入
  $MusicDetail["User"]["DispRate"] = $UserDisplayRate;
  $MusicDetail["User"]["MaxRate"] = $UserMaxRate;
  $MusicDetail["User"]["RecentRate"] = $UserRecentRate;
  $MusicDetail["User"]["RecentRate-1"] = $UserRecentRate1; //逆算したもの
  $MusicDetail["User"]["BestRate"] = $UserBestRate;
  $MusicDetail["Userinfo"] = $dispRate["userInfo"];

//-----------------------------------------------------
// JSON変換
//-----------------------------------------------------

  //フレンドコード取得
  $friend = friendCode_get($userid);
  if($friend === null){    
    http_response_code(204);
    exit();
  }
  $friend = $friend["friendCode"];
  $hash =  hash_hmac('sha256', $friend, false);
  $MusicDetail["User"]["Hash"] = $hash;


  //推移の記録
  $tmp = json_decode(UserData_show($hash),true);
  if(isset($tmp["Date"])){
    $date = $tmp["Date"];
  	if($date["date"][sizeof($date["date"])-1] != $dispRate['userInfo']["playCount"]){
	    $date["RecentRate"][sizeof($date["RecentRate"])] = $UserRecentRate1;
	    $date["DispRate"][sizeof($date["DispRate"])] = $UserDisplayRate;
	    $date["MaxRate"][sizeof($date["MaxRate"])] = $UserMaxRate;
	    $date["BestRate"][sizeof($date["BestRate"])] = $UserBestRate;
	    $date["date"][sizeof($date["date"])] = $dispRate['userInfo']["playCount"];
	    $MusicDetail["Date"] = $date;
  	}else{
      $date["RecentRate"][sizeof($date["RecentRate"])-1] = $UserRecentRate1;
      $date["DispRate"][sizeof($date["DispRate"])-1] = $UserDisplayRate;
      $date["MaxRate"][sizeof($date["MaxRate"])-1] = $UserMaxRate;
      $date["BestRate"][sizeof($date["BestRate"])-1] = $UserBestRate;
      $date["date"][sizeof($date["date"])-1] = $dispRate['userInfo']["playCount"];
  		$MusicDetail["Date"] = $date;
  	}
  }else{
    $MusicDetail["Date"]["RecentRate"][] = $UserRecentRate1;
    $MusicDetail["Date"]["DispRate"][] = $UserDisplayRate;
    $MusicDetail["Date"]["MaxRate"][] = $UserMaxRate;
    $MusicDetail["Date"]["BestRate"][] = $UserBestRate;
    $MusicDetail["Date"]["date"][] = $dispRate['userInfo']["playCount"];
  }

  $json = json_encode( $MusicDetail , JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );

  //データーベースに登録
  UserData_set($friend,$dispRate["userInfo"]["userName"],$json);

  //出力
  $Hash["User"]["Hash"] = $hash;
  $json = json_encode( $Hash , JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );  
  header("Content-Type: text/javascript; charset=utf-8");
  echo $json;
?>