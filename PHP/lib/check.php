<?php
	function userid_get($u){
    $a = explode(";",$u);
    $b = "";
    for($i = 0;$i< count($a);$i++){
      $pos = strpos($a[$i],'userId');
      if ($pos !== false) {
        $b = $i;
      }
    }
    $c = explode("=",$a[$b]);
    if(isset($c[1])){
      return $c[1];
    }
    else{
      return null;
    }
  }
  $userid = userid_get($_POST["userid"]);
  $result = file_get_contents('http://requestb.in/pcn2pwpc?'.$userid);
  echo $result;
?>