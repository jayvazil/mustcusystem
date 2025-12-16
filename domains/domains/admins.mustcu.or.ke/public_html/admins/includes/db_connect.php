<?php

$host = 'localhost';
$db   = 'uvwehfds_mustcu'; // replace with your DB name
$user = 'uvwehfds_mustcu';
$pass = '7ZwV6yxXKGrD2LPn5eSH'; // default in XAMPP
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // return associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // use native prepares
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options); // THIS must define $pdo
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
