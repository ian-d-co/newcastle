<?php
/**
 * Dickscord Fest 2026 - Newcastle Event Management System
 * Database Helper Class
 * 
 * Static helper class for database operations using PDO
 * Provides convenient methods for common CRUD operations with
 * built-in SQL injection prevention via prepared statements
 */

class Database {
    /**
     * @var PDO|null Static PDO connection instance
     */
    private static $connection = null;
    
    /**
     * Get PDO database connection (singleton pattern)
     * 
     * @return PDO Database connection instance
     * @throws PDOException If connection fails
     */
    public static function getConnection() {
        if (self::$connection === null) {
            self::$connection = getDbConnection();
        }
        return self::$connection;
    }
    
    /**
     * Execute a query with optional parameters
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return PDOStatement Executed statement
     * @throws PDOException If query execution fails
     */
    public static function query($sql, $params = []) {
        try {
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                throw $e;
            } else {
                error_log('Database query error: ' . $e->getMessage());
                throw new Exception('Database query failed');
            }
        }
    }
    
    /**
     * Fetch a single row from database
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|false Single row as associative array or false if not found
     */
    public static function fetchOne($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Fetch all rows from database
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array Array of rows as associative arrays
     */
    public static function fetchAll($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Insert a row into database
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value pairs
     * @return int Last insert ID
     * @throws Exception If insert fails
     */
    public static function insert($table, $data) {
        try {
            // Build column and placeholder lists
            $columns = array_keys($data);
            $placeholders = array_map(function($col) {
                return ':' . $col;
            }, $columns);
            
            $sql = sprintf(
                "INSERT INTO %s (%s) VALUES (%s)",
                $table,
                implode(', ', $columns),
                implode(', ', $placeholders)
            );
            
            // Execute with named parameters
            $params = [];
            foreach ($data as $key => $value) {
                $params[':' . $key] = $value;
            }
            
            self::query($sql, $params);
            return self::lastInsertId();
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                throw $e;
            } else {
                error_log('Database insert error: ' . $e->getMessage());
                throw new Exception('Database insert failed');
            }
        }
    }
    
    /**
     * Update rows in database
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value pairs to update
     * @param string $where WHERE clause (e.g., "id = :id")
     * @param array $whereParams Parameters for WHERE clause
     * @return int Number of affected rows
     * @throws Exception If update fails
     */
    public static function update($table, $data, $where, $whereParams = []) {
        try {
            // Build SET clause
            $setParts = [];
            foreach (array_keys($data) as $column) {
                $setParts[] = "$column = :set_$column";
            }
            
            $sql = sprintf(
                "UPDATE %s SET %s WHERE %s",
                $table,
                implode(', ', $setParts),
                $where
            );
            
            // Merge data and where parameters
            $params = [];
            foreach ($data as $key => $value) {
                $params[':set_' . $key] = $value;
            }
            $params = array_merge($params, $whereParams);
            
            $stmt = self::query($sql, $params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                throw $e;
            } else {
                error_log('Database update error: ' . $e->getMessage());
                throw new Exception('Database update failed');
            }
        }
    }
    
    /**
     * Delete rows from database
     * 
     * @param string $table Table name
     * @param string $where WHERE clause (e.g., "id = :id")
     * @param array $params Parameters for WHERE clause
     * @return int Number of affected rows
     * @throws Exception If delete fails
     */
    public static function delete($table, $where, $params = []) {
        try {
            $sql = sprintf("DELETE FROM %s WHERE %s", $table, $where);
            $stmt = self::query($sql, $params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                throw $e;
            } else {
                error_log('Database delete error: ' . $e->getMessage());
                throw new Exception('Database delete failed');
            }
        }
    }
    
    /**
     * Get last insert ID
     * 
     * @return int Last insert ID
     */
    public static function lastInsertId() {
        return self::getConnection()->lastInsertId();
    }
    
    /**
     * Begin a database transaction
     * 
     * @return bool True on success
     */
    public static function beginTransaction() {
        return self::getConnection()->beginTransaction();
    }
    
    /**
     * Commit a database transaction
     * 
     * @return bool True on success
     */
    public static function commit() {
        return self::getConnection()->commit();
    }
    
    /**
     * Rollback a database transaction
     * 
     * @return bool True on success
     */
    public static function rollback() {
        return self::getConnection()->rollBack();
    }
}
