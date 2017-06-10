<?php
    print("Enter username:");

    $username = trim(fgets(STDIN));
    $userid = '';

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, "https://osu.ppy.sh/users/{$username}");
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $data = curl_exec($curl);

    curl_close($curl);

    if(0 == preg_match('/"https:\\/\\/osu.ppy.sh\\/users\\/([0-9]*)"/', $data, $userid)){
        die('User Not Found!');
    }

    print("Userid:");
    print_r($userid[1]);
    print("\n")
?>
