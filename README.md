# SocialSync - Social Media Team Management

A lightweight, fast, and efficient social media content management system for collaborative team workflows.

## 🚀 Features

- **Role-Based Access Control**: Separate permissions for Creators and Approvers
- **Kanban Board View**: Visual workflow from Ideas to Scheduled posts
- **Weekly Calendar View**: Schedule and organize posts by week with drag-and-drop interface
- **Fast Approval Process**: One-click approve/reject with quick rejection reasons
- **Smart Filtering**: Filter by platform, status, urgency, creator, and search
- **Urgent Posts**: Flag important posts for priority handling
- **Multi-Platform Support**: Facebook, Instagram, LinkedIn, Twitter, TikTok, Website

## 📋 Requirements

- PHP 8.1 or higher
- MySQL 5.7 or MariaDB 10.2+
- Apache or Nginx web server
- Shared hosting compatible (Hostinger, cPanel, etc.)

## 🛠️ Installation

### Step 1: Upload Files

Upload all files to your web hosting via FTP:
```
/public_html/socialsync/
├── schema.sql
├── config.php
├── db.php
├── api.php
├── login.php
├── index.php
└── README.md
```

### Step 2: Create Database

1. Login to your hosting control panel (cPanel, Hostinger Panel, etc.)
2. Go to **MySQL Databases** or **phpMyAdmin**
3. Create a new database (e.g., `socialsync_db`)
4. Create a database user with all privileges
5. Import `schema.sql`:
   - In phpMyAdmin, select your database
   - Click "Import" tab
   - Choose `schema.sql` file
   - Click "Go"

### Step 3: Configure Database Connection

Edit `config.php` and update these lines with your database credentials:

```php
define('DB_HOST', 'localhost');        // Your database host
define('DB_NAME', 'socialsync_db');    // Your database name
define('DB_USER', 'your_db_user');     // Your database username
define('DB_PASS', 'your_db_password'); // Your database password
```

**Important Security Settings:**
```php
// If your site uses HTTPS, set this to true
define('SESSION_COOKIE_SECURE', true);

// For production, change environment to 'production'
define('ENVIRONMENT', 'production');
```

### Step 4: Create User Accounts

You need to create user accounts with hashed passwords. Run this PHP script once to generate password hashes:

**create_users.php** (temporary file, delete after use):
```php
<?php
require_once 'db.php';

// Create Creator Account
$username1 = 'creator1';

$password1 = 'your_secure_password_here';  // Change this!
$hash1 = password_hash($password1, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, role, password_hash) VALUES (?, 'creator', ?)";
executeQuery($sql, [$username1, $hash1]);

// Create Approver Account
$username2 = 'approver1';

$password2 = 'your_secure_password_here';  // Change this!
$hash2 = password_hash($password2, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, role, password_hash) VALUES (?, 'approver', ?)";
executeQuery($sql, [$username2, $hash2]);

echo "Users created successfully!";
?>
```

Run this file once in your browser: `https://yourdomain.com/socialsync/create_users.php`

Then **DELETE** the file for security.

### Step 5: Access Application

Visit: `https://yourdomain.com/socialsync/`

You'll be redirected to the login page. Use the credentials you created in Step 4.

## 👥 User Roles & Permissions

### Creator
- Create and edit their own posts
- Submit posts for review
- Mark posts as urgent
- View all posts
- Cannot approve/reject posts

### Approver
- View all posts
- Approve or reject posts with reasons
- Make minor edits to any post
- Cannot delete others' posts

## 📱 How to Use

### Creating a Post

1. Click **"+ New Post"** button
2. Fill in:
   - Title (required)
   - Content (required)
   - Platform (required)
   - Scheduled Date & Time
   - Image URL (optional)
   - Urgency checkbox if needed
3. Select status:
   - **Idea**: Just brainstorming
   - **Draft**: Work in progress
   - **Pending Review**: Ready for approval
4. Click **"Save Post"**

### Approving Posts (Approvers Only)

**Method 1: Quick Approval from Board**
- In Kanban view, posts in "Pending Review" column show Approve/Reject buttons
- Click **✓ Approve** or **✗ Reject**

**Method 2: Detailed Review**
- Click on any post card to open the editor
- Review content and make minor edits if needed
- Change status to "Approved" or "Rejected"
- Save

### Using Filters

**Sidebar Filters:**
- **All Posts**: Show everything
- **My Posts**: Only your created posts
- **Pending Review**: Posts waiting for approval
- **Urgent**: Posts marked as urgent
- **Platform dropdown**: Filter by social media platform
- **Search box**: Search by title or content

### Calendar View

1. Click **"📅 Calendar"** in sidebar
2. View posts scheduled for the current week
3. Navigate weeks with Previous/Next buttons
4. Click on any post to edit
5. See posts grouped by day with time stamps

## 🔧 Configuration Options

Edit `config.php` to customize:

```php
// Session lifetime (default: 24 hours)
define('SESSION_LIFETIME', 3600 * 24);

// Timezone (change to your timezone)
define('TIMEZONE', 'Africa/Cairo');

// Week start day (1 = Monday, 0 = Sunday)
define('WEEK_START_DAY', 1);
```

## 🔒 Security Notes

1. **Always use HTTPS** in production
2. **Set strong passwords** for all user accounts
3. **Set ENVIRONMENT to 'production'** in config.php for live sites
4. **Keep database credentials secure** - never commit config.php to version control
5. **Regularly backup** your database
6. **Update PHP** to the latest stable version

## 📊 Database Backup

Regular backups recommended! Use phpMyAdmin or command line:

```bash
mysqldump -u username -p socialsync_db > backup.sql
```

## 🐛 Troubleshooting

### "Database Connection Failed"
- Check config.php credentials
- Verify database exists
- Ensure database user has proper permissions

### "Login not working"
- Clear browser cookies
- Verify passwords were hashed correctly
- Check session settings in config.php

### "Posts not showing"
- Check browser console for JavaScript errors
- Verify api.php is accessible
- Test API directly: `yourdomain.com/socialsync/api.php?action=get_user`

### "500 Internal Server Error"
- Check PHP error logs
- Ensure PHP 8.1+ is installed
- Verify all files uploaded correctly

## 🚀 Next Steps (Future Enhancements)

- Add Manager and Scheduler roles
- Implement comments system
- Add revision history
- AI content generation integration
- File upload functionality with cloud storage
- Team activity analytics
- Email notifications
- Batch operations
- Export reports (PDF/Excel)

## 📞 Support

For issues or questions, check:
1. This README
2. Browser console for errors
3. PHP error logs on your server

## 📄 License

Copyright © 2025. All rights reserved.

---

**Built with ❤️ using PHP, MySQL, and Vanilla JavaScript**
