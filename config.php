<?php
// config.php
$host = 'localhost';
$dbname = 'contentmanagementsystem';
$username = 'root'; // Update based on your MySQL credentials
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    //die("Database connection failed: " . $e->getMessage());
    echo "Connection failed: " . $e->getMessage();
    exit;
}
?>