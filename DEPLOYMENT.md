# Dicksord Fest 2026 - Newcastle - Deployment Guide

## Overview

This application supports **two deployment methods** for Hostinger or other shared hosting:

1. **Method 1 (RECOMMENDED)**: Document root set to `public_html/public/`
   - ✅ Most secure (private files not accessible)
   - ✅ Standard Laravel-style structure
   - ✅ No file moving required

2. **Method 2**: Document root set to `public_html/`
   - ⚠️ Less secure but sometimes necessary on shared hosting
   - ⚠️ Requires additional .htaccess protection
   - ✅ Works when document root cannot be changed

## Method 1: Document Root in public/ (RECOMMENDED)

### Step 1: Upload Files

Upload all files to your hosting directory:
```
public_html/
├── app/
├── database/
├── logs/
├── public/          ← This will be your document root
├── .env.example
├── .gitignore
├── .htaccess       ← Root fallback (not used in Method 1)
└── index.php       ← Root fallback (not used in Method 1)
```

### Step 2: Configure Document Root

In Hostinger control panel:
1. Go to **Domains** or **Website Settings**
2. Find **Document Root** or **Website Root** setting
3. Change from `public_html/` to `public_html/public/`
4. Save changes

### Step 3: Create Environment File

Copy `.env.example` to `app/config/.env`:
```bash
# Location: public_html/app/config/.env

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
TIMEZONE=Europe/London

# Database (from Hostinger MySQL Databases)
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u983097270_newc
DB_USER=u983097270_newc
DB_PASSWORD=your_secure_password_here

# Session
SESSION_NAME=dicksord_fest_2026
SESSION_LIFETIME=7200

# Security
CSRF_TOKEN_NAME=csrf_token
```

**IMPORTANT**: Never place `.env` in the public/ directory!

### Step 4: Set Permissions

```bash
# Files
find public_html -type f -exec chmod 644 {} \;

# Directories
find public_html -type d -exec chmod 755 {} \;

# Extra security for .env
chmod 600 public_html/app/config/.env

# Logs directory should be writable
chmod 755 public_html/logs
```

### Step 5: Import Database

See [Database Setup](#database-setup) section below.

### Step 6: Test Installation

1. Visit your domain: `https://yourdomain.com`
2. You should see the homepage
3. Test login at: `https://yourdomain.com/index.php?page=login`

---

## Method 2: Document Root in public_html/

Use this method if you cannot change the document root in your hosting panel.

### Step 1: Upload Files

Upload all files to `public_html/`:
```
public_html/
├── app/
├── database/
├── logs/
├── public/
│   ├── api/
│   ├── css/
│   ├── js/
│   ├── .htaccess    ← Public directory routing
│   └── index.php    ← Main entry point
├── .env.example
├── .gitignore
├── .htaccess        ← ROOT .htaccess (routes to public/)
└── index.php        ← ROOT fallback entry point
```

### Step 2: Verify .htaccess Files

Ensure root `.htaccess` exists and routes to `public/`:

**File: `public_html/.htaccess`**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Route requests to public/ directory
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ public/index.php [QSA,L]
</IfModule>

# Block access to sensitive directories
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^app/ - [F,L]
    RewriteRule ^database/ - [F,L]
    RewriteRule ^logs/ - [F,L]
</IfModule>

# Prevent access to sensitive files
<FilesMatch "^(\.env|\.git|composer\.(json|lock))">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### Step 3: Create Environment File

Same as Method 1 - create `app/config/.env` from `.env.example`.

### Step 4: Set Permissions

Same as Method 1.

### Step 5: Import Database

See [Database Setup](#database-setup) section below.

### Step 6: Test Installation

1. Visit your domain: `https://yourdomain.com`
2. Requests will be routed through root `.htaccess` to `public/index.php`
3. Test login: `https://yourdomain.com/index.php?page=login`

**Note**: In Method 2, `.htaccess` routing handles most requests. The root `index.php` is a fallback if `.htaccess` doesn't work.

---

## Database Setup

### Step 1: Create MySQL Database

1. **Login to Hostinger Control Panel**
2. Go to **MySQL Databases** section
3. Click **Create New Database**
4. Note credentials:
   - Database Name: `u983097270_newc` (or your chosen name)
   - Database User: (create new user)
   - Database Password: (create strong password)
   - Host: Usually `localhost`

### Step 2: Import Schema

**Option A: phpMyAdmin**
1. Access phpMyAdmin from Hostinger panel
2. Select your database
3. Click "Import" tab
4. Choose `database/schema.sql`
5. Click "Go"

**Option B: Command Line (if SSH available)**
```bash
mysql -u your_user -p your_database < database/schema.sql
```

### Step 3: Verify Import

Run this query in phpMyAdmin:
```sql
SHOW TABLES;
```

You should see tables like: `users`, `events`, `activities`, `meals`, `polls`, etc.

---

## Security Hardening

### 1. Change Default Admin PIN

- Login with Discord Name: `Admin`, PIN: `123456`
- Create new admin user with secure PIN
- Delete or update default admin account

### 2. Enable HTTPS

1. Activate SSL in Hostinger panel (usually free Let's Encrypt)
2. Force HTTPS by uncommenting in `public/.htaccess`:
   ```apache
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

### 3. Verify .env Protection

Already configured in `.htaccess` files:
```apache
<FilesMatch "^(\.env|\.git)">
    Order allow,deny
    Deny from all
</FilesMatch>
```

**Test protection**: Try visiting `https://yourdomain.com/.env` - should get 403 Forbidden

### 4. Check Directory Blocking (Method 2 only)

If using Method 2, verify these URLs return 403 Forbidden:
- `https://yourdomain.com/app/`
- `https://yourdomain.com/database/`
- `https://yourdomain.com/logs/`

### 5. Disable Debug Mode in Production

In `app/config/.env`:
```bash
APP_ENV=production
APP_DEBUG=false  ← MUST be false in production
```

---

## Testing Installation

1. **Visit your domain**: `https://yourdomain.com`
2. **Test login page**: Navigate to `/index.php?page=login`
3. **Login as admin**: Discord Name: `Admin`, PIN: `123456`
4. **Register test attendance**: Click "I am attending!"
5. **Test all sections**:
   - Activities
   - Meals
   - Carshare
   - Hosting
   - Polls
   - Hotels
   - Dashboard

---

## Troubleshooting

### "Service Unavailable" Error

**Possible causes**:
1. `.env` file not found or misconfigured
2. Database connection failed
3. BASE_PATH not resolved correctly
4. Required files missing

**Solutions**:
1. Verify `.env` exists in `app/config/.env`
2. Check database credentials in `.env`
3. Enable debug mode temporarily: `APP_DEBUG=true`
4. Check error logs in `logs/error.log` or Hostinger panel

### Bootstrap/Configuration Errors

**Error**: "Configuration file not found"
- Verify `app/bootstrap.php` exists
- Check file permissions (should be 644)
- Ensure document root is correctly configured

**Error**: "Invalid BASE_PATH"
- The app expects `app/` and `public/` directories
- Verify complete file upload
- Check directory structure is intact

### Database Connection Issues

**Error**: "Database connection failed"

Check these in order:
1. Database exists: Login to phpMyAdmin, verify database name
2. User has permissions: Check user can access database
3. Credentials match: Compare `.env` with database settings
4. Host is correct: Usually `localhost`, not `127.0.0.1`
5. Port is correct: Default is `3306`

### .htaccess Not Working

**Symptoms**:
- 404 errors on all pages
- CSS/JS not loading
- Routing not working

**Solutions**:
1. Verify `mod_rewrite` is enabled (ask hosting support)
2. Check `.htaccess` files exist and are readable
3. Try `AllowOverride All` if you have Apache config access

### Path Resolution Issues

**Method 1 users**:
- Confirm document root is set to `public_html/public/`
- Check that `BASE_PATH` resolves correctly

**Method 2 users**:
- Verify root `.htaccess` is routing to `public/`
- Check that both `index.php` and `.htaccess` exist in root

---

## Initial Configuration

1. **Update event information** (via admin panel when ready)
2. **Add activities and meals**
3. **Create polls**
4. **Add hotels**
5. **Test booking flows**

---

## PHP Requirements

Minimum Requirements:
- PHP 7.4 or higher
- MySQL 5.7 or MariaDB 10.2
- PDO extension
- JSON extension
- Session support
- mbstring extension

Check with:
```bash
php -v
php -m | grep -i pdo
php -m | grep -i json
```

### Database Issues

**Import fails**:
- Check file encoding (should be UTF-8)
- Verify MySQL version compatibility
- Import in smaller chunks if needed
- Check user permissions (CREATE, ALTER, INSERT)

**Character encoding issues**:
- Ensure database charset is `utf8mb4`
- Verify collation is `utf8mb4_unicode_ci`
- Check connection charset in config.php

## Maintenance

### Regular Tasks

1. **Backup database regularly**:
   ```bash
   mysqldump -u user -p database > backup-$(date +%Y%m%d).sql
   ```

2. **Monitor logs**:
   - Check error_log in Hostinger
   - Review application errors
   - Monitor suspicious activity

3. **Update content**:
   - Keep event information current
   - Close expired polls
   - Update activities/meals as needed

4. **Security updates**:
   - Change admin PINs regularly
   - Review user accounts
   - Monitor payment status
   - Check for unauthorized access

### Performance Optimization

1. **Enable caching** in `.htaccess`:
   Already configured for static assets

2. **Optimize database**:
   ```sql
   OPTIMIZE TABLE activities, meals, polls, users;
   ```

3. **Monitor database size**:
   ```sql
   SELECT table_name, 
          ROUND(data_length/1024/1024,2) as size_mb
   FROM information_schema.tables 
   WHERE table_schema = 'your_database'
   ORDER BY size_mb DESC;
   ```

### Backup Strategy

**What to backup**:
1. Database (`.sql` export)
2. `.env` file (contains credentials)
3. Uploaded images (if added later)
4. Custom modifications

**Backup schedule**:
- Daily: Database
- Weekly: Full files
- Before updates: Complete backup

## Support & Resources

### Getting Help

1. Check error logs first
2. Review this deployment guide
3. Verify all prerequisites met
4. Test with simplified setup
5. Contact Hostinger support for server issues

### Useful Commands

**Check PHP configuration**:
```bash
php -i | grep -i session
php -i | grep -i pdo
```

**Test database connection**:
```bash
mysql -u user -p -e "SELECT 1"
```

**View recent logs**:
```bash
tail -f /path/to/error.log
```

**File permissions**:
```bash
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 600 .env
```

## Go Live Checklist

- [ ] Database imported successfully
- [ ] `.env` configured with production values
- [ ] Default admin PIN changed
- [ ] HTTPS enabled
- [ ] All file permissions correct
- [ ] Event information updated
- [ ] Test registration works
- [ ] Test booking flows work
- [ ] Test poll voting works
- [ ] Test admin dashboard accessible
- [ ] Error reporting configured appropriately
- [ ] Backup system in place
- [ ] Contact email/support set up
- [ ] Terms and privacy (if required) added
- [ ] Announcement sent to attendees

## Success!

Once all steps are complete and tests pass, your Dicksord Fest 2026 Newcastle event management application is live!

Share the URL with attendees and they can start registering their attendance, booking activities, and participating in the event planning.

**Default Admin Login**:
- URL: `https://yourdomain.com/index.php?page=login`
- Discord Name: `Admin`
- PIN: `123456` (CHANGE IMMEDIATELY!)

Enjoy Dicksord Fest 2026! 🎉
