<?php 
function isPresent($main, $sub)
{
	if(strpos($main, $sub)===false)
		return false;
	else
		return true;
}

function speechToText($url_record)
{
	file_put_contents("temp.wav", file_get_contents($url_record));
	shell_exec('./flac temp.wav -o temp.flac -f');
	shell_exec('rm ./temp.wav');
	$query = file_get_contents('./temp.flac');
	// ini_set('default_socket_timeout', 20);
	// file_put_contents("Tmpfile.flac", file_get_contents($matches[1]));

	// Google STT
	$stturl = "https://www.google.com/speech-api/v1/recognize?xjerr=1&client=chromium&lang=en-IN";
	$upload = $query;//file_get_contents("./Tmpfile.flac");
	$data = array(
	    "Content_Type"  =>  "audio/x-flac; rate=8000",
	    "Content"       =>  $upload,
	);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $stturl);
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array("Content-Type: audio/x-flac; rate=8000"));
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	ob_start();
	curl_exec($ch);
	curl_close($ch);
	$contents = ob_get_contents();
	ob_end_clean();
	$textarray = (json_decode($contents,true));
	$text = $textarray['hypotheses']['0']['utterance'];

	return $text;
}

function textToWolfram($text)
{
	$wolframurl = "http://api.wolframalpha.com/v2/query?appid=VVU7PG-9883K63QVE&format=plaintext&podtitle=Result&input=";
	$wolframurl .= urlencode($text);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $wolframurl);
    ob_start();
    curl_exec($ch);
    curl_close($ch);
    $contents = ob_get_contents();
    ob_end_clean();
    $obj = new SimpleXMLElement($contents);
    $answer = $obj->pod->subpod->plaintext;
    return $answer;
}

function console_log($msg){
	$STDERR = fopen("php://stderr", "w");
	fwrite($STDERR, $msg);
	fclose($STDERR);
}
?>