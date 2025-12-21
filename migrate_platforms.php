<?php
/**
 * Migration: Convert platform ENUM to platforms JSON
 * 
 * This script:
 * 1. Adds new 'platforms' TEXT column
 * 2. Migrates existing platform data to JSON array format
 * 3. Drops old 'platform' column
 * 
 * Run this ONCE to migrate existing data.
 */

require_once 'config.php';

echo "<h2>🔄 Multi-Platform Migration</h2>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if migration already done
    $columns = $pdo->query("SHOW COLUMNS FROM posts LIKE 'platforms'")->fetch();
    if ($columns) {
        echo "✅ Migration already completed. 'platforms' column exists.<br>";
        exit;
    }
    
    echo "📋 Starting migration...<br><br>";
    
    // Step 1: Add new platforms column
    echo "1️⃣ Adding 'platforms' TEXT column...<br>";
    $pdo->exec("ALTER TABLE posts ADD COLUMN platforms TEXT NULL AFTER content");
    echo "   ✓ Column added<br><br>";
    
    // Step 2: Migrate data (convert single platform to JSON array)
    echo "2️⃣ Migrating existing data...<br>";
    $stmt = $pdo->query("SELECT id, platform FROM posts WHERE platform IS NOT NULL");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updateStmt = $pdo->prepare("UPDATE posts SET platforms = ? WHERE id = ?");
    $count = 0;
    
    foreach ($posts as $post) {
        $platformsJson = json_encode([$post['platform']]);
        $updateStmt->execute([$platformsJson, $post['id']]);
        $count++;
    }
    echo "   ✓ Migrated {$count} posts<br><br>";
    
    // Step 3: Drop old platform column
    echo "3️⃣ Dropping old 'platform' ENUM column...<br>";
    $pdo->exec("ALTER TABLE posts DROP COLUMN platform");
    echo "   ✓ Old column removed<br><br>";
    
    // Step 4: Drop the platform index if exists
    echo "4️⃣ Cleaning up indexes...<br>";
    try {
        $pdo->exec("DROP INDEX idx_platform ON posts");
        echo "   ✓ Index removed<br><br>";
    } catch (Exception $e) {
        echo "   ⚠ Index already removed or didn't exist<br><br>";
    }
    
    echo "<h3 style='color: green;'>✅ Migration completed successfully!</h3>";
    echo "<p>All {$count} posts now support multiple platforms.</p>";
    echo "<p><strong>Next:</strong> Refresh your browser to use the new multi-platform feature.</p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Migration failed!</h3>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
