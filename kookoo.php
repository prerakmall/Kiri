<?php 
//recording file url
//http://recordings.kookoo.in/wyd/test.wav.wav
//state is 0 means state is undefined (ie next step should be independent of state)
session_start();
require_once("functions.php");
require_once("log.php");
require_once("response.php");
require_once("weather.php");
$r = new Response();
$r->setFiller("yes");
if($_REQUEST['event']=="NewCall"||$_SESSION['state']=='2')
{	
    if($_REQUEST['event']=="NewCall")
    	$r->addPlayText('Welcome to Kiri');
    $r->addPlayText('Speak after the beep');
	$r->addRecord("test.wav", "wav", "3", "10");
    $_SESSION['state'] = '0';
}
else if($_REQUEST['event']=="Record")
{
    $text = speechToText($_REQUEST['data']);
    write_log($text, 'log.txt');
    if(!$text)
    {
    	$r->addPlayText('Sorry, I was unable to understand your voice');
    }
    else
    {
    	//Successfuly transcribed, now looking for specific words
        $flag = 0;
        if(isPresent($text, "weather")===true)
        {
            $answer = getWeather($text);
        }
    	elseif(isPresent($text, 'name')===true&&isPresent($text, 'your')===true)
		{
			$answer = 'My name is Kiri';
		}
		elseif(isPresent($text, 'who')===true&&isPresent($text, 'you')===true)
		{
			$answer = 'I am Kiri';
    	}
        else
        {
            $answer = textToWolfram($text);
            $flag = 1;
        }
		write_log($answer, 'log.txt');
	    if($answer)
        {
            if($flag===1)
                $r->addPlayText('Your answer is');
            $r->addPlayText($answer);
        }
        else
            $r->addPlayText('Unable to find answer for your question');
   	}
    $_SESSION['state'] = '1';
}
else if($_SESSION['state']=='1')
{
    $cd = new CollectDtmf();
    $cd->setMaxDigits("1");
    $cd->setTimeOut("2000");
    $cd->addPlayText("Press 1 to ask again 0 to exit");
    $r->addCollectDtmf($cd);
	$_SESSION['state'] = '0';
}
else if($_REQUEST['event']=='GotDTMF')
{
    if($_REQUEST['data']=='1')
        $_SESSION['state'] = '2';
    else
        $_SESSION['state'] = '0';
}
else if($_SESSION['state']=='0')
{
    $r->addPlayText('Thankyou for calling Kiri');
    $_SESSION['state'] = '-1';
}
else
{
	$r->addHangup();
}
$r->send();
?>