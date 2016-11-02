# CHUNITHM Rate Calculator
## 概要
CHUNITHM[©SEGA]のレート解析ツールです。

## 注意
**非公式です。**

**あくまで自己責任で使用してください。このツールを使用したことで起こったトラブルに制作者は責任を持ちません**
## 使い方
```js
javascript:(function(){
    if (!location.href.match(/^https:\/\/chunithm-net.com/)) {
        alert("CHUNITHM NETを開いた状態でしてください");
        throw Error();
    }
    window.name = 'CHUNITHM ■ CHUNITHM-NET';
    var html = '<form method="post" action="https://chunical.net/chunithm.php" id="postjump" target=_brunk style="display: none;"><input type="hidden" name="userid" value="' + document.cookie + '" ></form>';
    $("body").append(html);
    $('#postjump').submit();
    $('#postjump').remove();
})(document);
```
- このコードをブックマークレットに登録
- チュウニズムネットにログインした状態でブックマークレットを実行する

詳しくは https://chunical.net/

## ライセンス
This software is released under the MIT License, see LICENSE.txt.
