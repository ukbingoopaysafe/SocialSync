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
$result = notify($testUserId, 'test', $title, $message, null, null);

echo "<pre style=\"white-space:pre-wrap;font-family:monospace;background:#f8fafc;border:1px solid #cbd5e1;padding:12px;border-radius:8px;max-width:900px;\">";
echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
echo "</pre>";

if (!empty($result['success'])) {
    echo "Push request accepted by OneSignal. Check your device.";
} else {
    echo "Push request failed before delivery. Review the debug block above.";
}
?>
