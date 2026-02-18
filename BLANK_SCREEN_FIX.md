# Dicksord Fest 2026 - Blank Screen Troubleshooting Guide

## Overview

This document addresses the specific issue of blank screens when visiting https://dbnewcastle.nwboundbear.xyz/ and provides comprehensive fixes that have been implemented.

## Recent Fixes Applied

The following improvements have been made to prevent blank screens and provide better error feedback:

### 1. Enhanced Error Logging (app/config/config.php)

**Changes:**
- Added warning logs when .env file is missing or unreadable
- Added custom error handler to prevent blank pages
- Improved database connection logging with detailed error messages
- Added connection timeout (5 seconds) to prevent hanging
- Enhanced error messages for both debug and production modes

**Benefits:**
- Errors are now logged to help diagnose issues
- In debug mode, clear error messages are displayed
- In production mode, user-friendly messages are shown instead of blank pages

### 2. Improved Database Connection Error Handling

**Changes:**
- Added detailed logging for database connection attempts
- Test connection with simple query after establishing
- Provide helpful error messages with troubleshooting steps
- Show different messages for debug vs production mode

**Sample Error Output (Debug Mode):**
```
Database Connection Error

Database connection failed: SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: NO)

Please check:
- .env file exists in app/config/ or root directory
- Database credentials are correct
- Database server is running
- Database u983097270_newc exists
```

### 3. Homepage Route Protection (public/index.php)

**Changes:**
- Wrapped homepage loading in try-catch block
- Added fallback event data if database has no active event
- Enhanced error messages for homepage failures
- Added logging for request flow

**Benefits:**
- Homepage can display even if database has issues (with default data)
- Clear error messages instead of blank screen
- Request tracking for debugging

### 4. Startup Error Handling

**Changes:**
- Added try-catch around critical file loading
- Added logging for each request
- Early detection of configuration issues

**Benefits:**
- Configuration errors are caught before blank pages occur
- Easier to diagnose startup issues

## Common Blank Screen Causes & Solutions

### Cause 1: Missing .env File

**Symptoms:**
- Blank screen when visiting site
- Error log shows: "Warning: .env file not found"
- Database connection fails

**Solution:**
1. Create .env file in `app/config/.env`:
   ```bash
   cp app/config/.env.example app/config/.env
   ```

2. Edit with your credentials:
   ```ini
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=u983097270_newc
   DB_USER=u983097270_newc
   DB_PASSWORD=your_password_here
   
   APP_ENV=production
   APP_DEBUG=false  # Set to true only for debugging
   APP_URL=https://dbnewcastle.nwboundbear.xyz
   ```

3. Set proper permissions:
   ```bash
   chmod 600 app/config/.env
   ```

### Cause 2: Database Connection Failure

**Symptoms:**
- Error: "Database connection failed"
- Cannot access any pages
- May show SQLSTATE errors in logs

**Solution:**
1. Verify database credentials in .env:
   ```bash
   cat app/config/.env | grep DB_
   ```

2. Test database connection:
   ```bash
   mysql -h localhost -u u983097270_newc -p u983097270_newc -e "SELECT 1;"
   ```

3. Common fixes:
   - Use `localhost` for DB_HOST (not 127.0.0.1)
   - Verify database name includes user prefix
   - Check user has permissions
   - Ensure database exists

4. Verify in Hostinger control panel:
   - Go to Databases → MySQL Databases
   - Confirm database name
   - Confirm user has privileges
   - Reset password if needed

### Cause 3: No Active Event in Database

**Symptoms:**
- Homepage loads but shows error
- Error: "No active event found"
- Other pages may work

**Solution:**
With our fixes, the site will now use default event data automatically. To add a real event:

1. Access phpMyAdmin
2. Select your database
3. Run this SQL:
   ```sql
   INSERT INTO events (title, description, start_date, end_date, location, content, is_active)
   VALUES (
       'Dicksord Fest 2026 - Newcastle',
       'Join us for an epic gaming event in Newcastle!',
       '2026-11-20',
       '2026-11-22',
       'Newcastle',
       '<p>Welcome to Dicksord Fest 2026!</p><p>This is a multi-day gaming event bringing together the Dicksord community.</p>',
       1
   );
   ```

### Cause 4: PHP Configuration Issues

**Symptoms:**
- 500 Internal Server Error
- Blank screen
- Server logs show PHP errors

**Solution:**
1. Check PHP version (requires 7.4+):
   ```php
   <?php phpinfo(); ?>
   ```

2. Verify required extensions:
   - PDO
   - pdo_mysql
   - json
   - mbstring
   - session

3. In Hostinger control panel:
   - Go to Advanced → PHP Configuration
   - Ensure version is 7.4 or higher
   - Enable required extensions

### Cause 5: File Permissions Issues

**Symptoms:**
- 403 Forbidden errors
- Cannot write sessions
- Blank pages with no errors

**Solution:**
1. Set correct permissions:
   ```bash
   # Directories
   find . -type d -exec chmod 755 {} \;
   
   # Files
   find . -type f -exec chmod 644 {} \;
   
   # .env file (more restrictive)
   chmod 600 app/config/.env
   ```

2. Verify web server can read files:
   ```bash
   ls -la app/config/
   ls -la public/
   ```

### Cause 6: .htaccess Issues

**Symptoms:**
- 500 Internal Server Error
- CSS/JS not loading
- Routing not working

**Solution:**
1. Verify mod_rewrite is enabled (Hostinger: usually enabled)

2. Check .htaccess syntax:
   ```bash
   apachectl configtest
   # Or check error logs
   ```

3. Verify .htaccess in public/ directory:
   ```apache
   RewriteEngine On
   RewriteBase /
   
   # Don't rewrite existing files/directories
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   
   # Route all requests to index.php
   RewriteRule ^ index.php [QSA,L]
   ```

4. If in subdirectory, update RewriteBase:
   ```apache
   RewriteBase /subdirectory/
   ```

## Debugging Steps

### Step 1: Enable Debug Mode

**Temporary debugging** (disable after fixing):

1. Edit .env:
   ```ini
   APP_DEBUG=true
   ```

2. Visit site - you should now see detailed error messages

3. **IMPORTANT:** Disable after fixing:
   ```ini
   APP_DEBUG=false
   ```

### Step 2: Check Error Logs

**Location of logs:**
- Hostinger: Control Panel → Error Logs
- Server: `/path/to/logs/error.log`
- PHP errors: Check php error_log setting

**View recent errors:**
```bash
tail -f /path/to/logs/error.log
```

**Look for:**
- Database connection errors
- File not found errors
- PHP syntax errors
- Permission denied errors

### Step 3: Test Database Connection

**Create test file** (delete after testing):

`public/test-db.php`:
```php
<?php
require_once __DIR__ . '/../app/config/config.php';

try {
    $db = getDbConnection();
    echo "✓ Database connection successful!\n";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM events");
    $result = $stmt->fetch();
    echo "✓ Database query successful! Found " . $result['count'] . " events.\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
```

Access: `https://yourdomain.com/test-db.php`

**Delete after testing:**
```bash
rm public/test-db.php
```

### Step 4: Test PHP Configuration

**Create test file** (delete after testing):

`public/test-config.php`:
```php
<?php
echo "PHP Version: " . PHP_VERSION . "\n\n";

$required = ['PDO', 'pdo_mysql', 'json', 'mbstring', 'session'];
foreach ($required as $ext) {
    $loaded = extension_loaded($ext);
    echo ($loaded ? "✓" : "✗") . " $ext: " . ($loaded ? "enabled" : "MISSING") . "\n";
}
```

Access: `https://yourdomain.com/test-config.php`

**Delete after testing:**
```bash
rm public/test-config.php
```

## Verification Checklist

After applying fixes, verify:

- [ ] .env file exists and is readable
- [ ] Database credentials are correct
- [ ] Database connection succeeds
- [ ] Homepage loads (with default or real event data)
- [ ] CSS and JS files load correctly
- [ ] No blank screens - errors show helpful messages
- [ ] Debug mode is OFF in production
- [ ] Error logs are being written
- [ ] File permissions are correct (755/644)
- [ ] .htaccess is working

## Testing the Site

### Quick Test Script

```bash
#!/bin/bash
echo "Testing Dicksord Fest 2026 site..."

# Test 1: Homepage loads
echo -n "1. Homepage loading... "
STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://dbnewcastle.nwboundbear.xyz/)
if [ "$STATUS" = "200" ]; then
    echo "✓ OK (HTTP $STATUS)"
else
    echo "✗ FAIL (HTTP $STATUS)"
fi

# Test 2: CSS loads
echo -n "2. CSS loading... "
STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://dbnewcastle.nwboundbear.xyz/css/styles.css)
if [ "$STATUS" = "200" ]; then
    echo "✓ OK"
else
    echo "✗ FAIL"
fi

# Test 3: JS loads
echo -n "3. JavaScript loading... "
STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://dbnewcastle.nwboundbear.xyz/js/app.js)
if [ "$STATUS" = "200" ]; then
    echo "✓ OK"
else
    echo "✗ FAIL"
fi

echo "Testing complete!"
```

## Support Resources

### Log Files to Check
- `/logs/error.log` - Application errors
- Hostinger Control Panel → Error Logs - Server errors  
- Browser DevTools → Console - JavaScript errors
- Browser DevTools → Network - Failed requests

### Key Configuration Files
- `app/config/.env` - Environment configuration
- `app/config/config.php` - Application configuration
- `public/.htaccess` - Routing rules
- `public/index.php` - Entry point

### Getting Help

1. **Check logs first** - most issues are logged
2. **Enable debug mode temporarily** - see detailed errors
3. **Verify .env file** - most common issue
4. **Test database connection** - second most common issue
5. **Check file permissions** - common on new installations

## Prevention

To avoid blank screens in the future:

1. **Never delete .env file** - it contains critical configuration
2. **Test changes locally first** - before deploying to production
3. **Keep backups** - database and files
4. **Monitor error logs** - catch issues early
5. **Keep debug mode OFF** - in production
6. **Document changes** - know what was changed when issues occur

## Summary of Improvements

The recent fixes ensure that:

✅ Missing .env file is logged (not silent failure)  
✅ Database connection errors show helpful messages  
✅ Homepage works even without database events (fallback data)  
✅ All errors are logged for debugging  
✅ Production mode shows user-friendly messages  
✅ Debug mode shows detailed technical information  
✅ No more blank screens - always shows something  

---

**Last Updated:** February 18, 2026  
**Status:** Fixes Implemented and Tested
