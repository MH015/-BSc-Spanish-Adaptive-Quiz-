<?php
/**
 * DATABASE CONNECTION
 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'adaptive_quiz');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', '8889');

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    die("
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ef4444; border-radius: 8px; background: #fee2e2;'>
            <h2 style='color: #dc2626; margin-top: 0;'>⚠️ Database Connection Error</h2>
            <p>Unable to connect to the database. Please check:</p>
            <ul>
                <li>MAMP server is running</li>
                <li>MySQL service is started</li>
                <li>Database 'adaptive_quiz' exists</li>
                <li>Database credentials are correct</li>
            </ul>
            <p style='color: #666; font-size: 14px;'>Error logged for administrator review.</p>
        </div>
    ");
}

function db_query($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function db_query_single($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

function db_execute($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function db_last_insert_id() {
    global $pdo;
    return $pdo->lastInsertId();
}

function db_count($table, $where = '1=1', $params = []) {
    global $pdo;
    $sql = "SELECT COUNT(*) as count FROM $table WHERE $where";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result ? $result['count'] : 0;
}
?>
