/*
  +---------------------------------------------------+
  | CHUNITHM Rate Calculator           [chunithm.js]  |
  +---------------------------------------------------+
  | Copyright (c) 2015-2016 Akashi_SN                 |
  +---------------------------------------------------+
  | This software is released under the MIT License.  |
  | http://opensource.org/licenses/mit-license.php    |
  +---------------------------------------------------+
  | Author: Akashi_SN <info@akashisn.info>            |
  +---------------------------------------------------+
*/

//--------------------------------------------------------------------
// グローバル変数
//--------------------------------------------------------------------

  var UserData;
  var Best = "";
  var Recent = "";
  var sort_Score = "";
  var sort_Diff = "";

//--------------------------------------------------------------------
// UserIdをPOSTしてデーターベースに登録
//--------------------------------------------------------------------

function JsonPost(Userid) {
  //ロード画面
  $("#overlay").fadeIn();
  // Ajax通信を開始する
  $.ajax({
    url: 'main.php',
    type: 'post',
    dataType: 'json',
    data: {
      userid: Userid
    },
    statusCode: {
      200: function(data){
        location.href = document.URL + '?user=' + data["User"]["Hash"];
      },
      204: function(){
        //UserIdの期限が切れた
        document.cookie = 'errorCode=100000';
        location.replace( "error.html" );
      },
      400: function(){
        //リクエストが不正
        document.cookie = 'errorCode=100001';
        location.replace( "error.html" );
      },
      403: function(){
        //データーベースに登録されていない
        document.cookie = 'errorCode=100002';
        location.replace( "error.html" );
      },
      500:function(){
        //エラー
        document.cookie = 'errorCode=100003';
        location.replace( "error.html" );
      }
    },
    complete: function() {
      $("#overlay").fadeOut();
    }
  });  
}

//--------------------------------------------------------------------
// HashをPOSTしてデーターベースを参照
//--------------------------------------------------------------------

function UserHash(hash) {
  //ロード画面
  $("#overlay").fadeIn();
  // Ajax通信を開始する
  $.ajax({
    url: 'main.php',
    type: 'post',
    dataType: 'json',
    data: {
      hash: hash
    },
    statusCode: {
      200: function(data){
        console.log(data);
        UserData = data;
        UserRateDisp();
        BestRateDisp();
      },
      204: function(){
        //UserIdの期限が切れた
        document.cookie = 'errorCode=100000';
        location.replace( "error.html" );
      },
      400: function(){
        //リクエストが不正
        document.cookie = 'errorCode=100001';
        location.replace( "error.html" );
      },
      403: function(){
        //データーベースに登録されていない
        document.cookie = 'errorCode=100002';
        location.replace( "error.html" );
      },
      500:function(){
        //エラー
        document.cookie = 'errorCode=100003';
        location.replace( "error.html" );
      }
    },
    complete: function() {
      $("#overlay").fadeOut();
    }
  });  
}

//--------------------------------------------------------------------
// ユーザーデータの表示
//--------------------------------------------------------------------

function UserRateDisp(){
  var UserRate = UserData["User"];
  var UserInfo = UserData["Userinfo"];
  var frame = ["normal", "copper", "silver", "gold", "platina"];
  var elements = `
  <div class="w420 box_player clearfix">
    <div id="UserCharacter" class="player_chara" style='background-image:url("https://chunithm-net.com/mobile/common/images/charaframe_`+ frame[parseInt(parseInt(UserInfo["characterLevel"])/5)] +`.png");margin-top: 10px;'>
      <img id="characterFileName" src="https://chunithm-net.com/mobile/` + UserInfo["characterFileName"] + `">
    </div>
    <div class="box07 player_data">
      <div id="UserHonor" class="player_honor" style='background-image:url("https://chunithm-net.com/mobile/common/images/honor_bg_` + frame[parseInt(UserInfo["trophyType"])] + `.png")'>
        <div class="player_honer_text_view">
          <div id="HonerText" class="player_honer_text">` + UserInfo["trophyName"] + `</div>
        </div>
      </div>`;
  if(UserInfo["reincarnationNum"] > 0){
    elements += `
      <div id="UserReborn" class="player_reborn">`;
    elements += UserInfo["reincarnationNum"];
  }else{
    elements += `
      <div id="UserReborn" class="player_reborn_0">`;                  
  }
  elements += `
      </div>
      <div class="player_name">
        <div class="player_lv">
          <span class="font_small mr_5">Lv.</span><span id="UserLv">` + UserInfo["level"] + `</span></div><span id="UserName">` + UserInfo["userName"] + `</span>
        </div>
        <div class="player_rating" id="player_rating">BEST枠 : <span id="UserRating">` + UserRate["BestRate"].toFixed(2) + `</span> / <span>MAX</span> <span id="UserRating">` + UserRate["MaxRate"].toFixed(2) + `</span><br><div style="margin-top:5px;">RECENT枠 :<span id="UserRating">` + UserRate["RecentRate-1"].toFixed(2) + `</span> / <span>表示レート</span><span id="UserRating">` + UserRate["DispRate"].toFixed(2) + `</span></div>
      </div>
    </div>
    <div id="tweet" style="margin-top: 10px;"></div>
    <div style="margin-top: 0px" class="more w400" onclick="window.open('https://akashisn.info/?page_id=52#Air', '_blank');"><a href="JavaScript:void(0);">Air対応について</a></div>
  </div>`;  
  var div = document.getElementById("userInfo_result");
  div.innerHTML = elements;

  //tweetボタン
  twttr.ready(function() {
    var rate = "BEST枠平均: " + UserRate["BestRate"].toFixed(2) + " 最大レート: " + UserRate["MaxRate"].toFixed(2) + "\n" + "RECENT枠平均: " + UserRate["RecentRate-1"].toFixed(2) + " 表示レート: " + UserRate["DispRate"].toFixed(2) + "\n";
    twttr.widgets.createShareButton(
      document.URL,
      location.href,
      document.getElementById('tweet'),
      {
        lang: 'ja',
        size: 'normal',
        text:  rate,
        hashtags : 'CHUNITHMRateCalculator'
      }
    )
  });
}

//--------------------------------------------------------------------
// Best枠の表示
//--------------------------------------------------------------------

function BestRateDisp(){
	jQuery("#sort").fadeIn();
  if(Best == ""){
    var element = "";
    Best = `
  <div class="frame01 w460">
    <div class="frame01_inside w450">
      <h2 style="margin-top:10px;" id="page_title">BEST枠</h2>
      <hr class="line_dot_black w420">
      <div class="box01 w420">
        <div class="mt_10">
          <div id="userPlaylog_result">`;
      //Best枠の数だけ繰り返す
    for(var i = 0; i < UserData["Best"].length; i++){
      var MusicDeteil = UserData["Best"][i];
      var MusicName = MusicDeteil["MusicName"];
      var MusicImg = MusicDeteil["Images"];
      var BaseRate = MusicDeteil["BaseRate"];
      var Score = MusicDeteil["Score"];
      var Rank = MusicDeteil["Rank"];
      var BestRate = MusicDeteil["BestRate"];
      var level = MusicDeteil["level"];
      var BestScore = MusicDeteil["ScoreBest"];
      
      if(i == 30){
          element += `
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="frame01 w460">
    <div class="frame01_inside w450">
      <h2 style="margin-top:10px;" id="page_title">BEST枠外</h2>
      <hr class="line_dot_black w420">
      <div class="box01 w420">
        <div class="mt_10">
          <div id="userPlaylog_result">`;
      }
      element += `
            <div class="frame02 w400">
              <div class="play_jacket_side">
                <div class="play_jacket_area">
                  <div id="Jacket" class="play_jacket_img">
                    <img src="https://chunithm-net.com/mobile/`+MusicImg+`"">
                  </div>
                </div>
              </div>
              <div class="play_data_side01">
                <div class="box02 play_track_block">
                  <div id="TrackLevel" class="play_track_result">
                    <img src="https://chunithm-net.com/mobile/common/images/icon_`+level+`.png">
                  </div>
                </div>
                <div class="box02 play_musicdata_block">
                  <div id="MusicTitle" class="play_musicdata_title">`+MusicName+`</div>
                  <div class="play_musicdata_score clearfix">
                    <div class="play_musicdata_score_text">譜面定数:<span id="Score">`+BaseRate+`</span></div><br>
                    <div class="play_musicdata_score_text">RATING:<span id="Score">`+BestRate+`</span></div><br>
                    <div class="play_musicdata_score_text">Score：<span id="Score">`+Score+`</span></div>
                    <div id="rank"><img src="https://chunithm-net.com/mobile/common/images/icon_`+Rank+`.png"></div>`;
      if(i > 29 && BestScore != 0){
        element += `
        				    <div class="play_musicdata_score_text">Best枠入りまで : <span id="Score">`+ (BestScore-Score) +`(`+BestScore+`)</span></div>`;
      }
      element += `
          				</div>
                </div>
              </div>
            </div>`;            
    }
    Best += element;
  }      
  var div = document.getElementById( "rate" );
  div.innerHTML = Best;
}

//スコア順にソート
function Sort_Score(){
	var Score_array = UserData["Best"];
	Score_array.sort(function(a,b){
    if(a.Score>b.Score) return -1;
    if(a.Score < b.Score) return 1;
    return 0;
	});
	if(sort_Score == ""){
    var element = "";
    var rank = Score_array[0]["Rank"];
    sort_Score = `
  <div class="frame01 w460">
    <div class="frame01_inside w450">
      <h2 style="margin-top:10px;" id="page_title">`+rank.toUpperCase()+`</h2>
      <hr class="line_dot_black w420">
      <div class="box01 w420">
        <div class="mt_10">
          <div id="userPlaylog_result">`;
    //Best枠の数だけ繰り返す
    for(var i = 0; i < Score_array.length; i++){
     	var MusicDeteil = Score_array[i];
      var MusicName = MusicDeteil["MusicName"];
      var MusicImg = MusicDeteil["Images"];
      var BaseRate = MusicDeteil["BaseRate"];
      var Score = MusicDeteil["Score"];
      var Rank = MusicDeteil["Rank"];
      var BestRate = MusicDeteil["BestRate"];
      var level = MusicDeteil["level"];      
      if(rank != Rank){
      	rank = Rank;
      	element += `
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="frame01 w460">
    <div class="frame01_inside w450">
      <h2 style="margin-top:10px;" id="page_title">`+rank.toUpperCase()+`</h2>
      <hr class="line_dot_black w420">
      <div class="box01 w420">
        <div class="mt_10">
          <div id="userPlaylog_result">`;
      }
      element += `
              <div class="frame02 w400">
                <div class="play_jacket_side">
                  <div class="play_jacket_area">
                    <div id="Jacket" class="play_jacket_img">
                      <img src="https://chunithm-net.com/mobile/`+MusicImg+`"">
                    </div>	
                  </div>
                </div>
                <div class="play_data_side01">
                  <div class="box02 play_track_block">
                    <div id="TrackLevel" class="play_track_result">
                      <img src="https://chunithm-net.com/mobile/common/images/icon_`+level+`.png">
                    </div>
                  </div>
                  <div class="box02 play_musicdata_block">
                    <div id="MusicTitle" class="play_musicdata_title">`+MusicName+`</div>
                    <div class="play_musicdata_score clearfix">
                      <div class="play_musicdata_score_text">譜面定数:<span id="Score">`+BaseRate+`</span></div><br>
                      <div class="play_musicdata_score_text">RATING:<span id="Score">`+BestRate+`</span></div><br>
                      <div class="play_musicdata_score_text">Score：<span id="Score">`+Score+`</span></div>
                      <img src="https://chunithm-net.com/mobile/common/images/icon_`+Rank+`.png">
                    </div>
                  </div>
                </div>
              </div>`;            
    }	
    sort_Score += element;
  }      
  var div = document.getElementById( "rate" );
  div.innerHTML = sort_Score;
}

//難易度を返す
function difficult(n){
  if(n >= 13.7) return "13+";
  if(n >= 13) return "13";
  if(n >= 12.7) return "12+";
  if(n >= 12) return "12";
  if(n >= 11.7) return "11+";
  if(n >= 11) return "11";
}

//難易度順にソート
function Sort_Diff(){
  var Diff_array = UserData["Best"];
  Diff_array.sort(function(a,b){
    if(a.BaseRate>b.BaseRate) return -1;
    if(a.BaseRate < b.BaseRate) return 1;
    return 0;
  });
  if(sort_Diff == ""){
    var element = "";
    var Diff = difficult(Diff_array[0]["BaseRate"]);
    sort_Diff = `
  <div class="frame01 w460">
    <div class="frame01_inside w450">
      <h2 style="margin-top:10px;" id="page_title">`+Diff+`</h2>
      <hr class="line_dot_black w420">
      <div class="box01 w420">
        <div class="mt_10">
          <div id="userPlaylog_result">`;
    //Best枠の数だけ繰り返す
    for(var i = 0; i < Diff_array.length; i++){
      var MusicDeteil = Diff_array[i];
      var MusicName = MusicDeteil["MusicName"];
      var MusicImg = MusicDeteil["Images"];
      var BaseRate = MusicDeteil["BaseRate"];
      var Score = MusicDeteil["Score"];
      var Rank = MusicDeteil["Rank"];
      var BestRate = MusicDeteil["BestRate"];
      var level = MusicDeteil["level"];      
      if(Diff != difficult(BaseRate)){
        Diff = difficult(BaseRate);
        element += `
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="frame01 w460">
    <div class="frame01_inside w450">
      <h2 style="margin-top:10px;" id="page_title">`+Diff+`</h2>
      <hr class="line_dot_black w420">
      <div class="box01 w420">
        <div class="mt_10">
          <div id="userPlaylog_result">`;
      }
      element += `
              <div class="frame02 w400">
                <div class="play_jacket_side">
                  <div class="play_jacket_area">
                    <div id="Jacket" class="play_jacket_img">
                      <img src="https://chunithm-net.com/mobile/`+MusicImg+`"">
                    </div>  
                  </div>
                </div>
                <div class="play_data_side01">
                  <div class="box02 play_track_block">
                    <div id="TrackLevel" class="play_track_result">
                      <img src="https://chunithm-net.com/mobile/common/images/icon_`+level+`.png">
                    </div>
                  </div>
                  <div class="box02 play_musicdata_block">
                    <div id="MusicTitle" class="play_musicdata_title">`+MusicName+`</div>
                    <div class="play_musicdata_score clearfix">
                      <div class="play_musicdata_score_text">譜面定数:<span id="Score">`+BaseRate+`</span></div><br>
                      <div class="play_musicdata_score_text">RATING:<span id="Score">`+BestRate+`</span></div><br>
                      <div class="play_musicdata_score_text">Score：<span id="Score">`+Score+`</span></div>
                      <img src="https://chunithm-net.com/mobile/common/images/icon_`+Rank+`.png">
                    </div>
                  </div>
                </div>
              </div>`;            
    } 
    sort_Diff += element;
  }      
  var div = document.getElementById( "rate" );
  div.innerHTML = sort_Diff;
}
//--------------------------------------------------------------------
// Recent枠の表示
//--------------------------------------------------------------------

function RecentRateDisp(){
	jQuery("#sort").fadeOut();
  if(Recent == ""){
    var element = "";
    Recent = `
  <div class="frame01 w460">
    <div class="frame01_inside w450">
      <h2 style="margin-top:10px;" id="page_title">RECENT枠</h2>
      <hr class="line_dot_black w420">
      <div class="box01 w420">
        <div class="mt_10">
          <div id="userPlaylog_result">`;
        //Best枠の数だけ繰り返す
        for(var i = 0; i < UserData["Recent"].length; i++){
          var MusicDeteil = UserData["Recent"][i];
          var MusicName = MusicDeteil["MusicName"];
          var MusicImg = MusicDeteil["Images"];
          var BaseRate = MusicDeteil["BaseRate"];
          var Score = MusicDeteil["Score"];
          var Rank = MusicDeteil["Rank"];
          var BestRate = MusicDeteil["BestRate"];
          var level = MusicDeteil["level"];
          if(i == 10){
              element += `
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="frame01 w460">
    <div class="frame01_inside w450">
      <h2 style="margin-top:10px;" id="page_title">RECENT枠外</h2>
      <hr class="line_dot_black w420">
      <div class="box01 w420">
        <div class="mt_10">
          <div id="userPlaylog_result">`;
            }
            element += `
              <div class="frame02 w400">
                <div class="play_jacket_side">
                  <div class="play_jacket_area">
                    <div id="Jacket" class="play_jacket_img">
                      <img src="https://chunithm-net.com/mobile/`+MusicImg+`"">
                    </div>
                  </div>
                </div>
                <div class="play_data_side01">
                  <div class="box02 play_track_block">
                    <div id="TrackLevel" class="play_track_result">
                      <img src="https://chunithm-net.com/mobile/common/images/icon_`+level+`.png">
                    </div>
                  </div>
                  <div class="box02 play_musicdata_block">
                    <div id="MusicTitle" class="play_musicdata_title">`+MusicName+`</div>
                    <div class="play_musicdata_score clearfix">
                      <div class="play_musicdata_score_text">譜面定数:<span id="Score">`+BaseRate+`</span></div><br>
                      <div class="play_musicdata_score_text">RATING:<span id="Score">`+BestRate+`</span></div><br>
                      <div class="play_musicdata_score_text">Score：<span id="Score">`+Score+`</span></div>
                      <img src="https://chunithm-net.com/mobile/common/images/icon_`+Rank+`.png">
                    </div>
                  </div>
                </div>
              </div>`;            
        }
        Recent += element;
      }      
      var div = document.getElementById( "rate" );
      div.innerHTML = Recent;
}


//--------------------------------------------------------------------
// 共通
//--------------------------------------------------------------------
(function (i, s, o, g, r, a, m) {
  i['GoogleAnalyticsObject'] = r;
  i[r] = i[r] || function () {
    (i[r].q = i[r].q || [])
    .push(arguments)
  }, i[r].l = 1 * new Date();
  a = s.createElement(o)
    , m = s.getElementsByTagName(o)[0];
  a.async = 1;
  a.src = g;
  m.parentNode.insertBefore(a, m)
})(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

ga('create', 'UA-74926268-1', 'auto');
ga('send', 'pageview');

! function (d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0]
    , p = /^http:/.test(d.location) ? 'http' : 'https';

  if (!d.getElementById(id)) {
    js = d.createElement(s);
    js.id = id;
    js.async = true;
    js.src = p + '://platform.twitter.com/widgets.js';
    fjs.parentNode.insertBefore(js, fjs);
  }
}(document, 'script', 'twitter-wjs');

//--------------------------------------------------------------------
// Cookieを取得(連想配列で全件返す)
//--------------------------------------------------------------------

function getCookie() {
  var resultList = new Array();
  var cookies = document.cookie;

  if (cookies != "") {
    var col = cookies.split(";");
    for (var i = 0; i < col.length; i++) {
      var value = col[i].split("=");
      resultList[value[0].trim()] = decodeURIComponent(value[1]);
    }
  }
  return resultList;
}

//--------------------------------------------------------------------
// エラー処理
//--------------------------------------------------------------------

function error() {
  var cookie = getCookie();
  var errorCode = cookie["errorCode"];
  if (errorCode == null || errorCode == "") {
    errorCode = 0;
  }
  var output = "";
  // エラーコード
  output += "<p class=\"font_small\">Error Code: " + errorCode + "<hr></p>";
  output += "<p class=\"font_small\">";
  switch (parseInt(errorCode)) {
  case 100000:
    output += "UserIdの期限が切れています。もう一度ログインしてから実行してください。"
    break;
  case 100001:
    output += "リクエストが不正もしは、曲数が足りていません。"
    break;
  case 100002:
    output += "データーベースに登録されていないので、実行しなおしてください。"
    break;
  case 100003:  
    output += 'エラーが発生しました。よろしければ<a href="https://twitter.com/Akashi_SN" target="_blank">@Akashi_SN</a>までお知らせください。'
    break;
  default:
    　output += "invalid ErrorCode.";
    break;
  }
  output += "</p>";
  var div = document.getElementById("errorText_result");
  div.innerHTML = output;
}
