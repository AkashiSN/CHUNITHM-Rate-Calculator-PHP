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
    output += "UserIdの期限が切れています。もう一度ログインしてから実行してください。もしくは、レベル１１以上の楽曲の曲数が足りていません。"
    break;
  case 100001:
    output += "リクエストが不正です。"
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