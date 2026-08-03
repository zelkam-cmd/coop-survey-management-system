<?php
/**
 * CampusVoice — Database Connection
 * Single PDO connection used across the entire application.
 * Uses prepared statements exclusively for SQL injection prevention.
 */

// Database credentials — adjust for your XAMPP/Laragon environment
define('DB_HOST', 'localhost');
define('DB_NAME', 'campusvoice');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO database connection (singleton pattern)
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error but don't expose details to users
            error_log("Database Connection Error: " . $e->getMessage());
            
            // Render error page directly if available
            if (file_exists(__DIR__ . '/../system-pages/error.php')) {
                require_once __DIR__ . '/../system-pages/error.php';
                exit;
            }
            
            die('Database connection failed. Please contact the administrator.');
        }
    }
    
    return $pdo;
}

// Shorthand alias
function db() {
    return getDBConnection();
}
