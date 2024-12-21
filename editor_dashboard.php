<?php
session_start();
require 'config.php';

if ($_SESSION['role'] !== 'editor') {
    die('Access denied');
}

// Handle content approval
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $content_id = $_GET['approve'];
    $stmt = $pdo->prepare("UPDATE contents SET is_approved = 1 WHERE id = :id");
    $stmt->execute(['id' => $content_id]);
    $message = "Content approved.";
}

// Handle content deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $content_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM contents WHERE id = :id");
    $stmt->execute(['id' => $content_id]);
    $message = "Content deleted.";
}

// Fetch all content
$stmt = $pdo->query("SELECT contents.*, users.username AS creator_name FROM contents JOIN users ON contents.creator_id = users.id");
$contents = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editor Dashboard</title>
</head>
<body>
    <h1>Welcome, Editor</h1>
    <?php if (isset($message)) echo "<p style='color: green;'>$message</p>"; ?>

    <!-- List of All Content -->
    <h2>All Contents</h2>
    <?php if (count($contents) > 0): ?>
        <ul>
            <?php foreach ($contents as $content): ?>
                <li>
                    <strong><?= htmlspecialchars($content['title']) ?></strong> by <?= htmlspecialchars($content['creator_name']) ?>
                    <p><?= htmlspecialchars($content['body']) ?></p>
                    <?php if ($content['image_path']): ?>
                        <img src="<?= htmlspecialchars($content['image_path']) ?>" alt="Content Image" width="100">
                    <?php endif; ?>
                    <br>
                    <?php if (!$content['is_approved']): ?>
                        <a href="editor_dashboard.php?approve=<?= $content['id'] ?>">Approve</a>
                    <?php endif; ?>
                    <a href="editor_dashboard.php?delete=<?= $content['id'] ?>">Delete</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No content available.</p>
    <?php endif; ?>
</body>
</html>
