<?php
/**
 * User Creation Script
 * 
 * Run this file ONCE to create initial user accounts
 * Then DELETE this file for security
 * 
 * Usage: Visit this file in your browser, then delete it
 */

require_once 'config.php';
require_once 'db.php';

// Prevent running multiple times
if (fetchOne("SELECT COUNT(*) as count FROM users")['count'] > 0) {
    die("Users already exist! Delete this file for security.");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Users - SocialSync</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Create User Accounts</h1>
            
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-lg mb-6">
                <strong>⚠️ Important:</strong> After creating users, DELETE this file for security!
            </div>

            <form method="POST" class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-700 border-b pb-2">Creator Account</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <input type="text" name="creator_username" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="creator_email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" name="creator_password" required minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 pt-4">Approver Account</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <input type="text" name="approver_username" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="approver_email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" name="approver_password" required minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-lg hover:bg-indigo-700">
                    Create Users
                </button>
            </form>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    // Create Creator
                    $creator_username = trim($_POST['creator_username']);
                    $creator_email = trim($_POST['creator_email']);
                    $creator_password = $_POST['creator_password'];
                    $creator_hash = password_hash($creator_password, PASSWORD_DEFAULT);
                    
                    $sql = "INSERT INTO users (username, email, role, password_hash) VALUES (?, ?, 'creator', ?)";
                    executeQuery($sql, [$creator_username, $creator_email, $creator_hash]);
                    
                    // Create Approver
                    $approver_username = trim($_POST['approver_username']);
                    $approver_email = trim($_POST['approver_email']);
                    $approver_password = $_POST['approver_password'];
                    $approver_hash = password_hash($approver_password, PASSWORD_DEFAULT);
                    
                    $sql = "INSERT INTO users (username, email, role, password_hash) VALUES (?, ?, 'approver', ?)";
                    executeQuery($sql, [$approver_username, $approver_email, $approver_hash]);
                    
                    echo '<div class="mt-6 bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg">';
                    echo '<strong>✓ Success!</strong> Users created successfully.<br>';
                    echo '<strong style="color: red;">DELETE THIS FILE NOW!</strong><br><br>';
                    echo 'Creator: <strong>' . htmlspecialchars($creator_username) . '</strong><br>';
                    echo 'Approver: <strong>' . htmlspecialchars($approver_username) . '</strong><br><br>';
                    echo '<a href="login.php" class="text-indigo-600 font-semibold">Go to Login →</a>';
                    echo '</div>';
                    
                } catch (Exception $e) {
                    echo '<div class="mt-6 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg">';
                    echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage());
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>
</body>
</html>
