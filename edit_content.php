<?php
    require 'config.php';
    session_start();

    // Ensure user is logged in and has the editor role
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'editor') {
        header("Location: login.php");
        exit;
    }

    // Fetch the content to be edited using the ID passed via GET
    if (isset($_GET['id'])) {
        $content_id = $_GET['id'];
        
        // Get the content details
        $stmt = $pdo->prepare("SELECT * FROM contents WHERE id = :content_id");
        $stmt->execute(['content_id' => $content_id]);
        $content = $stmt->fetch();

        if (!$content) {
            echo "Content not found!";
            exit;
        }
    } else {
        echo "No content ID provided!";
        exit;
    }

    // Handle form submission to update the content
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_content'])) {
        $title = $_POST['title'];
        $body = $_POST['body'];
        $image = $_FILES['image'];
        $is_approved = isset($_POST['is_approved']) ? 1 : 0; // Capture approval status

        // Handle image upload if a new one is provided
        $image_path = $content['image_path']; // Default to current image if not replaced
        if ($image['error'] === UPLOAD_ERR_OK) {
            $image_path = uniqid() . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
            move_uploaded_file($image['tmp_name'], 'uploads/' . $image_path);
        }

        // Update content details and approval status in the database
        $stmt = $pdo->prepare("UPDATE contents SET title = :title, body = :body, image_path = :image_path, is_approved = :is_approved WHERE id = :content_id");
        $stmt->execute([
            'title' => $title,
            'body' => $body,
            'image_path' => $image_path,
            'is_approved' => $is_approved,
            'content_id' => $content_id
        ]);
    }

    // Handle comment submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
        $comment = $_POST['comment'];

        // Insert the comment into the database
        $stmt = $pdo->prepare("INSERT INTO comments (content_id, user_id, comment) VALUES (:content_id, :user_id, :comment)");
        $stmt->execute([
            'content_id' => $content_id,
            'user_id' => $_SESSION['user_id'],
            'comment' => $comment
        ]);
    }

    // Fetch existing comments for the content
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE content_id = :content_id ORDER BY created_at DESC");
    $stmt->execute(['content_id' => $content_id]);
    $comments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Content</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, #4e54c8, #8f94fb);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header h1 {
            color: #4e54c8;
            margin: 0;
            font-size: 18px;
        }

        .header a {
            padding: 8px 16px;
            border-radius: 5px;
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
        }

        input[type="text"], textarea, input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
        }

        .image-preview img {
            max-width: 100%;
            border-radius: 5px;
        }

        button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            color: #fff;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            align-self: flex-end;
        }

        button:hover {
            background: #3e44b8;
        }

        .comments {
            margin-top: 20px;
        }

        .comment {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .comment textarea {
            margin-top: 10px;
        }

    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Edit Content</h1>
        <a href="editor_dashboard.php">Dashboard</a>
    </div>

    <!-- Content editing form -->
    <form method="POST" enctype="multipart/form-data">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($content['title']) ?>" required>

        <label for="body">Body</label>
        <textarea id="body" name="body" rows="4" required><?= htmlspecialchars($content['body']) ?></textarea>

        <label for="image">Image</label>
        <?php if (!empty($content['image_path'])): ?>
            <div class="image-preview">
                <img src="uploads/<?= htmlspecialchars($content['image_path']) ?>" alt="Content Image">
            </div>
        <?php endif; ?>
        <input type="file" id="image" name="image">

        <div style="display: flex; justify-content: space-between; align-items: center;">
            <label for="approve-checkbox" style="display: flex; align-items: center;">
                <input type="checkbox" id="approve-checkbox" name="is_approved" style="margin-right: 5px;" <?= $content['is_approved'] ? 'checked' : '' ?>> Approve Content
            </label>
            <button type="submit" name="save_content">Save Changes</button>
        </div>
    </form>

    <!-- Comment Section -->
    <div class="comments">
        <h2>Comments</h2>
        <form method="POST">
            <textarea name="comment" rows="3" placeholder="Add a comment..." required></textarea>
            <button type="submit">Post Comment</button>
        </form>

        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <strong>User <?= htmlspecialchars($comment['user_id']) ?>:</strong>
                <p><?= htmlspecialchars($comment['comment']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>

