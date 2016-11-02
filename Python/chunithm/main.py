#!/usr/bin/env python3

import common

userid =  common.userid_get("","")

Music_Base = common.LocadJson()

BestRate_to_Musicid = []
MusicId_to_Score = []
MusicIDArray = Music_Base["MusicIDList"]
MusicID_Expert_Array = Music_Base["ExpertExist"]
Musics = 0

MusicBestScore_Mas = common.BestScore_get(userid,19903) #マスターの取得
MusicBestScore_Exp = common.BestScore_get(userid,19902) #エキスパートの取得

#登録されている楽曲の数だけ繰り返す
for MusicID in MusicIDArray:
	#ImageからMusicIDの配列
	Img_to_MusicID[Music_Base[MusicID]["Images"]] = MusicID
	#エキスパートを順に確認
	for Music_Expert in MusicBestScore_Exp["userMusicList"]:
		#リストに乗っている楽曲の場合
		if Music_Expert["musicId"] == MusicID:
			#リストにエキスパートがあるか
			try:
				if MusicID_Expert_Array[MusicID]:
					BestRate_to_Musicid[-MusicID] = common.Score_to_Rate(Music_Expert["scoreMax"],Music_Base[MusicID]["BaseRate"]["ex"])
					MusicId_to_Score[-MusicID] = Music_Expert["scoreMax"]
					++Musics
			except IndexError:
				raise
	#マスタを順に確認
	for Music_Master in MusicBestScore_Mas["userMusicList"]:
		#リストに乗っている楽曲の場合
		if Music_Master["musicId"] == MusicID:
			BestRate_to_Musicid[MusicID] = common.Score_to_Rate(Music_Master["scoreMax"],Music_Base[MusicID]["BaseRate"]["mas"])
			MusicId_to_Score[MusicID] = Music_Master["scoreMax"]
			++Musics

#30種類未満の場合
if Musics < 30:
	exit()

#レート値で降順にソート
BestRate_to_Musicid = sorted(BestRate_to_Musicid.items(), key=lambda x:x[1],reverse=True)

#データの整理
for MusicID, BestRate in BestRate_to_Musicid:
	#エキスパートの場合
	if MusicID < 0:
		Temp["MusicID"] = MusicIDArray
		Temp["level"] = "expert"
		Temp["MusicName"] = Music_Base[-MusicID]["MusicName"]
		Temp["Images"] = Music_Base[-MusicID]["Images"]
		Temp["BaseRate"] = Music_Base[-MusicID]["BaseRate"]["ex"]
		Temp["Score"] = MusicId_to_Score[MusicID]
		Temp["BestRate"] = 