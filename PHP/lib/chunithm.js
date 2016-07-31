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
// 共通
//--------------------------------------------------------------------

(function(i, s, o, g, r, a, m) {
  i['GoogleAnalyticsObject'] = r;
  i[r] = i[r] || function() {
    (i[r].q = i[r].q || []).push(arguments)
  }, i[r].l = 1 * new Date();
  a = s.createElement(o),
    m = s.getElementsByTagName(o)[0];
  a.async = 1;
  a.src = g;
  m.parentNode.insertBefore(a, m)
})(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

ga('create', 'UA-74926268-1', 'auto');
ga('send', 'pageview');

! function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0],
    p = /^http:/.test(d.location) ? 'http' : 'https';

  if (!d.getElementById(id)) {
    js = d.createElement(s);
    js.id = id;
    js.async = true;
    js.src = p + '://platform.twitter.com/widgets.js';
    fjs.parentNode.insertBefore(js, fjs);
  }
}(document, 'script', 'twitter-wjs');


//--------------------------------------------------------------------
// グローバル変数
//--------------------------------------------------------------------

  var UserData;
  var Best = "";
  var Recent ="";

//--------------------------------------------------------------------
// レート値取得
//--------------------------------------------------------------------

//apiにアクセス
function JsonPost(Userid) {
  //ロード画面
  $("#overlay").fadeIn();
  // Ajax通信を開始する
  $.ajax({
    url: 'main.php',
    type: 'post', // getかpostを指定(デフォルトは前者)
    dataType: 'json', // 「json」を指定するとresponseがJSONとしてパースされたオブジェクトになる
    data: { // 送信データを指定(getの場合は自動的にurlの後ろにクエリとして付加される)
      userid: Userid
    },
    // ・ステータスコードは正常で、dataTypeで定義したようにパース出来たとき
    success: function(data) {
      $('#result').val('成功');
      console.log(data);
      UserData = data;
      UserRateDisp();
      BestRateDisp();
    },
    // ・サーバからステータスコード400以上が返ってきたとき
    // ・ステータスコードは正常だが、dataTypeで定義したようにパース出来なかったとき
    // ・通信に失敗したとき
    error: function() {
      location.replace( "error.html" );
    },
    // 成功・失敗に関わらず通信が終了した際の処理
    complete: function() {
      $("#overlay").fadeOut();
    }
  });  
}

//ユーザーデータの表示
function UserRateDisp(){
  var UserRate = UserData["User"];
  document.getElementById('best-max').textContent = "BEST枠平均: " + UserRate["BestRate"].toFixed(2) + "/最大レート: " + UserRate["MaxRate"].toFixed(2); 
  document.getElementById('recent-disp').textContent = "RECENT枠平均: " + UserRate["RecentRate-1"].toFixed(2) + "/表示レート: " + UserRate["DispRate"].toFixed(2);
  //tweetボタン
  twttr.ready(function() {
    var rate = "BEST枠平均: " + UserRate["BestRate"].toFixed(2) + " 最大レート: " + UserRate["MaxRate"].toFixed(2) + "\n" + "RECENT枠平均: " + UserRate["RecentRate-1"].toFixed(2) + " 表示レート: " + UserRate["DispRate"].toFixed(2) + "\n";
    twttr.widgets.createShareButton(
      'https://akashisn.info/?page_id=52',
      location.href,
      document.getElementById('tweet'),
      {
        lang: 'ja',
        size: 'large',
        text:  rate,
        hashtags : 'CHUNITHMRateCalculator'
      }
    )
  });
}

//best枠の表示
function BestRateDisp(){
  if(Best == ""){
    var element = "";
    Best = '\
  <div class="frame01 w460">\
    <div class="frame01_inside w450">\
      <h2 style="margin-top:10px;" id="page_title">BEST枠</h2>\
      <hr class="line_dot_black w420">\
      <div class="box01 w420">\
        <div class="mt_10">\
          <div id="userPlaylog_result">';
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
          
          if(i == 30){
              element += '\
          </div>\
        </div>\
      </div>\
    </div>\
  </div>\
  <div class="frame01 w460">\
    <div class="frame01_inside w450">\
      <h2 style="margin-top:10px;" id="page_title">BEST枠外</h2>\
      <hr class="line_dot_black w420">\
      <div class="box01 w420">\
        <div class="mt_10">\
          <div id="userPlaylog_result">';
            }
          element += '\
            <div class="frame02 w400">\
              <div class="play_jacket_side">\
                <div class="play_jacket_area">\
                  <div id="Jacket" class="play_jacket_img">\
                    <img src="https://chunithm-net.com/mobile/'+MusicImg+'"">\
                  </div>\
                </div>\
              </div>\
              <div class="play_data_side01">\
                <div class="box02 play_track_block">\
                  <div id="TrackLevel" class="play_track_result">\
                    <img src="https://chunithm-net.com/mobile/common/images/icon_'+level+'.png">\
                  </div>\
                </div>\
                <div class="box02 play_musicdata_block">\
                  <div id="MusicTitle" class="play_musicdata_title">'+MusicName+'</div>\
                  <div class="play_musicdata_score clearfix">\
                    <div class="play_musicdata_score_text">譜面定数:<span id="Score">'+BaseRate+'</span></div><br>\
                    <div class="play_musicdata_score_text">RATING:<span id="Score">'+BestRate+'</span></div><br>\
                    <div class="play_musicdata_score_text">Score：<span id="Score">'+Score+'</span></div>\
                    <img src="https://chunithm-net.com/mobile/common/images/icon_'+Rank+'.png">\
                  </div>\
                </div>\
              </div>\
            </div>';            
        }
        Best += element;
      }      
      var div = document.getElementById( "inner" );
      div.innerHTML = Best;
}

//Recent枠の表示
function RecentRateDisp(){
  if(Recent == ""){
    var element = "";
    Recent = '\
  <div class="frame01 w460">\
    <div class="frame01_inside w450">\
      <h2 style="margin-top:10px;" id="page_title">RECENT枠</h2>\
      <hr class="line_dot_black w420">\
      <div class="box01 w420">\
        <div class="mt_10">\
          <div id="userPlaylog_result">';
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
              element += '\
          </div>\
        </div>\
      </div>\
    </div>\
  </div>\
  <div class="frame01 w460">\
    <div class="frame01_inside w450">\
      <h2 style="margin-top:10px;" id="page_title">RECENT枠外</h2>\
      <hr class="line_dot_black w420">\
      <div class="box01 w420">\
        <div class="mt_10">\
          <div id="userPlaylog_result">';
            }
            element += '\
              <div class="frame02 w400">\
                <div class="play_jacket_side">\
                  <div class="play_jacket_area">\
                    <div id="Jacket" class="play_jacket_img">\
                      <img src="https://chunithm-net.com/mobile/'+MusicImg+'"">\
                    </div>\
                  </div>\
                </div>\
                <div class="play_data_side01">\
                  <div class="box02 play_track_block">\
                    <div id="TrackLevel" class="play_track_result">\
                      <img src="https://chunithm-net.com/mobile/common/images/icon_'+level+'.png">\
                    </div>\
                  </div>\
                  <div class="box02 play_musicdata_block">\
                    <div id="MusicTitle" class="play_musicdata_title">'+MusicName+'</div>\
                    <div class="play_musicdata_score clearfix">\
                      <div class="play_musicdata_score_text">譜面定数:<span id="Score">'+BaseRate+'</span></div><br>\
                      <div class="play_musicdata_score_text">RATING:<span id="Score">'+BestRate+'</span></div><br>\
                      <div class="play_musicdata_score_text">Score：<span id="Score">'+Score+'</span></div>\
                      <img src="https://chunithm-net.com/mobile/common/images/icon_'+Rank+'.png">\
                    </div>\
                  </div>\
                </div>\
              </div>';            
        }
        Recent += element;
      }      
      var div = document.getElementById( "inner" );
      div.innerHTML = Recent;
}