<?php
require 'config.php';
session_start();

header('Content-Type: application/json');

// Base SQL query logic
$sql = "SELECT contents.*, users.username AS creator_name FROM contents JOIN users ON contents.creator_id = users.id";
$params = [];

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// Fetch all data
$contents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output as JSON
echo json_encode($contents, JSON_PRETTY_PRINT);