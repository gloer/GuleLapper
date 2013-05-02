<?php



function json_decode_nice($json, $assoc = FALSE){
    $json = str_replace(array("\n","\r"),"",$json);
    $json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/','$1"$3":',$json);
    $json = preg_replace('/(,)\s*}$/','}',$json);
    return json_decode($json,$assoc);
}


if (isset($_POST["data"]))
{
	$json = $_POST["data"];
	//$jsondata = json_decode_nice($json);
	file_put_contents('array1.json', $json);
	
} else {
	echo "Den er ikke SATT";	
}




?>