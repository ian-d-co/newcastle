# Dicksord Fest 2026 - Newcastle
# Hostinger Deployment Setup Guide

Complete step-by-step guide for deploying the Newcastle event management system to Hostinger shared hosting.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Git Deployment Setup](#git-deployment-setup)
3. [Environment Configuration](#environment-configuration)
4. [Database Setup](#database-setup)
5. [File Permissions](#file-permissions)
6. [HTTPS/SSL Setup](#httpsssl-setup)
7. [Routing Configuration](#routing-configuration)
8. [Testing](#testing)
9. [Troubleshooting](#troubleshooting)
10. [Security Checklist](#security-checklist)
11. [Git Auto-Deployment](#git-auto-deployment)

---

## Prerequisites

Before starting deployment, ensure you have:

- **Hostinger Hosting Account** (Business or higher recommended for SSH access)
- **GitHub Repository Access** to `ian-d-co/newcastle`
- **SSH Access** (if available on your plan)
- **FTP/SFTP Credentials** (alternative to SSH)
- **MySQL Database** (created via Hostinger control panel)
- **PHP 7.4 or higher** (check in control panel)
- **PDO MySQL extension** (usually enabled by default)

### Verify PHP Requirements

1. Login to Hostinger control panel
2. Navigate to **Advanced** → **PHP Configuration**
3. Verify PHP version is 7.4 or higher
4. Ensure these extensions are enabled:
   - PDO
   - pdo_mysql
   - json
   - mbstring
   - session

---

## Git Deployment Setup

### Option 1: Using SSH (Recommended)

If your Hostinger plan includes SSH access:

1. **Access SSH Terminal**
   ```bash
   ssh u983097270@your-domain.com
   # Use SSH credentials from Hostinger control panel
   ```

2. **Navigate to web root**
   ```bash
   cd public_html
   # Or cd domains/your-domain.com/public_html
   ```

3. **Clone the repository**
   ```bash
   git clone https://github.com/ian-d-co/newcastle.git temp_newcastle
   cd temp_newcastle
   ```

4. **Move files to web root**
   ```bash
   # Move public folder contents to web root
   mv public/* ../
   mv public/.htaccess ../
   
   # Move app, database folders to parent (outside web root for security)
   mv app ../../
   mv database ../../
   
   # Clean up
   cd ..
   rm -rf temp_newcastle
   ```
   
   **Expected File Structure:**
   ```
   domains/your-domain.com/
   ├── app/                    # Application code (secure, outside web root)
   │   ├── config/
   │   │   ├── .env.example   # Environment template
   │   │   └── config.php
   │   ├── controllers/
   │   ├── models/
   │   └── views/
   ├── database/               # Database schemas (secure, outside web root)
   └── public_html/           # Web root (publicly accessible)
       ├── index.php          # Main entry point with auto-detection
       ├── .htaccess
       ├── css/
       ├── js/
       └── api/
   ```

5. **Automatic Path Detection**
   The application now includes intelligent path detection in `index.php`. It will automatically locate the `app` directory whether you're using:
   - Standard repo structure: `repo_root/public/` as web root
   - Hostinger structure: `public_html/` as web root with `app/` in parent
   
   No manual configuration needed! The system checks multiple locations and logs the detected path.

### Option 2: Using FTP/SFTP

If SSH is not available:

1. **Clone repository locally**
   ```bash
   git clone https://github.com/ian-d-co/newcastle.git
   ```

2. **Connect via FTP client** (FileZilla, Cyberduck, etc.)
   - Host: your-domain.com (or FTP hostname from Hostinger)
   - Username: Your FTP username
   - Password: Your FTP password
   - Port: 21 (FTP) or 22 (SFTP)

3. **Upload directory structure**
   - Upload `public/` folder contents to `public_html/`
   - Upload `app/` folder to parent of `public_html/` (e.g., `domains/your-domain.com/`)
   - Upload `database/` folder to same location as `app/`
   
   **Important:** The application will automatically detect the `app` directory location, so no manual path configuration is needed.

4. **Verify structure**
   ```
   domains/your-domain.com/
   ├── app/
   ├── database/
   └── public_html/
       ├── index.php
       ├── .htaccess
       ├── css/
       ├── js/
       └── api/
   ```

---

## Environment Configuration

1. **Copy environment template**
   
   The `.env` file should be placed in `app/config/.env` (this is the recommended and primary location):
   
   ```bash
   cd /path/to/domains/your-domain.com/
   cp app/config/.env.example app/config/.env
   ```
   
   **Note:** The application configuration looks for `.env` in `app/config/` first, and falls back to the root directory for backward compatibility. However, for security and organization, **always use `app/config/.env`** as documented in this guide.

2. **Edit .env file** with your database credentials
   ```bash
   nano app/config/.env
   # or use File Manager in Hostinger control panel
   ```

3. **Update these values**:
   ```ini
   # Application Configuration
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   TIMEZONE=Europe/London
   
   # Database Configuration (from Hostinger control panel)
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=u983097270_newc
   DB_USER=u983097270_newc
   DB_PASSWORD=your_secure_password_here
   
   # Session Configuration
   SESSION_NAME=dicksord_fest_2026
   SESSION_LIFETIME=3600
   
   # Security
   CSRF_TOKEN_NAME=csrf_token
   ```

4. **Important notes**:
   - `DB_HOST` is usually `localhost` for Hostinger shared hosting
   - `DB_NAME` and `DB_USER` follow format: `u[userid]_[name]`
   - Never set `APP_DEBUG=true` in production
   - Update `APP_URL` to your actual domain with `https://`

---

## Database Setup

### Create Database via Hostinger Control Panel

1. **Login to Hostinger control panel**
2. **Navigate to Databases → MySQL Databases**
3. **Create new database**:
   - Database name: `newcastle` (will become `u983097270_newc`)
   - Create new user or use existing
   - Set strong password
   - Grant all privileges
4. **Note your credentials**:
   - Database name (with user prefix)
   - Username (same as database name)
   - Password
   - Host (usually `localhost`)

### Import Database Schema

**Option 1: Using phpMyAdmin (Recommended)**

1. Access **phpMyAdmin** from Hostinger control panel
2. Select your database from left sidebar
3. Click **Import** tab
4. Choose file: `database/schema.sql`
5. Ensure format is **SQL**
6. Click **Go**
7. Verify tables were created successfully

**Option 2: Using Command Line (if SSH available)**

```bash
mysql -u u983097270_newc -p u983097270_newc < database/schema.sql
# Enter password when prompted
```

### Verify Database Import

1. In phpMyAdmin, check that these tables exist:
   - `users`
   - `events`
   - `event_attendees`
   - `activities`
   - `activity_bookings`
   - `meals`
   - `meal_bookings`
   - `polls`
   - `poll_options`
   - `poll_votes`
   - `carshare_offers`
   - `carshare_bookings`
   - `hosting_offers`
   - `hosting_bookings`
   - `hotels`
   - `hotel_rooms`
   - `hotel_reservations`

2. Verify default admin user exists:
   - Discord Name: `Admin`
   - PIN: `123456` (change immediately after first login!)

---

## File Permissions

Set appropriate permissions for security:

### Using SSH

```bash
# Navigate to deployment directory
cd /path/to/domains/your-domain.com/

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Restrict .env file (more secure)
chmod 600 app/config/.env

# Make .htaccess readable
chmod 644 public_html/.htaccess
```

### Using FTP/File Manager

- **Directories**: 755
- **PHP files**: 644
- **`app/config/.env` file**: 600
- **`.htaccess` file**: 644

### Verify Permissions

```bash
ls -la app/config/
# Should show:
# -rw-------  .env
# -rw-r--r--  .env.example
# -rw-r--r--  config.php
```

---

## HTTPS/SSL Setup

### Enable SSL Certificate (Free with Hostinger)

1. **Login to Hostinger control panel**
2. **Navigate to SSL**
3. **Select your domain**
4. **Install SSL certificate** (Hostinger provides free Let's Encrypt)
5. **Wait for activation** (usually 10-15 minutes)

### Force HTTPS Redirect

The `.htaccess` file in `public/` directory already includes HTTPS redirect rules:

```apache
# Force HTTPS (uncomment in production)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

**To enable**:
1. Edit `public/.htaccess`
2. Uncomment the three lines above
3. Save and test

### Update Configuration

Update `.env` file:
```ini
APP_URL=https://your-domain.com
```

### Test SSL

1. Visit `https://your-domain.com`
2. Check for padlock icon in browser
3. Verify certificate is valid
4. Test HTTP redirect: `http://your-domain.com` should redirect to HTTPS

---

## Routing Configuration

The application uses `.htaccess` for URL routing. This is already configured in `public/.htaccess`.

### Default Configuration

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Redirect to HTTPS (uncomment in production)
    # RewriteCond %{HTTPS} off
    # RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Route all requests to index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?page=$1 [QSA,L]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Prevent access to sensitive files
<FilesMatch "\.(env|sql|md|git)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Enable caching for static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### Subdirectory Installation

If installing in a subdirectory (e.g., `yourdomain.com/dicksord`):

1. Edit `.htaccess`
2. Change `RewriteBase /` to `RewriteBase /dicksord/`
3. Update `.env`: `APP_URL=https://yourdomain.com/dicksord`

---

## Testing

### Comprehensive Testing Checklist

#### 1. Database Connection
- [ ] Visit site homepage - should load without errors
- [ ] Check for database connection errors

#### 2. Login System
- [ ] Navigate to `/index.php?page=login`
- [ ] Login with default credentials:
  - Discord Name: `Admin`
  - PIN: `123456`
- [ ] Should redirect to home page
- [ ] Verify session persists on page navigation

#### 3. User Registration
- [ ] Click "I am attending!" on home page
- [ ] Fill registration form with test data
- [ ] Submit form
- [ ] Verify registration success message
- [ ] Check database: new user in `users` table
- [ ] Check attendance record in `event_attendees` table

#### 4. User Features
- [ ] **Activities**: View and book activity
- [ ] **Meals**: View and book meal
- [ ] **Carshare**: Create offer or book ride
- [ ] **Hosting**: Create offer or book accommodation
- [ ] **Polls**: Vote on poll (if available)
- [ ] **Hotels**: Reserve hotel room (if available)
- [ ] **Dashboard**: View all bookings and registrations

#### 5. Admin Panel
- [ ] Login as admin
- [ ] Access `/index.php?page=admin`
- [ ] Verify statistics display
- [ ] Check recent attendees list
- [ ] Test admin functions (if implemented)

#### 6. Security Tests
- [ ] Test CSRF protection: Submit form without token (should fail)
- [ ] Test authentication: Access protected page without login (should redirect)
- [ ] Test admin access: Access admin page as regular user (should deny)
- [ ] Verify `.env` file not accessible via browser

#### 7. Error Handling
- [ ] Test invalid page: `/index.php?page=invalid` (should redirect)
- [ ] Test database error handling (temporarily break .env)
- [ ] Verify error messages don't reveal sensitive info

---

## Troubleshooting

### Common Issues and Solutions

#### Issue: "Database connection failed"

**Causes**:
- Incorrect database credentials
- Database doesn't exist
- User doesn't have permissions
- Wrong host

**Solutions**:
1. Verify `.env` credentials match Hostinger control panel
2. Ensure database exists in phpMyAdmin
3. Check user has all privileges
4. Try `localhost` instead of `127.0.0.1`
5. Enable debug mode temporarily:
   ```ini
   APP_DEBUG=true
   ```
6. Check error in browser for specific PDO error
7. Disable debug mode after fixing:
   ```ini
   APP_DEBUG=false
   ```

#### Issue: "500 Internal Server Error"

**Causes**:
- File permissions incorrect
- .htaccess syntax error
- PHP version incompatibility
- Missing PHP extensions

**Solutions**:
1. Check file permissions (755 dirs, 644 files)
2. Review `.htaccess` syntax
3. Check error logs in Hostinger control panel
4. Verify PHP version >= 7.4
5. Enable error reporting temporarily:
   ```php
   // Add to top of index.php
   error_reporting(E_ALL);
   ini_set('display_errors', '1');
   ```

#### Issue: "CSS/JS not loading"

**Causes**:
- Incorrect paths
- .htaccess blocking requests
- Files not uploaded

**Solutions**:
1. Check browser DevTools Network tab
2. Verify files exist in `public/css/` and `public/js/`
3. Check `.htaccess` RewriteBase
4. Clear browser cache
5. Check file permissions (644)

#### Issue: "Session not persisting"

**Causes**:
- Session directory not writable
- Cookie settings incompatible
- HTTPS mismatch

**Solutions**:
1. Check session save path permissions
2. Verify cookie settings in config.php
3. Ensure APP_URL matches actual domain (HTTPS vs HTTP)
4. Check session settings in php.ini

#### Issue: "CSRF token invalid"

**Causes**:
- Session not started
- Token mismatch
- Session expired

**Solutions**:
1. Verify `initSession()` called before forms
2. Check session lifetime not too short
3. Ensure CSRF token included in form
4. Clear browser cookies and try again

#### Issue: "Access denied to .env file" (Good!)

This is expected behavior for security. The `.htaccess` prevents direct access to `.env` files.

---

## Security Checklist

Before going live, ensure all security measures are in place:

### Essential Security Steps

- [ ] **Change default admin PIN**
  - Login as Admin
  - Create new admin user with strong PIN
  - Delete or update default admin account

- [ ] **Enable HTTPS**
  - SSL certificate installed
  - HTTPS redirect enabled in `.htaccess`
  - APP_URL uses `https://`

- [ ] **Secure `.env` file**
  - Permissions set to 600
  - Not accessible via browser
  - Contains production credentials only

- [ ] **Disable debug mode**
  - `APP_DEBUG=false` in `.env`
  - Error display off
  - Error logging enabled

- [ ] **Verify file permissions**
  - Directories: 755
  - Files: 644
  - `.env`: 600
  - No world-writable files

- [ ] **Test security features**
  - CSRF protection working
  - Authentication required for protected pages
  - Admin access restricted
  - SQL injection prevention (prepared statements)
  - XSS prevention (output escaping)

- [ ] **Review security headers**
  - X-Content-Type-Options: nosniff
  - X-Frame-Options: SAMEORIGIN
  - X-XSS-Protection: 1; mode=block

- [ ] **Database security**
  - Strong database password
  - User has minimum required privileges
  - Sensitive files not in web root

### Recommended Additional Security

- [ ] Set up regular database backups
- [ ] Enable Hostinger's malware scanner
- [ ] Set up uptime monitoring
- [ ] Configure email notifications for errors
- [ ] Review logs regularly for suspicious activity
- [ ] Keep PHP version updated
- [ ] Monitor failed login attempts

---

## Git Auto-Deployment

Set up automatic deployment when pushing to GitHub.

### Prerequisites

- SSH access to Hostinger
- Git installed on server
- GitHub repository access

### Setup Git Auto-Deployment

#### 1. Create Deploy Script

```bash
# On your server
cd /path/to/domains/your-domain.com/
nano deploy.sh
```

Add this content:
```bash
#!/bin/bash

# Dicksord Fest 2026 - Newcastle Auto-Deploy Script

# Configuration
REPO_URL="https://github.com/ian-d-co/newcastle.git"
DEPLOY_DIR="/path/to/domains/your-domain.com"
WEB_ROOT="$DEPLOY_DIR/public_html"
APP_DIR="$DEPLOY_DIR/app"
BACKUP_DIR="$DEPLOY_DIR/backups"

# Create backup
echo "Creating backup..."
mkdir -p "$BACKUP_DIR"
tar -czf "$BACKUP_DIR/backup-$(date +%Y%m%d-%H%M%S).tar.gz" "$APP_DIR" "$WEB_ROOT"

# Pull latest changes
echo "Pulling latest changes..."
cd "$DEPLOY_DIR"

if [ ! -d ".git" ]; then
    echo "Initializing Git repository..."
    git init
    git remote add origin "$REPO_URL"
fi

git fetch origin main
git reset --hard origin/main

# Set permissions
echo "Setting permissions..."
find "$APP_DIR" -type d -exec chmod 755 {} \;
find "$APP_DIR" -type f -exec chmod 644 {} \;
find "$WEB_ROOT" -type d -exec chmod 755 {} \;
find "$WEB_ROOT" -type f -exec chmod 644 {} \;
chmod 600 "$APP_DIR/config/.env"

echo "Deployment complete!"
```

Make executable:
```bash
chmod +x deploy.sh
```

#### 2. Manual Deployment

Run deploy script manually:
```bash
./deploy.sh
```

#### 3. GitHub Webhook (Advanced)

If your Hostinger plan allows:

1. **Create webhook endpoint**:
   ```bash
   cd public_html
   nano webhook.php
   ```

2. **Add webhook handler**:
   ```php
   <?php
   // webhook.php
   $secret = 'your_webhook_secret_here';
   
   $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';
   $payload = file_get_contents('php://input');
   
   if (hash_equals('sha1=' . hash_hmac('sha1', $payload, $secret), $signature)) {
       shell_exec('/path/to/deploy.sh > /dev/null 2>&1 &');
       echo "Deployment triggered";
   } else {
       http_response_code(403);
       echo "Invalid signature";
   }
   ```

3. **Configure GitHub webhook**:
   - Go to GitHub repository settings
   - Add webhook: `https://your-domain.com/webhook.php`
   - Set secret to match your script
   - Select "Just the push event"
   - Save webhook

---

## Production Optimization

### Performance Tuning

1. **Enable OPcache** (if available):
   ```ini
   ; In php.ini or .user.ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=10000
   ```

2. **Database optimization**:
   ```sql
   -- Run periodically
   OPTIMIZE TABLE users, events, activities, meals, polls;
   ```

3. **Caching headers** (already in .htaccess):
   - Static assets cached for 1 year
   - CSS/JS cached for 1 month

### Monitoring

1. **Set up uptime monitoring**:
   - Use Hostinger's built-in monitoring
   - Or external service (UptimeRobot, Pingdom)

2. **Error logging**:
   ```php
   // Errors logged to error_log file
   // Location: check php.ini error_log setting
   ```

3. **Database monitoring**:
   ```sql
   -- Check table sizes
   SELECT table_name, 
          ROUND(data_length/1024/1024,2) as size_mb
   FROM information_schema.tables 
   WHERE table_schema = 'u983097270_newc'
   ORDER BY size_mb DESC;
   ```

---

## Backup Strategy

### Regular Backups

**Database**:
```bash
# Daily backup (add to cron)
mysqldump -u u983097270_newc -p u983097270_newc > backup-$(date +%Y%m%d).sql
```

**Files**:
```bash
# Weekly backup
tar -czf backup-files-$(date +%Y%m%d).tar.gz app/ public_html/
```

### Backup Schedule

- **Daily**: Database backup
- **Weekly**: Full file backup
- **Before updates**: Complete backup
- **Before major changes**: Snapshot backup

### Restore Procedure

1. **Restore database**:
   ```bash
   mysql -u u983097270_newc -p u983097270_newc < backup-20260218.sql
   ```

2. **Restore files**:
   ```bash
   tar -xzf backup-files-20260218.tar.gz
   ```

---

## Support

### Getting Help

1. **Check error logs first**:
   - Hostinger control panel → Error logs
   - Enable debug mode temporarily

2. **Review this guide**:
   - Follow troubleshooting steps
   - Check security checklist

3. **Hostinger support**:
   - Live chat available 24/7
   - Submit ticket for technical issues
   - Check knowledge base

### Useful Resources

- **Hostinger Help Center**: https://support.hostinger.com
- **PHP Documentation**: https://www.php.net/manual/
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **Git Documentation**: https://git-scm.com/doc

---

## Success!

Once all steps are complete:

✅ Application is live at `https://your-domain.com`  
✅ HTTPS enabled and enforced  
✅ Database connected and populated  
✅ Default admin accessible  
✅ Security measures in place  
✅ Backups configured  

### Next Steps

1. **Change default admin PIN immediately**
2. **Test all features thoroughly**
3. **Configure event details**
4. **Add activities, meals, polls**
5. **Share URL with attendees**

### Default Admin Login

- **URL**: `https://your-domain.com/index.php?page=login`
- **Discord Name**: `Admin`
- **PIN**: `123456`

**⚠️ CHANGE THIS PIN IMMEDIATELY!**

---

**Enjoy Dicksord Fest 2026 - Newcastle! 🎉**

For issues or questions, contact the development team.
