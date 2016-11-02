#!/usr/bin/env python3

import urllib.request,json

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

userid =  userid_get("","")

print(userid)