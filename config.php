<?php
$host = 'localhost'; 
// $db ='blog';
// $user ='root';
// $pass = '';
$db = 'u721127026_blog'; 
$user = 'u721127026_root'; 
$pass = 'Tplp004#32'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>