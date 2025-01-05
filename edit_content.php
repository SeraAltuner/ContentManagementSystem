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
    margin-top: 0;
    border-radius: 15px;
    position: fixed;
    top: 2%;
    left: 50%;
    transform: translateX(-50%);
    width: 90%;
    z-index: 1000;
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
            margin-top: 90px;
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
    <!-- Content editing form -->
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

        <!-- Approval Checkbox -->
        <label for="approve-checkbox">Approve Content</label>
        <input type="checkbox" id="approve-checkbox" name="is_approved" <?= $content['is_approved'] ? 'checked' : '' ?>>

        <button type="submit" name="save_content">Save Changes</button>
    </form>

    <!-- Comment Section -->
    <h2>Comments</h2>
    <form method="POST">
        <textarea name="comment" rows="3" placeholder="Add a comment..." required></textarea>
        <button type="submit">Post Comment</button>
    </form>

    
</div>

<script>
    // Function to handle the content approval status
    function approveContent(contentId) {
        const checkbox = document.querySelector(`#approve-checkbox`);
        const isChecked = checkbox.checked;

        // Use AJAX to update the is_approved attribute
        const formData = new FormData();
        formData.append('approve_content_id', contentId);
        formData.append('is_approved', isChecked ? 1 : 0); // Send the current state (1 for approved, 0 for not approved)

        fetch('editor_dashboard.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Content approval status updated');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Listen for changes to the approval checkbox
    document.querySelector('#approve-checkbox').addEventListener('change', function() {
        approveContent(<?= $content_id ?>); // Call the function with the current content ID
    });
</script>

</body>
</html>
