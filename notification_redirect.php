<?php
require_once 'config.php';
require_once 'db.php';
require_once 'includes/security.php';

startAppSession();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$launchCompanyId = max(0, (int) ($_GET['notification_company_id'] ?? 0));
$launchPostId = max(0, (int) ($_GET['notification_post_id'] ?? 0));
$launchType = preg_replace('/[^a-z0-9_]/i', '', (string) ($_GET['notification_type'] ?? ''));

if ($launchCompanyId > 0 && (int) ($_SESSION['company_id'] ?? 0) !== $launchCompanyId) {
    $notificationCompany = fetchOne("SELECT id, name, logo_url FROM companies WHERE id = ?", [$launchCompanyId]);
    if ($notificationCompany) {
        $_SESSION['company_id'] = (int) $notificationCompany['id'];
        $_SESSION['company_name'] = $notificationCompany['name'];
        $_SESSION['company_logo'] = $notificationCompany['logo_url'] ?? 'images/Final_Logo White.png';
    }
}

$_SESSION['notification_launch'] = [
    'company_id' => $launchCompanyId,
    'post_id' => $launchPostId,
    'type' => $launchType,
];

$targetHash = (strpos($launchType, 'submission_') === 0 && $launchPostId === 0) ? '#submissions' : '#board';

header('Location: index.php' . $targetHash);
exit;
