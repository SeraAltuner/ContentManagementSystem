<?php
session_start();
require 'config.php';

// Check if the user is logged in and is a content creator
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'content_creator') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Access denied. You must be logged in as a content creator.']);
    exit;
}

// Get the creator's ID from the session
$creator_id = $_SESSION['user_id'];

// Prepare and execute the query to fetch all content for this creator
$stmt = $pdo->prepare("SELECT id, title, body, image_path, created_at, is_approved FROM contents WHERE creator_id = :creator_id");
$stmt->execute(['creator_id' => $creator_id]);

// Fetch all the content
$contents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set the content type to JSON
header('Content-Type: application/json');

// Output the content as JSON
echo json_encode($contents, JSON_PRETTY_PRINT);