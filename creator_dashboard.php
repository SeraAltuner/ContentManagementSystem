<?php
session_start();
require 'config.php'; // Make sure this file has your PDO connection setup

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

    // Handle image upload
    $image_path = null;
    // if (!empty($_FILES['image']['name'])) {
    //     // $target_dir = "uploads/";
    //     // $image_path = $target_dir . basename($_FILES['image']['name']);
    //     $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/cms/uploads/";
    //     $image_path = $target_dir . basename($_FILES['image']['name']);

    //     move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    // }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/cms/uploads/";
        $image_path = $target_dir . basename($_FILES['image']['name']);

        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            echo "The file has been uploaded successfully.";
        } else {
            echo "Error: Failed to move the uploaded file.";
        }
    } else {
        echo "Error: " . $_FILES['image']['error']; // Display specific upload error
    }
    

    $stmt = $pdo->prepare("INSERT INTO contents (title, body, image_path, creator_id) VALUES (:title, :body, :image_path, :creator_id)");
    $stmt->execute(['title' => $title, 'body' => $body, 'image_path' => $image_path, 'creator_id' => $creator_id]);
    $message = "Content added successfully.";
}

// Handle content deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $content_id = $_GET['delete'];
    $creator_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("DELETE FROM contents WHERE id = :id AND creator_id = :creator_id");
    $stmt->execute(['id' => $content_id, 'creator_id' => $creator_id]);
    $message = "Content deleted successfully.";
}

// Fetch all content created by the logged-in user
$stmt = $pdo->prepare("SELECT * FROM contents WHERE creator_id = :creator_id");
$stmt->execute(['creator_id' => $_SESSION['user_id']]);
$contents = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Content Creator Dashboard</title>
</head>
<body>
    <h1>Welcome, Content Creator</h1>
    <?php if (isset($message)) echo "<p style='color: green;'>$message</p>"; ?>

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

    <!-- List of Created Content -->
    <h2>Your Contents</h2>
    <?php if (count($contents) > 0): ?>
        <ul>
            <?php foreach ($contents as $content): ?>
                <li>
                    <strong><?= htmlspecialchars($content['title']) ?></strong>
                    <p><?= htmlspecialchars($content['body']) ?></p>
                    <?php if ($content['image_path']): ?>
                        <img src="<?= htmlspecialchars($content['image_path']) ?>" alt="Content Image" width="100">
                    <?php endif; ?>
                    <br>
                    <a href="creator_dashboard.php?delete=<?= $content['id'] ?>">Delete</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>You have not created any content yet.</p>
    <?php endif; ?>
</body>
</html>
