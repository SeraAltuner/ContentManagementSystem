<?php
session_start();
require 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'content_creator') {
    header("Location: login.php");
    exit;
}

// Handle content addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_content'])) {
    $title = $_POST['title'];
    $body = $_POST['body'];
    $creator_id = $_SESSION['user_id'];
    $image_path = null;

    // Validate and upload image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $file_tmp_name = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        $file_size = $_FILES['image']['size'];
        $file_type = $_FILES['image']['type'];

        // Check for file type
        if (!in_array($file_type, $allowed_types)) {
            die("Error: Invalid file type. Only JPEG, PNG, and GIF files are allowed.");
        }

        // Check for file size
        if ($file_size > $max_size) {
            die("Error: File size exceeds the 5MB limit.");
        }

        // Target directory
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/ContentManagementSystem/uploads/";
        $image_path = $target_dir . $file_name;

        // Move the uploaded file
        if (move_uploaded_file($file_tmp_name, $image_path)) {
            // File uploaded successfully
        } else {
            die("Error: Failed to move the uploaded file.");
        }
    } else {
        $image_error = $_FILES['image']['error'] ?? null;
        if ($image_error !== UPLOAD_ERR_OK) {
            echo "Image upload error: " . $image_error;
        }
    }

    // Insert content into database
    $stmt = $pdo->prepare("INSERT INTO contents (title, body, image_path, creator_id) VALUES (:title, :body, :image_path, :creator_id)");
    $stmt->execute(['title' => $title, 'body' => $body, 'image_path' => $image_path, 'creator_id' => $creator_id]);

    // Redirect to public_view.php after successful content creation
    header("Location: public_view.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Creator Dashboard</title>
    <style>
    body {
        font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 0;
        background: linear-gradient(to bottom right, #4e54c8, #8f94fb);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        color: #333;
    }

    .dashboard-container {
        background: #fff;
        border-radius: 12px;
        padding: 40px;
        width: 400px;
        box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.2);
        text-align: center;
    }

    h1 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #4e54c8;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    label {
        font-size: 14px;
        text-align: left;
    }

    input[type="text"],
    textarea,
    input[type="file"] {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        width: 100%;
        box-sizing: border-box;
    }

    textarea {
        resize: vertical;
        height: 150px;
    }

    button {
        background: linear-gradient(to right, #4e54c8, #8f94fb);
        color: #fff;
        border: none;
        border-radius: 25px;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        transition: transform 0.2s;
    }

    button:hover {
        transform: scale(1.05);
    }
    </style>
</head>

<body>

    <div class="dashboard-container">
        <h1>Welcome, Content Creator</h1>

        <!-- Add Content Form -->
        <h2>Add Content</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Title:</label>
            <input type="text" name="title" required><br>

            <label>Body:</label>
            <textarea name="body" required></textarea><br>

            <label>Image:</label>
            <input type="file" name="image"><br>

            <button type="submit" name="add_content">Add Content</button>
        </form>
    </div>
</body>

</html>