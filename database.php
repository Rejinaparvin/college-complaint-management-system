<?php

$host = "db01.dbhost.dev";      
$dbname = "db_454yr73gy";      
$user = "user_454yr73gy";           // nimbus username - .env file la irukkum
$pass = "p454yr73gy";               // nimbus password - .env la irukkum
// =========================================

try {
    // MySQL connect
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn = $pdo;

    // Table auto create
    $pdo->exec("CREATE TABLE IF NOT EXISTS ccms_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        department VARCHAR(50),
        role VARCHAR(20) DEFAULT 'student',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

} catch (PDOException $e) {
    // DB illa na create pannu
    try {
        $tmp = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
        $tmp->exec("CREATE DATABASE IF NOT EXISTS $dbname");
        $tmp = null;
        // Rettry
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn = $pdo;
        $pdo->exec("CREATE TABLE IF NOT EXISTS ccms_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            department VARCHAR(50),
            role VARCHAR(20) DEFAULT 'student',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    } catch (PDOException $e2) {
        // MySQL mudiyalana SQLite ku pogum - error varathu
        $pdo = new PDO("sqlite:" . __DIR__ . "/../ccms.db");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn = $pdo;
        $pdo->exec("CREATE TABLE IF NOT EXISTS ccms_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            email TEXT UNIQUE,
            password TEXT,
            department TEXT,
            role TEXT DEFAULT 'student',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }
}
?>
