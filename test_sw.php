<?php
$url = "https://broman.online/OneSignalSDKWorker.js";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
// timeout
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if(curl_errno($ch)){
    echo "cURL error: " . curl_error($ch) . "\n";
} else {
    echo "HTTP Status: " . $httpcode . "\n";
    echo "Response:\n";
    echo substr($response, 0, 500); 
}
curl_close($ch);
?>
