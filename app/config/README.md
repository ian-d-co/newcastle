# Configuration Directory

This directory contains the application configuration files.

## Files

### config.php
Main configuration file that:
- Loads environment variables from `.env` file
- Defines application constants
- Provides database connection helper
- Sets up session configuration

### .env (NOT in git)
Environment-specific configuration file containing sensitive credentials.

**Location**: `app/config/.env`

This file should be created from `.env.example` and should **never** be committed to version control.

#### Creating .env file:
```bash
cp app/config/.env.example app/config/.env
# Then edit with your actual credentials
```

#### Required variables:
- `APP_ENV` - Environment (development/production)
- `APP_DEBUG` - Debug mode (true/false)
- `APP_URL` - Your application URL
- `TIMEZONE` - PHP timezone
- `DB_HOST` - Database host (usually localhost)
- `DB_PORT` - Database port (usually 3306)
- `DB_NAME` - Database name
- `DB_USER` - Database username
- `DB_PASSWORD` - Database password
- `SESSION_NAME` - Session cookie name
- `SESSION_LIFETIME` - Session lifetime in seconds
- `CSRF_TOKEN_NAME` - CSRF token field name

### .env.example
Template file showing all available configuration options.

**Important**: Update this file when adding new configuration options, but never include actual credentials.

### test-connection.php
Diagnostic script for testing database connectivity.

Run it to verify your .env configuration:
```bash
php app/config/test-connection.php
```

This file can be deleted after successful deployment.

## Security Notes

1. **Never commit .env file** - It contains sensitive credentials
2. **Set proper permissions**: `chmod 600 app/config/.env` 
3. **Keep .env outside web root** - This directory is already outside public/
4. **Use strong passwords** - Especially for production databases
5. **Enable SSL in production** - Set APP_URL to https://

## How Configuration Loading Works

1. `config.php` checks for `.env` file in this directory first
2. If not found, it falls back to looking in the project root
3. Each line in `.env` is parsed as `KEY=VALUE`
4. Environment variables are set via `putenv()`, `$_ENV`, and `$_SERVER`
5. Configuration constants are then defined using these values

## Troubleshooting

### Database connection failed
1. Verify credentials in `.env` match your database settings
2. Check database exists and user has proper privileges
3. Ensure MySQL server is running
4. Verify host and port are correct
5. Run `php app/config/test-connection.php` for detailed diagnostics

### Configuration not loading
1. Verify `.env` file exists in `app/config/` directory
2. Check file permissions are readable (644 or 600)
3. Ensure no syntax errors in `.env` file
4. Check for proper `KEY=VALUE` format (no spaces around =)
