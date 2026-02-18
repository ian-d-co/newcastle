# Dicksord Fest 2026 - Newcastle - Deployment Guide

## Quick Start for Hostinger Deployment

### Step 1: Prepare Your Hostinger Environment

1. **Login to Hostinger Control Panel**
   - Navigate to your hosting dashboard
   - Access File Manager or use FTP/SFTP

2. **Create MySQL Database**
   - Go to "MySQL Databases" section
   - Click "Create New Database"
   - Note your credentials:
     - Database Name: `u983097270_newc` (or your chosen name)
     - Database User: (create new user)
     - Database Password: (create strong password)
     - Host: Usually `localhost`

### Step 2: Upload Files

**Option A: File Manager**
1. Upload all files to your hosting directory (usually `public_html/`)
2. Ensure the `public/` folder contents are in your web root

**Option B: FTP/SFTP**
1. Connect via FTP client (FileZilla, etc.)
2. Upload entire project structure
3. Move `public/` folder contents to web root OR configure subdomain

### Step 3: Configure Environment

1. **Create `.env` file** from `.env.example` in `app/config/` directory:
   ```bash
   # Location: app/config/.env
   
   # Application
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://dbnewcastle.nwboundbear.xyz
   TIMEZONE=Europe/London
   
   # Database (from Hostinger MySQL Databases)
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=u983097270_newc
   DB_USER=u983097270_newc
   DB_PASSWORD=your_secure_password_here
   
   # Session
   SESSION_NAME=dicksord_fest_2026
   SESSION_LIFETIME=3600
   
   # Security
   CSRF_TOKEN_NAME=csrf_token
   ```

2. **Set file permissions**:
   - Files: 644
   - Directories: 755
   - `app/config/.env`: 600 (more restrictive for security)

### Step 4: Import Database

**Option A: phpMyAdmin**
1. Access phpMyAdmin from Hostinger panel
2. Select your database
3. Click "Import" tab
4. Choose `database/schema.sql`
5. Click "Go"

**Option B: Command Line (if SSH access available)**
```bash
mysql -u your_user -p your_database < database/schema.sql
```

### Step 5: Configure Web Server

**If using subdirectory** (e.g., yourdomain.com/dicksord):
1. Edit `public/.htaccess`
2. Update line:
   ```apache
   RewriteBase /dicksord/
   ```

**If using subdomain** (e.g., dicksord.yourdomain.com):
1. Create subdomain in Hostinger panel
2. Point document root to `public/` folder
3. No .htaccess changes needed

### Step 6: Security Hardening

1. **Change default admin PIN**:
   - Login with Discord Name: `Admin`, PIN: `123456`
   - Create new admin user
   - Delete or update default admin

2. **Enable HTTPS**:
   - Activate SSL in Hostinger panel
   - Uncomment HTTPS redirect in `public/.htaccess`:
     ```apache
     RewriteCond %{HTTPS} off
     RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
     ```

3. **Restrict sensitive files**:
   Already configured in `.htaccess`:
   ```apache
   <FilesMatch "\.(env|sql|md)$">
       Order allow,deny
       Deny from all
   </FilesMatch>
   ```

### Step 7: Test Installation

1. **Visit your domain**: `https://yourdomain.com`
2. **Test login page**: Should see login form
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

### Step 8: Initial Configuration

1. **Update event information** (via admin panel when ready)
2. **Add activities and meals**
3. **Create polls**
4. **Add hotels**
5. **Test booking flows**

## Troubleshooting

### Common Issues

**Issue: Database connection failed**
- Check `.env` credentials
- Verify database exists
- Check database user permissions
- Ensure host is `localhost` (not 127.0.0.1 on some hosts)

**Issue: 500 Internal Server Error**
- Check file permissions (755 for dirs, 644 for files)
- Review error logs in Hostinger panel
- Enable `APP_DEBUG=true` temporarily in `.env`
- Check PHP version (requires 7.4+)

**Issue: CSS/JS not loading**
- Verify path in browser DevTools
- Check .htaccess rewrite rules
- Ensure `public/` is web root
- Clear browser cache

**Issue: Forms not submitting**
- Check CSRF token generation
- Verify JavaScript is loading
- Check browser console for errors
- Test with different browser

**Issue: Sessions not persisting**
- Check session configuration in `php.ini`
- Verify session save path is writable
- Check cookie settings (HTTPOnly, SameSite)

### PHP Requirements

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
