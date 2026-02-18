<?php
/**
 * Database Connection Test Script
 * 
 * This script tests the database connection configuration.
 * Use it to verify that the .env file is being loaded correctly
 * and that the database connection is working.
 * 
 * Can be deleted after testing.
 */

// Load configuration
require_once __DIR__ . '/config.php';

echo "=== Database Connection Test ===\n\n";

// Display loaded configuration (hide password for security)
echo "Configuration loaded:\n";
echo "- DB_HOST: " . DB_HOST . "\n";
echo "- DB_PORT: " . DB_PORT . "\n";
echo "- DB_NAME: " . DB_NAME . "\n";
echo "- DB_USER: " . DB_USER . "\n";
echo "- DB_PASSWORD: " . (DB_PASSWORD ? str_repeat('*', strlen(DB_PASSWORD)) : '(empty)') . "\n";
echo "- DB_CHARSET: " . DB_CHARSET . "\n\n";

// Test database connection
echo "Testing database connection...\n";
try {
    $pdo = getDbConnection();
    echo "✓ Database connection successful!\n\n";
    
    // Test a simple query
    echo "Testing query execution...\n";
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && $result['test'] == 1) {
        echo "✓ Query execution successful!\n\n";
    } else {
        echo "✗ Query execution failed: unexpected result\n\n";
    }
    
    // Check if tables exist
    echo "Checking database tables...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "✓ Found " . count($tables) . " tables in database:\n";
        foreach ($tables as $table) {
            echo "  - $table\n";
        }
    } else {
        echo "⚠ No tables found in database (database might be empty)\n";
    }
    
    echo "\n=== All tests passed! ===\n";
    
} catch (PDOException $e) {
    echo "✗ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Please check:\n";
    echo "1. Database credentials in app/config/.env are correct\n";
    echo "2. MySQL server is running\n";
    echo "3. Database '" . DB_NAME . "' exists\n";
    echo "4. User '" . DB_USER . "' has access to the database\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Unexpected error!\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
