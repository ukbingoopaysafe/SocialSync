<?php
/**
 * Multi-Tenancy Migration Script
 * Creates companies table and adds company_id to posts
 * Run once: http://localhost/socialsync/migrate_multitenancy.php
 */

require_once 'db.php';

echo "<html><head><title>Multi-Tenancy Migration</title>
<style>body{font-family:system-ui;max-width:700px;margin:50px auto;padding:20px;background:#0f172a;color:#e2e8f0}
h1{color:#fbbf24}.step{background:#1e293b;padding:15px;border-radius:8px;margin:10px 0}
.success{color:#22c55e}.error{color:#ef4444}.warning{color:#f59e0b}</style></head><body>";
echo "<h1>🏢 Multi-Tenancy Migration</h1>";

try {
    // Step 1: Create companies table
    echo "<div class='step'><b>1️⃣ Creating companies table...</b><br>";
    
    $tableExists = fetchOne("SHOW TABLES LIKE 'companies'");
    if ($tableExists) {
        echo "<span class='warning'>⚠️ Table already exists, skipping creation</span></div>";
    } else {
        executeQuery("
            CREATE TABLE companies (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(50) UNIQUE NOT NULL,
                logo_url VARCHAR(255),
                primary_color VARCHAR(20) DEFAULT '#1e3a5f',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "<span class='success'>✓ Table created</span></div>";
    }
    
    // Step 2: Seed companies
    echo "<div class='step'><b>2️⃣ Seeding companies data...</b><br>";
    
    $existingCompanies = fetchOne("SELECT COUNT(*) as c FROM companies")['c'] ?? 0;
    if ($existingCompanies > 0) {
        echo "<span class='warning'>⚠️ Companies already exist ({$existingCompanies}), skipping seed</span></div>";
    } else {
        executeQuery("
            INSERT INTO companies (id, name, slug, logo_url, primary_color) VALUES
            (1, 'BroMan', 'broman', 'images/Final_Logo.png', '#1e3a5f'),
            (2, 'Cible', 'cible', 'images/Logo_Cible.png', '#2563eb')
        ");
        echo "<span class='success'>✓ Seeded: BroMan (ID 1), Cible (ID 2)</span></div>";
    }
    
    // Step 3: Add company_id column to posts
    echo "<div class='step'><b>3️⃣ Adding company_id to posts table...</b><br>";
    
    $columnExists = fetchOne("SHOW COLUMNS FROM posts LIKE 'company_id'");
    if ($columnExists) {
        echo "<span class='warning'>⚠️ Column already exists</span></div>";
    } else {
        executeQuery("ALTER TABLE posts ADD COLUMN company_id INT NOT NULL DEFAULT 1 AFTER id");
        echo "<span class='success'>✓ Column added</span></div>";
    }
    
    // Step 4: Migrate existing posts to BroMan
    echo "<div class='step'><b>4️⃣ Migrating existing posts to BroMan...</b><br>";
    
    $postsToMigrate = fetchOne("SELECT COUNT(*) as c FROM posts WHERE company_id = 1 OR company_id IS NULL")['c'];
    executeQuery("UPDATE posts SET company_id = 1 WHERE company_id IS NULL OR company_id = 0");
    echo "<span class='success'>✓ All {$postsToMigrate} posts assigned to BroMan (ID 1)</span></div>";
    
    // Step 5: Add foreign key (optional, may fail if data integrity issues)
    echo "<div class='step'><b>5️⃣ Adding foreign key constraint...</b><br>";
    try {
        // Check if constraint exists
        $fkExists = fetchOne("
            SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'posts' 
            AND COLUMN_NAME = 'company_id' 
            AND REFERENCED_TABLE_NAME = 'companies'
        ");
        
        if ($fkExists) {
            echo "<span class='warning'>⚠️ Foreign key already exists</span></div>";
        } else {
            executeQuery("ALTER TABLE posts ADD CONSTRAINT fk_posts_company FOREIGN KEY (company_id) REFERENCES companies(id)");
            echo "<span class='success'>✓ Foreign key added</span></div>";
        }
    } catch (Exception $e) {
        echo "<span class='warning'>⚠️ Could not add foreign key (non-critical): " . $e->getMessage() . "</span></div>";
    }
    
    // Step 6: Add index for performance
    echo "<div class='step'><b>6️⃣ Adding index for performance...</b><br>";
    try {
        executeQuery("CREATE INDEX idx_posts_company ON posts(company_id)");
        echo "<span class='success'>✓ Index created</span></div>";
    } catch (Exception $e) {
        echo "<span class='warning'>⚠️ Index may already exist</span></div>";
    }
    
    // Summary
    $companies = fetchAll("SELECT * FROM companies");
    $postCounts = fetchAll("SELECT company_id, COUNT(*) as count FROM posts GROUP BY company_id");
    
    echo "<div class='step' style='background:#064e3b;border:2px solid #22c55e'>
        <h2 style='color:#22c55e;margin:0'>✅ Migration Completed Successfully!</h2>
        <p><b>Companies:</b></p>
        <ul>";
    foreach ($companies as $c) {
        echo "<li>{$c['name']} (ID: {$c['id']}) - Logo: {$c['logo_url']}</li>";
    }
    echo "</ul>
        <p><b>Posts per Company:</b></p>
        <ul>";
    foreach ($postCounts as $pc) {
        $companyName = $pc['company_id'] == 1 ? 'BroMan' : 'Cible';
        echo "<li>{$companyName}: {$pc['count']} posts</li>";
    }
    echo "</ul>
        <p><b>Next:</b> Log out and log in again. You'll see company selection on login page.</p>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='step' style='background:#7f1d1d;border:2px solid #ef4444'>
        <span class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</span>
    </div>";
}

echo "</body></html>";
