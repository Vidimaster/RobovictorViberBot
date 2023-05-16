<?php 

$auth_token = "";
$send_name = win2utf("Робовиктор");
$is_log = true;
$i = 1;
$tempfilename = "";
$picture = "";
$pictext = "";

function put_log_in($data)
{
	global $is_log;
	if($is_log) {file_put_contents("tmp_in.txt", $data."\n", FILE_APPEND);}
}

function put_log_out($data)
{
	global $is_log;
	if($is_log) {file_put_contents("tmp_out.txt", $data."\n", FILE_APPEND);}
}

function sendReq($data)
{
	$request_data = json_encode($data);
	put_log_out($request_data);
	//here goes the curl to send data to user
	$ch = curl_init("https://chatapi.viber.com/pa/send_message");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	$response = curl_exec($ch);
	$err = curl_error($ch);
	curl_close($ch);
	file_put_contents("userids.txt", $data['receiver']."\n", FILE_APPEND);
	if($err) {return $err;}
	else {return $response;}
}

function sendMsg($sender_id, $text, $type, $tracking_data = Null, $arr_asoc = Null)
{
	global $auth_token, $send_name;

	$data['auth_token'] = $auth_token;
	$data['receiver'] = $sender_id;
	if($text != Null) {$data['text'] = $text;}
	$data['type'] = $type;
	$data['sender']['name'] = $send_name;
	if($tracking_data != Null) {$data['tracking_data'] = $tracking_data;}
	if($arr_asoc != Null)
	{
		foreach($arr_asoc as $key => $val) {$data[$key] = $val;}
	}
	
	return sendReq($data);
}

function sendHello($user_id, $text, $tracking_data = Null, $arr_asoc = Null)
{
	global $auth_token, $send_name;
    
	$data['auth_token'] = $auth_token;
	$data['receiver'] = $user_id;
	if($text != Null) {$data['text'] = $text;}
	$data['type'] = "text";
	$data['sender']['name'] = $send_name;
	$data['media'] = "https://real-buh.ru/ViberBot/img/bus-iconFORViber.png";
	$data['type'] = "picture";
	$data['thumbnail'] = "https://real-buh.ru/ViberBot/img/bus-iconFORViber.png";
	if($tracking_data != Null) {$data['tracking_data'] = $tracking_data;}
	if($arr_asoc != Null)
	{
		foreach($arr_asoc as $key => $val) {$data[$key] = $val;}
	}
  
	return sendReq($data);
}

function sendPic($sender_id, $text, $type, $picture, $tracking_data = Null, $arr_asoc = Null)
{
	global $auth_token, $send_name;
  
	$data['auth_token'] = $auth_token;
	$data['receiver'] = $sender_id;
	$data['media'] = $picture;
	$pictemp1 = preg_split("/_/", $text);
	$pictemp2 = preg_split('//', $pictemp1[1], -1, PREG_SPLIT_NO_EMPTY);
	$pictemp3 = (win2utf("МАРШРУТ ") . mb_convert_encoding('&#8470;', 'UTF-8', 'HTML-ENTITIES') . $pictemp1[0] . mb_convert_encoding('&#10;', 'UTF-8', 'HTML-ENTITIES') . win2utf("Выезд в ") . $pictemp2[0] . $pictemp2[1] . ":" . $pictemp2[2] . $pictemp2[3]);
	$data['text'] = $pictemp3;
	$data['type'] = "picture";
	$data['thumbnail'] = "https://real-buh.ru/ViberBot/img/thumbnail1.png";
	$data['sender']['name'] = $send_name;
	if($tracking_data != Null) {$data['tracking_data'] = $tracking_data;}
	if($arr_asoc != Null)
	{
		foreach($arr_asoc as $key => $val) {$data[$key] = $val;}
	}
	
	return sendReq($data);
}


function sendMsgText($sender_id, $text, $tracking_data = Null)
{
	return sendMsg($sender_id, $text, "text", $tracking_data);
}

$request = file_get_contents("php://input");
$input = json_decode($request, true);
put_log_in($request);

$type = $input['message']['type']; //type of message received (text/picture)
$text = $input['message']['text']; //actual message the user has sent
$sender_id = $input['sender']['id']; //unique viber id of user who sent the message
$user_id = $input['user']['id']; //unique viber id of user who sent the message
$sender_name = $input['sender']['name']; //name of the user who sent the message
$temppicture = $data['media'];

if($input['event'] == 'webhook') 
{
  $webhook_response['status'] = 0;
  $webhook_response['status_message'] = "oka";
  $webhook_response['event_types'] = 'delivered';
  echo json_encode($webhook_response);
  die;
}
elseif($input['event'] == "subscribed") 
{
  sendMsgText($sender_id, win2utf("Счастливого пути!"));
}
elseif($input['event'] == "conversation_started")
{
  sendHello($user_id, win2utf("Здравствуйте! Чтобы узнать график маршрута, напишите номер маршрута и время выезда в таком формате:" . mb_convert_encoding('&#10;', 'UTF-8', 'HTML-ENTITIES') . "2 14:55 или 2 1455"));
}
elseif($input['message']['type'] == "picture") 
{
  sendMsgText("KclOrxuthI8ekTaQtn/m6A==", win2utf("Новое сообщение"));
}
elseif($input['event'] == "message")
{ 

  $input1 = $text;
  $input1 = preg_replace("/ /", "_", $input1);
  $input1 = preg_replace("/:/", "", $input1);
  $iparr = preg_split ("/\_/", $input1); 
  if (strlen ($iparr[1]) == 3) {
    $input1 = $iparr[0] . "_" . "0" . $iparr[1];
   }
  
  $arFileName = scandir("./img");
foreach($arFileName as $FileName) {
        //$arFileList[] = $FileName;
        $tempfilename = preg_replace("/.jpg/", "", $FileName);
		if ($tempfilename == $input1)
			{
			$picture = "https://real-buh.ru/ViberBot/img/" . $tempfilename . ".jpg";
			//break;
			}         
}

if ($picture == "")
{  
  sendMsgText($sender_id, win2utf("Ничего не найдено. Быть может, такой карты тут и нет? Тогда можно добавить!"  . mb_convert_encoding('&#10;', 'UTF-8', 'HTML-ENTITIES')  . mb_convert_encoding('&#10;', 'UTF-8', 'HTML-ENTITIES') .  "Чтобы узнать график маршрута, напишите номер маршрута и время выезда в таком формате:" . mb_convert_encoding('&#10;', 'UTF-8', 'HTML-ENTITIES') . "2 14:55 или 2 1455"));
//  sendMsgText($sender_id, $tempfilename);
  } 
  else 
	{
	sendPic($sender_id, $input1, $type, $picture);
	sendMsgText($sender_id, win2utf("Счастливого пути!"));
	
	}
if ($text == "111"){
  sendMsgText("KclOrxuthI8ekTaQtn/m6A==", win2utf("Новое сообщение"));    
  
  }	
sendKeyboard($sender_id, $tracking_data = Null, $arr_asoc = Null);
}

function sendKeyboard($sender_id, $tracking_data = Null, $arr_asoc = Null)
{
	global $auth_token, $send_name;
  
	$data['auth_token'] = $auth_token;
	$data['receiver'] = $sender_id;

  $data['BgColor']="#ffffff";

  
  $keyboard_array['Type']='keyboard';
  $keyboard_array['DefaultHeight']=false;
  $keyboard_array['BgColor']="#7a7fff";
  $keyboard['keyboard']=$keyboard_array;
        


  
  $website['Columns']=6;
  $website['Rows']=1;
  $website['TextVAlign']="center";
  $website['TextHAlign']="center";
  $website['TextOpacity']="100";
  $website['Text']=win2utf("Добавить маршрутную карту");
  $website['TextSize']="large";
  $website['ActionType']="open-url";
  $website['ActionBody']="https://real-buh.ru/viberbot-routes.php";
  $website['BgColor']="#e0e0e0";
  $website['Image']="https://real-buh.ru/ViberBot/img/button6.png";
  $keyboard['keyboard']['Buttons'][]=$website;
  $data['keyboard']=$keyboard['keyboard'];	
	
	return sendReq($data);

}

function win2utf($s){
		$t = "";
            $c209 = chr(209); $c208 = chr(208); $c129 = chr(129);
            for($i=0; $i<strlen($s); $i++)    {
                $c=ord($s[$i]);
                if ($c>=192 and $c<=239) $t.=$c208.chr($c-48);
                elseif ($c>239) $t.=$c209.chr($c-112);
                elseif ($c==184) $t.=$c209.$c209;
                elseif ($c==168)    $t.=$c208.$c129;
                else $t.=$s[$i];
            }
            return $t;
        }

?>
