<?php
 header("Access-Control-Allow-Origin: *");
 
 function getDevices()
    {
	  $ch = curl_init();

	  curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/players?app_id=28d085ec-8329-4505-8990-c13903364f14");
	  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 
	                                             'Authorization: Basic NjY2NmJlYjItNjMxMy00NjhmLWFhNGItNGMwZmEwMmVlNTBi'));
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	  curl_setopt($ch, CURLOPT_HEADER, FALSE);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	  $response = curl_exec($ch);

	  curl_close($ch);

	  echo $response;
	}
	
function sendNotificationById($penerima)
    {
    	$ids		= $penerima->ids;
    	$title		= $penerima->title;
    	$message	= $penerima->message;
    	$url		= $penerima->url;
    
    	if(empty($ids) || empty($title) || empty($message)) return false;

		$content = [
			"en" => $message,
			"id" => $message,
		];

		$headings = [
			"en" => $title,
			"id" => $title,
		];

		$fields = [
			'app_id' 			=> "28d085ec-8329-4505-8990-c13903364f14",
			'include_player_ids'=> [$ids],
			'data' 				=> ["foo" => "bar"],
			'url'				=> $url ? $url : '',
			'headings' 			=> $headings,
			'contents' 			=> $content,
			'android_group'  	=> $title,
			'android_group_message' => array("en" => "$[notif_count] notifikasi baru."),
		];
		
		$fields = json_encode($fields);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8', 'Authorization: Basic NjY2NmJlYjItNjMxMy00NjhmLWFhNGItNGMwZmEwMmVlNTBi']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);

		//echo $response;
    }
	
?>