#include "common.h"

int main(int argc, char* argv[]){
  if (argc < 2) {
    std::cout << "<pre>         CHUNITHM Rate Calculator" << std::endl << std::endl;
    std::cout << "It is a tool to calculate the rate of Chunithm." << std::endl;
    std::cout << "  Usage: chunithm [OPTION]... [-n] UserID" << std::endl;
    std::cout << "     or: chunithm [OPTION]... [-s] [-SortOption] UserID" << std::endl;
    std::cout << "Advanced Options" << std::endl;
    std::cout << " -n UserID        Add to NewUser" << std::endl;
    std::cout << " -s               To sort the Json in" << std::endl;
    std::cout << " ..-r               ..rate" << std::endl;
    std::cout << " ..-d               ..difficult" << std::endl;
    std::cout << " ..-s               ..score" << std::endl;
    std::cout << " ..-ns              ..need score" <<std::endl;
    std::cout << "CHUNITHM Rate Calculator by Akashi_SN</pre>" << std::endl; 
    return 1;
  }else if(*argv[1] == '-'){
    if(*(argv[1]+1) == 'n'){
      if(*(argv[2]) != ' '){
        /*宣言部*/  
        std::string userid,score;
        double rate = 0;
        std::map<int, std::string> MusicID_to_Music_Name;
        std::map<int, std::string> MusicID_to_Music_Image;
        std::map<int, double> MusicID_to_base_rate_ex;
        std::map<int, double> MusicID_to_base_rate_mas;
        std::map<int, double> MusicID_to_best_rate_ex;
        std::map<int, double> MusicID_to_best_rate_mas;
        std::vector<std::pair<double, int> > MusicID_to_best_rate;
        std::map<int, int> MusicID_to_score_ex;
        std::map<int, int> MusicID_to_score_mas;
        std::vector<int> musicid;
        rapidjson::Document document,doc,docs;  
        FILE* fp;
        char buf[655360];
        int count=0;
        userid = argv[2];

        /*譜面定数の取得*/
        fp = fopen("chunithm-mas.json", "rb");
        rapidjson::FileReadStream rs(fp, buf, sizeof(buf));
        doc.ParseStream(rs);
        fclose(fp);
        for(rapidjson::SizeType i = 0; i < doc.Size(); i++){
          MusicID_to_Music_Name.insert(std::pair<int, std::string>(doc[i]["MusicID"].GetInt(),doc[i]["楽曲名"].GetString()));
          MusicID_to_Music_Image.insert(std::pair<int, std::string>(doc[i]["MusicID"].GetInt(),doc[i]["Images"].GetString()));
          MusicID_to_base_rate_mas.insert(std::pair<int, double>(doc[i]["MusicID"].GetInt(),doc[i]["譜面定数"].GetDouble()));
          musicid.push_back(doc[i]["MusicID"].GetInt());
        }
        fp = fopen("chunithm-ex.json", "rb");
        rapidjson::FileReadStream is(fp, buf, sizeof(buf));
        docs.ParseStream(is);
        fclose(fp);
        for(rapidjson::SizeType i = 0; i < docs.Size(); i++){
          MusicID_to_base_rate_ex.insert(std::pair<int, double>(docs[i]["MusicID"].GetInt(),docs[i]["譜面定数"].GetDouble()));
        }
        /*MusicIdを昇順にソート*/
        std::sort(musicid.begin(), musicid.end());

        /*楽曲の詳細データの取得*/
        for(int i = 0; i < musicid.size(); i++){
          score = score_get(userid,musicid[i]);
          /*エラー判定*/
          if(score == "null"){
            std::cout << "UserID is invalid" << std::endl;
            return 1;
          }
          if(document.Parse(score.c_str()).HasParseError() == false){      
            /*やったことあるか*/
            if(document["length"].GetInt() == 0){
              continue;
            }      
            /*エキスパートとマスターを順に確認*/
            rapidjson::Value& a = document["userMusicList"];      
            for(rapidjson::SizeType j = 0; j < a.Size(); j++){
            	/*初期化*/
            	rate = 0;
              rapidjson::Value& b = a[j];        
              /*エキスパートの場合*/
              if(b["level"].GetInt() == 2){
                /*リストに乗っているか*/
                if(MusicID_to_base_rate_ex[b["musicId"].GetInt()] != 0){
                  rate = score_to_rate(b["scoreMax"].GetInt(),MusicID_to_base_rate_ex[musicid[i]]);
                  rate = rounder(rate);
                  MusicID_to_score_ex.insert(std::pair<int, int>(musicid[i],b["scoreMax"].GetInt()));
                  MusicID_to_best_rate_ex.insert(std::pair<int, double>(musicid[i],rate)); 
                  MusicID_to_best_rate.push_back(std::pair<double, int>(rate,-musicid[i]));
                }else{
                  continue;
                }
              }
              /*マスターの場合*/
              else if(b["level"].GetInt() == 3){
                rate = score_to_rate(b["scoreMax"].GetInt(),MusicID_to_base_rate_mas[musicid[i]]);
                rate = rounder(rate);
                MusicID_to_score_mas.insert(std::pair<int, int>(musicid[i],b["scoreMax"].GetInt()));
                MusicID_to_best_rate_mas.insert(std::pair<int, double>(musicid[i],rate));
                MusicID_to_best_rate.push_back(std::pair<double, int>(rate,musicid[i]));
              }
            }
          }
        }
        /*レート順でMusicIDをソート*/
        std::sort(MusicID_to_best_rate.begin(),MusicID_to_best_rate.end(),std::greater<std::pair<double, int> >());
        
        for(int i = 0; i < MusicID_to_best_rate.size(); i++){
          if(MusicID_to_best_rate[i].second < 0){
            std::cout<<"楽曲名:"<<MusicID_to_Music_Name[-MusicID_to_best_rate[i].second]<<
            std::endl<<"MusicID:"<<-MusicID_to_best_rate[i].second<<
            std::endl<<"Image:"<<MusicID_to_Music_Image[-MusicID_to_best_rate[i].second]<<
            std::endl<<"譜面定数:"<<MusicID_to_base_rate_ex[-MusicID_to_best_rate[i].second]<<
            std::endl<<"score:"<<MusicID_to_score_ex[-MusicID_to_best_rate[i].second]<<
            std::endl<<"rate:"<<MusicID_to_best_rate_ex[-MusicID_to_best_rate[i].second]<<
            std::endl<<"ex"<<
            std::endl<<std::endl;
          }
          if(MusicID_to_best_rate[i].second > 0){
            std::cout<<"楽曲名:"<<MusicID_to_Music_Name[MusicID_to_best_rate[i].second]<<
            std::endl<<"MusicID:"<<MusicID_to_best_rate[i].second<<
            std::endl<<"Image:"<<MusicID_to_Music_Image[MusicID_to_best_rate[i].second]<<
            std::endl<<"譜面定数:"<<MusicID_to_base_rate_mas[MusicID_to_best_rate[i].second]<<
            std::endl<<"score:"<<MusicID_to_score_mas[MusicID_to_best_rate[i].second]<<
            std::endl<<"rate:"<<MusicID_to_best_rate_mas[MusicID_to_best_rate[i].second]<<
            std::endl<<std::endl;
          }
        }
      }
    }
    else if(*(argv[1]+1) == 's'){
      if(*(argv[2]) != ' '){
      }
  	}
  }
  return 0;
}
