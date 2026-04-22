<?php
$url = "https://broman.online/OneSignalSDKWorker.js?appId=9748ea3b-8a42-4279-b664-e6ab00d9756e";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if(curl_errno($ch)){
    echo "cURL error: " . curl_error($ch) . "\n";
} else {
    echo "HTTP Status: " . $httpcode . "\n";
    echo "Response:\n";
}
curl_close($ch);
?>
