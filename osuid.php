<?php
$apiKey=0;
$userAgent='osuid';
error_reporting(0);
ini_set('user_agent',$userAgent);
define('osuAPIPrefix','https://osu.ppy.sh/api/');
define('UserLinkPrefix','https://osu.ppy.sh/u/');
define('NewUserLinkPrefix','https://osu.ppy.sh/users/');
class osuid {
	private static function fallback($url,$api=0) {
		global $userAgent;
		if (strtolower(PHP_SAPI) === 'cli') {
			echo "Warning: Your PHP/Network is not working properly or you use -c parameter! Use fallback mode now!\n";
		}
		$curl=curl_init();
		curl_setopt($curl,CURLOPT_URL,$url);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl,CURLOPT_USERAGENT,$userAgent);
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
	public static function get_userid($username,$apiKey=0) {
		global $opt;
		if (!empty($apiKey)) {
			$url=osuAPIPrefix."get_user?k={$apiKey}&u={$username}";
			if (!isset($opt['c'])) {
				$out=file_get_contents($url);
			}
			if (!isset($out)) {
				$out=self::fallback($url,1);
			}
			$json=json_decode($out);
			$info['UserID']=(isset($json[0]->user_id)) ? $json[0]->user_id : 0;
			$info['Username']=(isset($json[0]->username)) ? $json[0]->username : $username;
			if (isset($json[0]->country)) {
				$info['Country']=strtoupper($json[0]->country);
			}
			return $info;
		}
		stream_context_set_default(array('http'=>array('method'=>'HEAD')));
		$url="http://osu.ppy.sh/users/{$username}";
		if (!isset($opt['c'])) {
			$userLinkHeaders=get_headers($url,1);
		}
		if (!$userLinkHeaders) {
			$userLinkHeaders['Location']=self::fallback($url);
		}
		$info['UserID']=(isset($userLinkHeaders['Location'])) ? str_replace('https://osu.ppy.sh/users/','',$userLinkHeaders['Location']) : 0;
		$info['Username']=$username;
		return $info;
	}
}
if (PHP_SAPI === 'cli') {
	$opt=getopt('ck');
	echo "Enter Username: ";
	$username=trim(fgets(STDIN));
	if (empty($username)) {
		die("Please Enter Your Username.\n");
	}
	if (file_exists('APIKey')) {
		$apiKey=trim(file_get_contents('APIKey'));
	} elseif (isset($opt['k'])) {
		echo "Enter APIKey:";
		$apiKey=trim(fgets(STDIN));
		if (!$apiKey) {
			die("Please Enter Your APIKey.\n");
		}
	}
	$info=osuid::get_userid($username,$apiKey);
	if (!$info['UserID']) {
		die("User Not Found!\n");
	}
	$username=$info['Username'];
	unset($info['Username']);
	$userLink=UserLinkPrefix."{$info['UserID']}";
	$newUserLink=NewUserLinkPrefix."{$info['UserID']}";
	$markdownFormat="[{$username}]({$newUserLink})";
	if (isset($info['Country'])) {
		$markdownFormat="![][flag_{$info['Country']}] {$markdownFormat}";
	}
	foreach ($info as $key => $value) {
		echo "{$username}'s {$key}: {$value}\n";
	}
	echo "{$username}'s UserLink: {$userLink}\n";
	echo "{$username}'s NewUserLink: {$newUserLink}\n";
	echo "Markdown Format: {$markdownFormat}\n";
}
?>
