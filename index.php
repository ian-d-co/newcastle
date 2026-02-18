<?php
/**
 * Dicksord Fest 2026 - Newcastle Event Management System
 * Root Index - Method 2 Deployment Fallback
 * 
 * This file should only be reached if:
 * 1. Document root is set to public_html/ (Method 2)
 * 2. .htaccess rewrite rules are not working
 * 
 * RECOMMENDED: Set document root to public_html/public/ (Method 1)
 * This is more secure as it keeps app/ directory outside the web root.
 */

// Log that we're using Method 2
error_log('WARNING: Request handled by root index.php. Consider setting document root to public_html/public/ for better security.');

// Load bootstrap - it will work from any location
require_once __DIR__ . '/app/bootstrap.php';

// Forward to the public index
require_once PUBLIC_PATH . '/index.php';
