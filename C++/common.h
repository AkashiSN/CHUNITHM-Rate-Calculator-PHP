#include <iostream>
#include <sstream>
#include <curl/curl.h>
#include <string>
#include <vector>
#include <algorithm>
#include <map>
#include <utility>
#include <functional>
#include "rapidjson/document.h"
#include "rapidjson/writer.h"
#include "rapidjson/stringbuffer.h"
#include "rapidjson/filereadstream.h"
#include "rapidjson/filewritestream.h"
#include <math.h>

double rounder(double src)
{
  double dst;
  dst = src*100;
  dst=floor(dst);
  return dst/100;
}
size_t callbackWrite(char *ptr, size_t size, size_t nmemb, std::string *stream)
{
  int dataLength = size * nmemb;
  stream->append(ptr, dataLength);
  return dataLength;
}
std::string curl_JSON_POST(std::string post_data,std::string url){

  CURL *curl;
  CURLcode ret;
  curl = curl_easy_init();
  std::string chunk;
  /*エラー処理*/
  if (curl == NULL) {
    std::cerr << "curl_easy_init() failed" << std::endl;
    return "error";
  }

  curl_easy_setopt(curl, CURLOPT_URL, url.c_str());
  curl_easy_setopt(curl, CURLOPT_POSTFIELDS, post_data.c_str());
  curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, callbackWrite);
  curl_easy_setopt(curl, CURLOPT_WRITEDATA, &chunk);
  ret = curl_easy_perform(curl);
  curl_easy_cleanup(curl);

  /*エラー処理*/
  if (ret != CURLE_OK) {
    std::cerr << "curl_easy_perform() failed." << std::endl;
    return "error";
  }
  return chunk;
}
double rate_get(std::string userid){
  std::string url = "https://chunithm-net.com/ChuniNet/GetUserInfoApi";
  std::string post_data = "{\"userId\":\"";
  post_data += userid;
  post_data += "\",\"friendCode\":0,\"fileLevel\":1}";

  std::string data = curl_JSON_POST(post_data,url);

  rapidjson::Document document;

  if(document.Parse(data.c_str()).HasParseError() == false){
     rapidjson::Value& a = document["userInfo"];
     return (double)(a["playerRating"].GetInt())/100.0;
  }else{
     return -1;
  }
}
long int friendcode_get(std::string userid){
  std::string url = "https://chunithm-net.com/ChuniNet/GetUserFriendlistApi";
  std::string post_data = "{\"userId\":\"";
  post_data += userid;
  post_data += "\",\"state\":4}";

  std::string data = curl_JSON_POST(post_data,url);

  rapidjson::Document document;

  if(document.Parse(data.c_str()).HasParseError() == false){
     return document["friendCode"].GetInt();
  }else{
     return -1;
  }
}
std::string score_get(std::string userid,int musicid){
  std::string url = "https://chunithm-net.com/ChuniNet/GetUserMusicDetailApi";
  std::string post_data = "{\"userId\":\"";
  post_data += userid;
  post_data += "\",\"musicId\":\"";
  std::stringstream ss;
  ss << musicid;
  post_data += ss.str();
  post_data += "\"}";

  std::string json = curl_JSON_POST(post_data,url);

  return json;
}

double score_to_rate(int score,double base_rate){
  if(score >= 1007500){
    return (double)(base_rate+2);
  }if(score >= 1005000){
    return (double)(base_rate+1.5+(double)(score-1005000)*10/50000);
  }if(score >= 1000000){
    return (double)(base_rate+1+(double)(score-1000000)*5/50000);
  }if(score >= 975000){
    return (double)(base_rate+(double)(score-975000)*2/50000);
  }if(score >= 950000){
    return (double)(base_rate-1.5+(double)(score-950000)*3/50000);
  }if(score >= 925000){
    return (double)(base_rate-3+(double)(score-925000)*3/50000);
  }if(score >= 900000){
    return (double)(base_rate-5+(double)(score-900000)*4/50000);
  }if(score >= 800000){
    return (double)(base_rate-7.5+(double)(score-800000)*1.25/50000);
  }if(score >= 700000){
    return (double)(base_rate-8.5+(double)(score-700000)*0.5/50000);
  }if(score >= 600000){
    return (double)(base_rate-9+(double)(score-600000)*0.25/50000);
  }if(score >= 500000){
    return (double)(base_rate-13.7+(double)(score-500000)*2.35/50000);
  }
}