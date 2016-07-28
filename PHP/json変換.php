<?php
	$json = file_get_contents("chunithm.json");
	$arr = json_decode($json,true);
	$out = array();
	for($i = 0; $i < sizeof($arr);$i++){
		$images = $arr[$i]["Images"];
		$musicID = $arr[$i]["MusicID"];
		$ex = $arr[$i]["ex"];
		$musicName = $arr[$i]["楽曲名"];
		$baseRate = $arr[$i]["譜面定数"];		
		$out[$musicID]["MusicID"] = $musicID;
		$out[$musicID]["楽曲名"] = $musicName;
		$out[$musicID]["Images"] = $images;
		if($ex == 1){
			$out[$musicID]["譜面定数"]["ex"] = $baseRate;
			$out[$musicID]["譜面定数"]["mas"];
 		}else{
			$out[$musicID]["譜面定数"]["ex"];
			$out[$musicID]["譜面定数"]["mas"] = $baseRate;
		}
	}
	$json = json_encode( $out , JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );
	print_r($json);
	$file = fopen('result.json', 'a');
	fwrite($file,$json);
	fclose($file);
?>