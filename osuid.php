<?php
error_reporting(0);
$apikey=0;
function get_userid($username,$apikey) {
	if ($apikey) {
		$json=json_decode(file_get_contents("https://osu.ppy.sh/api/get_user?k=$apikey&u=$username"));
		if (count($json)) {
			$info['userid']=$json[0]->user_id;
			$info['username']=$json[0]->username;
			return $info;
		}
	}
	stream_context_set_default(array('http'=>array('method'=>'HEAD')));
	$info['userid']=str_replace('https://osu.ppy.sh/users/','',get_headers("https://osu.ppy.sh/users/$username",1)['Location']);
	return $info;
}
echo "Enter Username:";
$username=trim(fgets(STDIN));
if (empty($username)) {
	die("Please Enter Your Username.\n");
}
if (isset(getopt('k')['k'])) {
	echo "Enter APIKey:";
	$apikey=trim(fgets(STDIN));
	if (!$apikey) {
		die("Please Enter Your APIKey.\n");
	}
}
$info=get_userid($username,$apikey);
if (!$info['userid']) {
	die("User Not Found!\n");
}
$username=(!$info['username']) ? $username : $info['username'];
echo "$username's ID:".$info['userid']."\n";
?>
