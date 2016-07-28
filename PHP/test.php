<?php
header("Content-Type: text/javascript; charset=utf-8");
$out["userid"] = $_POST["userid"];
$out["score"]["mas"] = 10000000;
echo json_encode($out);
?>
