<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "CREATE DATABASE IF NOT EXISTS path_of_excellence CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($sql);
    
    echo "Database 'path_of_excellence' created successfully!\n";
    
    // Test connection to the new database
    $test_pdo = new PDO('mysql:host=127.0.0.1;dbname=path_of_excellence', 'root', '');
    echo "Successfully connected to path_of_excellence database!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}