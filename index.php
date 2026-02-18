<?php
/**
 * Dicksord Fest 2026 - Newcastle
 * Root Index File
 * 
 * This file serves as the entry point when the web server's document root
 * is set to the repository root instead of the public/ directory.
 * It simply includes the main application entry point.
 */

// Change to public directory and include the main index
chdir(__DIR__ . '/public');
require __DIR__ . '/public/index.php';
