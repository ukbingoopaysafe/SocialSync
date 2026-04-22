<?php
require_once 'config.php';
require_once 'db.php';

// This will trigger the notify() function which sends the OneSignal push
require_once 'api.php';

$testUserId = 7; // The user ID that you are logged in as
$title = "Test Notification 🚀";
$message = "If you see this, OneSignal is working perfectly on brman.online!";

echo "Sending push notification to User ID: " . $testUserId . " ...<br>";

// We call your updated notify function
notify($testUserId, 'test', $title, $message, null, null);

echo "Notification sent! Check your screen.";
?>
