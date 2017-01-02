<?php
	define("key", "PhimMoi.Net://");
	header('Content-Type: text/json; charset=utf-8');
	require_once 'aes_decrypt.class.php';

	if (isset($_GET['url']))
	{
		$ConstLink = $_GET['url'];
		print_r(FindStreamUrl($ConstLink));
	}

	function FindStreamUrl($film)	
	{
		$PageFilm = cURL($film);
		preg_match_all("/<script async=\"true\" src=\"(.*?)\"/", $PageFilm, $Info);
		$Callback = cURL($Info[1][2]);

		preg_match("/\"episodeId\":(.*?),/", $Callback, $EpisodeID);
		$EpisodeID = $EpisodeID[1];

		preg_match_all("/\"resolution\":(.*?),\"type\":\"(.*?)\",\"width\":(.*?),\"height\":(.*?),\"url\":\"(.*?)\"/", $Callback, $StreamInfo);
		$Resolution = $StreamInfo[1];
		$Type = $StreamInfo[2];
		$Width = $StreamInfo[3];
		$Height = $StreamInfo[4];
		$StreamUrl = $StreamInfo[5];

		$Result = Array();
		for ($i = 0; $i < count($StreamUrl); $i++)
		{
			$Result[$Resolution[$i]]['type'] = $Type[$i];
			$Result[$Resolution[$i]]['width'] = $Width[$i];
			$Result[$Resolution[$i]]['height'] = $Height[$i];
			$Result[$Resolution[$i]]['stream_url'] = GibberishAES::dec($StreamUrl[$i], constant("key").$EpisodeID);
		}

		return json_encode($Result, JSON_PRETTY_PRINT);
	}

	function cURL($url, $postArray = array(), $setopt = array())
	{
		$opts = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_COOKIEFILE => $cookie,
			CURLOPT_COOKIEJAR => $cookie,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_HEADER => false,
			CURLOPT_FRESH_CONNECT => true,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.52 Safari/537.36',
			CURLOPT_REFERER => $url
		);
		if(count($postArray) > 0 && $postArray != false){
			$postFields = array(
				'POST' => true, 
				'POSTFIELDS' => http_build_query($postArray),
				'REFERER' => $url
			);
			$setopt = array_merge($setopt, $postFields);
		}
		foreach($setopt as $key => $value){
			$opts[constant('CURLOPT_'.strtoupper($key))] = $value;
		}
		
		$s = curl_init();
		curl_setopt_array($s, $opts);
		$data = curl_exec($s);
		curl_close($s);
		@unlink($cookie);
		return $data;
	}
?>
