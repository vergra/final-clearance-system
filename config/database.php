<?php
/**
 * Database connection for Gradline Clearance System
 * Configure for XAMPP: default user 'root', no password
 */
define('DB_HOST', 'localhost');
/** Base URL path (e.g. /student_clearance or '' if app is in document root) */
define('WEB_BASE', '/student_clearance');
define('DB_NAME', 'student_clearance');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    return $pdo;
}
