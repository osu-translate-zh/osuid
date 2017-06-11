<?php
error_reporting(0);
print("Enter Username:");
$username=trim(fgets(STDIN));
if ($username === '') { die("Please Enter Your Username.\n"); }
$curl=curl_init();
curl_setopt($curl,CURLOPT_URL,"https://osu.ppy.sh/users/$username");
curl_setopt($curl,CURLOPT_HEADER,1);
curl_setopt($curl,CURLOPT_NOBODY,1);
curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
curl_setopt($curl,CURLOPT_FOLLOWLOCATION,1);
curl_exec($curl);
$effective_url=curl_getinfo($curl,CURLINFO_EFFECTIVE_URL);
curl_close($curl);
$userid=str_replace('https://osu.ppy.sh/users/','',$effective_url);
if (!is_numeric($userid)) { die("User Not Found!\n"); }
echo "{$username}'s ID:{$userid}\n";
?>
