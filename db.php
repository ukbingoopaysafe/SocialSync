<?php
/**
 * Database Connection Handler
 * 
 * Provides a secure PDO connection to MySQL database
 */

require_once 'config.php';

/**
 * Get PDO database connection
 * 
 * @return PDO Database connection object
 * @throws PDOException on connection failure
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Log error in production, display in development
            if (ENVIRONMENT === 'development') {
                die("Database Connection Failed: " . $e->getMessage());
            } else {
                error_log("Database Connection Error: " . $e->getMessage());
                die("Database connection error. Please contact support.");
            }
        }
    }
    
    return $pdo;
}

/**
 * Execute a prepared statement safely
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters for prepared statement
 * @return PDOStatement
 */
function executeQuery($sql, $params = []) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch a single row
 * 
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return array|false
 */
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

/**
 * Fetch all rows
 * 
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return array
 */
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Get last inserted ID
 * 
 * @return string
 */
function lastInsertId() {
    $pdo = getDBConnection();
    return $pdo->lastInsertId();
}
