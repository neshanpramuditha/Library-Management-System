<?php

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'library_system');
define('DB_USER', 'root');
define('DB_PASS', ''); // Empty for XAMPP default
define('DB_CHARSET', 'utf8mb4');

// Database Connection Class
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    private $pdo = null;
    
    // Get PDO Database Connection
    public function connect() {
        if ($this->pdo === null) {
            try {
                // First, try to connect to the database
                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
                
            } catch (PDOException $e) {
                // If database doesn't exist, try to create it
                if ($e->getCode() == 1049) {
                    try {
                        // Connect without database name
                        $pdo_temp = new PDO(
                            "mysql:host={$this->host};charset={$this->charset}",
                            $this->username,
                            $this->password,
                            $options
                        );
                        
                        // Create database
                        $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS `{$this->db_name}` 
                                        CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                        
                        // Now connect to the new database
                        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
                        $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
                        
                        // Display setup message
                        echo '<div style="background:#fef3c7;border:2px solid #f59e0b;padding:15px;margin:20px;border-radius:8px;">';
                        echo '<strong>⚠️ Database Created!</strong><br>';
                        echo 'The database "library_system" has been created automatically.<br>';
                        echo '<strong>IMPORTANT:</strong> You must import the SQL file now!<br>';
                        echo '<ol>';
                        echo '<li>Go to: <a href="http://localhost/phpmyadmin" target="_blank">phpMyAdmin</a></li>';
                        echo '<li>Click on "library_system" database</li>';
                        echo '<li>Click "Import" tab</li>';
                        echo '<li>Choose file: library_system.sql</li>';
                        echo '<li>Click "Go" to import</li>';
                        echo '<li>Then refresh this page</li>';
                        echo '</ol>';
                        echo '</div>';
                        
                    } catch (PDOException $e2) {
                        die("<div style='background:#fee;border:2px solid #f00;padding:15px;margin:20px;border-radius:8px;'>
                            <strong>❌ Connection Error:</strong><br>
                            Could not create database: " . $e2->getMessage() . "<br><br>
                            <strong>Solution:</strong><br>
                            1. Make sure MySQL is running in XAMPP<br>
                            2. Check your database credentials in config/database.php<br>
                            3. Try manually creating the database in phpMyAdmin
                            </div>");
                    }
                } else {
                    die("<div style='background:#fee;border:2px solid #f00;padding:15px;margin:20px;border-radius:8px;'>
                        <strong>❌ Database Connection Failed:</strong><br>" . $e->getMessage() . "<br><br>
                        <strong>Common Solutions:</strong><br>
                        1. Check if MySQL is running in XAMPP Control Panel<br>
                        2. Verify database name: <code>library_system</code><br>
                        3. Check username: <code>root</code> and password: <code>(empty)</code><br>
                        4. Make sure you've imported the SQL file
                        </div>");
                }
            }
        }
        
        return $this->pdo;
    }
}

// Get Database Connection
function getDB() {
    $database = new Database();
    return $database->connect();
}
?>