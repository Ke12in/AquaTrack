<?php
$serverName = "localhost";
$database = "aquatrack";
$username = "Khristos";  // default XAMPP MySQL username
$password = "";      // default XAMPP MySQL password is empty

try {
    $pdo = new PDO("mysql:host=$serverName;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>