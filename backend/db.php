<?php
/**
 * Database connection with singleton pattern
 * Ensures single database connection throughout the application
 */

require_once __DIR__ . '/config.php';

class Database
{
    private static $instance = null;
    private $conn = null;

    private function __construct()
    {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->conn->connect_error) {
            error_log("Database connection failed: " . $this->conn->connect_error);
            die("Database connection error. Please try again later.");
        }
        
        $this->conn->set_charset("utf8mb4");
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function close()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Global connection for backward compatibility
$conn = Database::getInstance()->getConnection();
?>
