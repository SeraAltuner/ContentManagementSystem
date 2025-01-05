<?php
    require 'config.php';
    session_start();

    // If the user is logged out, set the flag
    if (isset($_SESSION['logged_out']) && $_SESSION['logged_out'] === true) {
        unset($_SESSION['logged_out']); // Reset the flag after checking
        $_SESSION['role'] = null; // Ensure the user is logged out completely
    }

    // Handle search
    $search_query = isset($_GET['search']) ? $_GET['search'] : '';

    // Get the logged-in user's ID and role
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $user_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

    // Base SQL query logic for editor
    if ($user_role === 'editor') {
        // Editors can view all content without restrictions
        $sql = "SELECT contents.*, users.username AS creator_name FROM contents JOIN users ON contents.creator_id = users.id";
        $params = [];
    }

    // Append search condition if search query exists
    if ($search_query) {
        $sql .= " WHERE (contents.title LIKE :search_query OR users.username LIKE :search_query)";
        $params['search_query'] = '%' . $search_query . '%';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Fetch all data
    $contents = $stmt->fetchAll();

    // Handle content deletion for editor
    if (isset($_GET['delete_content_id']) && $user_id && $user_role === 'editor') {
        $content_id = $_GET['delete_content_id'];

        // Delete the content
        $stmt = $pdo->prepare("DELETE FROM contents WHERE id = :content_id");
        $stmt->execute(['content_id' => $content_id]);

        // Redirect after deletion
        header("Location: editor_dashboard.php");
        exit;
    }

    // Handle AJAX content approval update
    if (isset($_POST['approve_content_id'], $_POST['is_approved']) && $user_id && $user_role === 'editor') {
        $content_id = $_POST['approve_content_id'];
        $is_approved = $_POST['is_approved']; // Get the new approval state (1 or 0)

        // Update the is_approved attribute
        $stmt = $pdo->prepare("UPDATE contents SET is_approved = :is_approved WHERE id = :content_id");
        $stmt->execute(['is_approved' => $is_approved, 'content_id' => $content_id]);

        // Return response indicating success
        echo json_encode(['success' => true]);
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Dashboard</title>
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
            justify-content: center;
            margin-bottom: 20px;
            gap: 10px;
        }
        input[type="text"] {
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            flex: 1;
            transition: border-color 0.3s ease-in-out;
        }
        input[type="text"]:focus {
            border-color: #4e54c8;
            outline: none;
        }
        select {
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            background: #fff;
            flex-shrink: 0;
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
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .content-card {
            background: #f9f9f9;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.15);
        }
        .content-card img {
            max-width: 100%;
            height: auto;
            max-height: 100px;
            border-radius: 8px;
            margin-bottom: 10px;
            object-fit: cover;
        }
        .content-card h2 {
            font-size: 18px;
            color: #4e54c8;
            margin-bottom: 10px;
        }
        .content-card p {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }
        .content-card .creator {
            font-size: 12px;
            color: #888;
            margin-top: 10px;
        }
        .more-options {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 18px;
            color: #4e54c8;
        }
        .options-dropdown {
            display: none;
            position: absolute;
            top: 30px;
            right: 0;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            width: 150px;
        }
        .options-dropdown a {
            display: block;
            padding: 10px;
            color: #4e54c8;
            text-decoration: none;
            font-size: 14px;
        }
        .options-dropdown a:hover {
            background-color: #f0f0f0;
        }
        .content-card.show-options .options-dropdown {
            display: block;
        }
        .comments-list {
            list-style-type: none;
            padding-left: 0;
            margin: 0;
        }

        .comment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            /* margin-bottom: 5px; */
        }

        .comments-header{
            position: relative;
            margin-top: 10px;
            margin-bottom: -1%;
            margin-left: -30px;
            text-align: left;
        }
            
    </style>
</head>
<body>
    <div class="header">
        <h1>Editor Dashboard</h1>
        <?php if (isset($_SESSION['role'])): ?>
            <?php elseif ($_SESSION['role'] === 'editor'): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Log In</a>
            <?php endif; ?>
        
            <a href="login.php">Log In</a>
       
    </div>

    <div class="container">
        <form method="GET">
            <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search content or creator...">
            <button type="submit">Search</button>
        </form>
        <div class="content-grid">
            <?php if (!empty($contents)): ?>
                <?php foreach ($contents as $content): ?>
                    <div class="content-card" id="content-<?= $content['id'] ?>">
                        <div class="more-options" onclick="toggleOptions(<?= $content['id'] ?>)">...</div>
                        <div class="options-dropdown">
                            <a href="edit_content.php?id=<?= $content['id'] ?>">Edit</a>
                            <a href="editor_dashboard.php?delete_content_id=<?= $content['id'] ?>" onclick="deleteContent(<?= $content['id'] ?>)">Delete</a>
                        </div>
                        <?php 
    $image_url = !empty($content['image_path']) ? "uploads/" . rawurlencode($content['image_path']) : "uploads/default_image.jpg"; 
?>
<img src="<?= $image_url ?>" alt="<?= !empty($content['image_path']) ? 'Content Image' : 'Default Image' ?>">

                        <h2><?= htmlspecialchars($content['title']) ?></h2>
                        <p><?= htmlspecialchars($content['body']) ?></p>
                        <div class="creator">Created by: <?= htmlspecialchars($content['creator_name']) ?></div>
                         <!-- Display Comments -->
                <?php
                // Fetch comments for the content
                $stmt = $pdo->prepare("SELECT c.comment, c.created_at, u.username FROM comments c INNER JOIN users u ON c.user_id = u.id WHERE c.content_id = ? ORDER BY c.created_at DESC");
                $stmt->execute([$content['id']]);
                $comments = $stmt->fetchAll();
                ?>

                <div class="comments-section">
                    <?php if (!empty($comments)): ?>
                        <!-- <h3 class="comments-header">Comments:</h3> -->
                        <ul class="comments-list">
                            <?php foreach ($comments as $comment): ?>
                                <li class="comment-item">
                                    <strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                                    <p><?= htmlspecialchars($comment['comment']) ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No comments yet. Be the first to comment!</p>
                    <?php endif; ?>
                </div>
               
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="content-card">
                    <h2>No content available</h2>
                    <p>Try adjusting your search or come back later.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function deleteContent(contentId) {
            window.location.href = 'editor_dashboard.php?delete_content_id=' + contentId;
        }

        function toggleOptions(contentId) {
            const card = document.getElementById('content-' + contentId);
            card.classList.toggle('show-options');
        }

        function approveContent(contentId) {
            const checkbox = document.querySelector(`#content-${contentId} .checkbox-container input`);
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
    </script>
</body>
</html>
