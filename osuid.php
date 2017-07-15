<?php
$apikey=0;
class osuid {
private static function fallback($url,$api=0) {
	if (PHP_SAPI === 'cli') {
		echo "Warning:Your PHP/Network is not working properly or you use -c parameter! Use fallback mode now!\n";
	}
	$curl=curl_init();
	curl_setopt($curl,CURLOPT_URL,$url);
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
	if ($api) {
		$out=curl_exec($curl);
		curl_close($curl);
		return $out;
	}
	curl_setopt($curl,CURLOPT_HEADER,1);
	curl_setopt($curl,CURLOPT_NOBODY,1);
	curl_setopt($curl,CURLOPT_FOLLOWLOCATION,1);
	curl_exec($curl);
	$eurl=curl_getinfo($curl,CURLINFO_EFFECTIVE_URL);
	curl_close($curl);
	return $eurl;
}
public static function get_userid($username,$apikey=0) {
	global $opt;
	if ($apikey) {
		$url="https://osu.ppy.sh/api/get_user?k=$apikey&u=$username";
		if (!isset($opt['c'])) {
			$out=file_get_contents($url);
		}
		if (!isset($out)) {
			$out=self::fallback($url,1);
		}
		$json=json_decode($out);
		if (count($json)) {
			$info['userid']=$json[0]->user_id;
			$info['username']=$json[0]->username;
			return $info;
		}
	}
	stream_context_set_default(array('http'=>array('method'=>'HEAD')));
	$url="https://osu.ppy.sh/users/$username";
	if (!isset($opt['c'])) {
		$userlink=@get_headers($url,1);
	}
	if (!isset($userlink)) {
		$userlink['Location']=self::fallback($url);
	}
	$info['userid']=(isset($userlink['Location'])) ? str_replace('https://osu.ppy.sh/users/','',$userlink['Location']) : 0;
	$info['username']=$username;
	return $info;
}
}
if (PHP_SAPI === 'cli') {
	$opt=getopt('ck');
	echo "Enter Username:";
	$username=trim(fgets(STDIN));
	if (empty($username)) {
		die("Please Enter Your Username.\n");
	}
	if (isset($opt['k'])) {
		echo "Enter APIKey:";
		$apikey=trim(fgets(STDIN));
		if (!$apikey) {
			die("Please Enter Your APIKey.\n");
		}
	}
	$info=osuid::get_userid($username,$apikey);
	if (!$info['userid']) {
		die("User Not Found!\n");
	}
	$username=$info['username'];
	echo "$username's ID:".$info['userid']."\n";
}
?>
