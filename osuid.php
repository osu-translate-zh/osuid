<?php
$apiKey=0;
error_reporting(0);
define('UserAgent','osuid');
define('osuAPIPrefix','https://osu.ppy.sh/api/');
define('UserLinkPrefix','https://osu.ppy.sh/u/');
define('NewUserLinkPrefix','https://osu.ppy.sh/users/');
ini_set('user_agent',UserAgent);
$isWarned=0;
class osuid {
	private static function fallback($url,$api=0) {
		global $isWarned;
		if (!$isWarned && strtolower(PHP_SAPI) === 'cli') {
			$isWarned=1;
			echo "Warning: Your PHP/Network is not working properly or you use -c parameter! Use fallback mode now!\n";
		}
		$curl=curl_init();
		curl_setopt($curl,CURLOPT_URL,$url);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl,CURLOPT_USERAGENT,UserAgent);
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
	public static function setUserLink($userID) {
		$userLink=UserLinkPrefix."{$userID}";
		$newUserLink=NewUserLinkPrefix."{$userID}";
		return array($userLink,$newUserLink);
	}
	public static function getUserInfo($usernameArr,$apiKey=0) {
		global $opt;
		$allUsersInfo=array();
		foreach ($usernameArr as $username) {
			$username=trim($username);
			if (!empty($apiKey)) {
				$url=osuAPIPrefix."get_user?k={$apiKey}&u={$username}";
				if (!isset($opt['c'])) {
					$out=file_get_contents($url);
				}
				if (empty($out)) {
					$out=self::fallback($url,1);
				}
				$json=json_decode($out);
				if (isset($json[0]->username)) {
					$username=$json[0]->username;
				}
				$allUsersInfo[$username]['UserID']=(isset($json[0]->user_id)) ? $json[0]->user_id : 0;
				if (empty($allUsersInfo[$username]['UserID'])) {
					continue;
				}
				if (isset($json[0]->country)) {
					$allUsersInfo[$username]['Country']=strtoupper($json[0]->country);
				}
				if (isset($json[0]->pp_raw)) {
					$allUsersInfo[$username]['Performance Point']=strtoupper($json[0]->pp_raw);
				}
			} else {
				stream_context_set_default(array('http'=>array('method'=>'HEAD')));
				$url="http://osu.ppy.sh/users/{$username}";
				if (!isset($opt['c'])) {
					$userLinkHeaders=get_headers($url,1);
				}
				if (!$userLinkHeaders) {
					$userLinkHeaders['Location']=self::fallback($url);
				}
				$allUsersInfo[$username]['UserID']=(isset($userLinkHeaders['Location'])) ? str_replace('https://osu.ppy.sh/users/','',$userLinkHeaders['Location']) : 0;
			}
			unset($url,$out,$json,$userLinkHeaders);
		}
		return $allUsersInfo;
	}
	public static function getMarkdownFormat($username,$userLink,$country=0) {
		$markdownFormat="[{$username}]({$userLink})";
		if ($country) {
			$markdownFormat="![][flag_{$country}] {$markdownFormat}";
		}
		return $markdownFormat;
	}
}
if (PHP_SAPI === 'cli') {
	$opt=getopt('ck');
	echo "Enter Username: ";
	$input=trim(fgets(STDIN));
	if (empty($input)) {
		die("Please Enter Your Username.\n");
	}
	$usernameArr=explode(',',$input);
	if (file_exists('APIKey')) {
		$apiKey=trim(file_get_contents('APIKey'));
	} elseif (isset($opt['k'])) {
		echo "Enter APIKey:";
		$apiKey=trim(fgets(STDIN));
		if (!$apiKey) {
			die("Please Enter Your APIKey.\n");
		}
	}
	$allUsersInfo=osuid::getUserInfo($usernameArr,$apiKey);
	if (!$allUsersInfo || count($allUsersInfo) < 1) {
		die("User Not Found!\n");
	}
	foreach ($allUsersInfo as $username => $userInfo) {
		echo "{$username}:\n";
		list($userInfo['UserLink'],$userInfo['NewUserLink'])=osuid::setUserLink($userInfo['UserID']);
		$userInfo['Markdown Format']=osuid::getMarkdownFormat($username,$userInfo['NewUserLink'],((isset($userInfo['Country'])) ? $userInfo['Country'] : 0));
		if (empty($userInfo['UserID'])) {
			echo "	User Not Found!\n";
			continue;
		}
		foreach ($userInfo as $key => $value) {
			echo "	{$key}: {$value}\n";
		}
	}
}
?>
