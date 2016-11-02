#!/usr/bin/env python3

import urllib.request,json

#userid取得
def userid_get(segaid,password):
  url = "https://chunithm-net.com/Login/SegaIdLoginApi"
  method = "POST"
  headers = {
    "Content-Type":"application/json",
    "Accept":"application/json"
  }
  obj = {'segaId':"%s"%(segaid),'password':"%s"%(password)}
  json_data = json.dumps(obj).encode("utf-8")
  request = urllib.request.Request(url, data=json_data, method=method, headers=headers)
  with urllib.request.urlopen(request) as response:
    response_body = response.read().decode("utf-8")
    content = json.loads(response_body)
    return content['sessionIdList'][0]

#楽曲のベストスコア取得
def BestScore_get(userid,level):
  url = "https://chunithm-net.com/ChuniNet/GetUserMusicApi"
  method = "POST"
  headers = {
    "Content-Type":"application/json",
    "Accept":"application/json"
  }
  obj = {'level':"%s"%(level),'userId':"%s"%(userid)}
  json_data = json.dumps(obj).encode("utf-8")
  request = urllib.request.Request(url, data=json_data, method=method, headers=headers)
  with urllib.request.urlopen(request) as response:
    response_body = response.read().decode("utf-8")
    content = json.loads(response_body)
    return content

#json読み込み
def LocadJson():
  file = open("chunithm.json","rb")
  data = file.read().decode("utf-8")
  json_data = json.loads(data)
  return json_data

#スコアからレート
def Score_to_Rate(score,base_rate):
  if score >= 1007500:
    return base_rate+2
  else if score >= 1005000:
    return base_rate+1.5+(score-1005000)*10/50000
  else if　score >= 1000000
    return base_rate+1+(score-1000000)*5/50000
  else if score >= 975000:
    return base_rate+(score-975000)*2/50000
  else if score >= 950000:
    return base_rate-1.5+(score-950000)*3/50000
  else if　score >= 925000:
    return base_rate-3+(score-925000)*3/50000
  else if　score >= 900000:
    return base_rate-5+(score-900000)*4/50000
  else if　score >= 800000:
    return base_rate-7.5+(score-800000)*1.25/50000
  else if　score >= 700000:
    return base_rate-8.5+(score-700000)*0.5/50000
  else if score >= 600000:
    return base_rate-9+(score-600000)*0.25/50000
  else if score >= 500000:
    return base_rate-13.7+(score-500000)*2.35/50000
  else:
    return null