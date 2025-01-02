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
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'];
        $body = $_POST['body'];
        $image = $_FILES['image'];

        // Handle image upload if a new one is provided
        $image_path = $content['image_path']; // Default to current image if not replaced
        if ($image['error'] === UPLOAD_ERR_OK) {
            $image_path = uniqid() . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
            move_uploaded_file($image['tmp_name'], 'uploads/' . $image_path);
        }

        // Update content details in the database
        $stmt = $pdo->prepare("UPDATE contents SET title = :title, body = :body, image_path = :image_path WHERE id = :content_id");
        $stmt->execute([
            'title' => $title,
            'body' => $body,
            'image_path' => $image_path,
            'content_id' => $content_id
        ]);

        // Redirect back to the editor dashboard
        header("Location: editor_dashboard.php");
        exit;
    }
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
            align-items: flex-start;
            min-height: 100vh;
            flex-direction: column;
        }
        .header, .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: #fff;
            padding: 10px 30px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-bottom: 2px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: -8%;
            border-radius: 15px;
        }
        .header h1 {
            color: #4e54c8;
            margin: 0;
        }
        .header a {
            padding: 10px 20px;
            border-radius: 8px;
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .header a:hover {
            transform: translateY(-3px);
            box-shadow: 0px 6px 15px rgba(78, 84, 200, 0.4);
        }
        .container {
            background: #fff;
            border-radius: 15px;
            padding: 20px 30px;
            margin-top: 30px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
        }
        h1 {
            color: #4e54c8;
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        input[type="text"], textarea {
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        textarea {
            resize: vertical;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            color: #fff;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0px 6px 15px rgba(78, 84, 200, 0.4);
        }
        .image-preview {
            max-width: 200px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Edit Content</h1>
    <a href="editor_dashboard.php">Back to Dashboard</a>
</div>

<div class="container">
    <form method="POST" enctype="multipart/form-data">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($content['title']) ?>" required>

        <label for="body">Body</label>
        <textarea id="body" name="body" rows="5" required><?= htmlspecialchars($content['body']) ?></textarea>

        <label for="image">Image</label>
        <?php if (!empty($content['image_path'])): ?>
            <div class="image-preview">
                <img src="uploads/<?= htmlspecialchars($content['image_path']) ?>" alt="Content Image">
            </div>
        <?php endif; ?>
        <input type="file" id="image" name="image">

        <button type="submit">Save Changes</button>
    </form>
</div>

</body>
</html>
