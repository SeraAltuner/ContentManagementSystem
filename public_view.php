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
$approval_filter = isset($_GET['approval_filter']) ? $_GET['approval_filter'] : 'all';

// Get the logged-in user's ID, role, and view_all_contents attribute
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$view_all_contents = 1;

if ($user_id && $user_role !== 'editor') {
    $stmt = $pdo->prepare("SELECT view_all_contents FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $view_all_contents = $stmt->fetchColumn();
}

// Base SQL query logic
if ($user_role === 'editor') {
    // Editors can view all content without restrictions
    $sql = "SELECT contents.*, users.username AS creator_name FROM contents JOIN users ON contents.creator_id = users.id";
    $params = [];
} elseif ($user_id && !$view_all_contents) {
    // Regular users can only view their own content
    $sql = "SELECT contents.*, users.username AS creator_name FROM contents JOIN users ON contents.creator_id = users.id WHERE contents.creator_id = :user_id";
    $params = ['user_id' => $user_id];
} else {
    // Other users can view all content
    $sql = "SELECT contents.*, users.username AS creator_name FROM contents JOIN users ON contents.creator_id = users.id";
    $params = [];
}

// Append search condition if search query exists
if ($search_query) {
    $sql .= " WHERE (contents.title LIKE :search_query OR contents.body LIKE :search_query)";
    $params['search_query'] = '%' . $search_query . '%';
}

// Append approval filter condition if the user is not an editor
if ($user_role !== 'editor') {
    if ($approval_filter === 'approved') {
        $sql .= $search_query ? " AND" : " WHERE";
        $sql .= " contents.is_approved = 1";
    } elseif ($approval_filter === 'not_approved') {
        $sql .= $search_query ? " AND" : " WHERE";
        $sql .= " contents.is_approved = 0";
    }
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// Fetch all data
$contents = $stmt->fetchAll();

// Handle content deletion
if (isset($_GET['delete_content_id']) && $user_id) {
    $content_id = $_GET['delete_content_id'];

    // Ensure the logged-in user is the creator of the content
    $stmt = $pdo->prepare("SELECT creator_id FROM contents WHERE id = :content_id");
    $stmt->execute(['content_id' => $content_id]);
    $creator_id = $stmt->fetchColumn();

    if ($creator_id == $user_id) {
        // If the logged-in user is the creator, delete the content
        $stmt = $pdo->prepare("DELETE FROM contents WHERE id = :content_id");
        $stmt->execute(['content_id' => $content_id]);

        // Redirect after deletion
        header("Location: public_view.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public View</title>
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
    top: 0;
    left: 5%; /* Adjust left to ensure it aligns properly */
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
    margin-top: 90px; /* Adjust this to match the height of your header */
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
}

.delete-content {
    position: absolute;
    top: 10px;
    right: 10px;
    color: #ff4c4c;
    cursor: pointer;
    font-size: 20px;
    display: inline-block;
}

.approval-checkbox {
    position: absolute;
    bottom: 10px;
    right: 10px;
    display: inline-block;
    background-color: green;
}

.approval-checkbox input[type="checkbox"] {
    width: 20px;
    height: 20px;
    background-color: green;
    border: 2px solid green;
    cursor: not-allowed;
}

.approval-checkbox input[type="checkbox"]:checked {
    background-color: green;
    border: 2px solid green;
}

.approval-checkbox:hover::after {
    content: "Approved by editor";
    position: absolute;
    bottom: 30px;
    right: 0;
    background-color: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 5px;
    border-radius: 5px;
    font-size: 12px;
}


    </style>
</head>
<body>
    <div class="header">
        <h1>Public Content</h1>
        <?php if (isset($_SESSION['role'])): ?>
    <?php if ($_SESSION['role'] === 'content_creator'): ?>
        <a href="creator_dashboard.php">Create Content</a>
        <a href="logout.php">Logout</a>
    <?php elseif ($_SESSION['role'] === 'editor'): ?>
        <a href="editor_dashboard.php">Edit Content</a>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Log In</a>
    <?php endif; ?>
<?php else: ?>
    <a href="login.php">Log In</a>
<?php endif; ?>

    </div>

    <div class="container">
    <form method="GET">
    <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search content...">
    <select name="approval_filter" id="approval-filter" onchange="applyFilter()">
        <option value="all" <?= $approval_filter === 'all' ? 'selected' : '' ?>>All</option>
        <option value="approved" <?= $approval_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
        <option value="not_approved" <?= $approval_filter === 'not_approved' ? 'selected' : '' ?>>Not Approved</option>
    </select>
    <button type="submit">Search</button>
</form>

<div class="content-grid">
    <?php if (!empty($contents)): ?>
        <?php foreach ($contents as $content): ?>
            <div class="content-card" id="content-<?= $content['id'] ?>">
                <?php if (!empty($content['image_path']) && file_exists('uploads/' . $content['image_path'])): ?>
                    <img src="uploads/<?= htmlspecialchars($content['image_path']) ?>" alt="Content Image">
                <?php else: ?>
                    <img src="uploads/default_image.jpg" alt="Default Image">
                <?php endif; ?>
                <h2><?= htmlspecialchars($content['title']) ?></h2>
                <p><?= htmlspecialchars($content['body']) ?></p>
                <p class="creator">By <?= htmlspecialchars($content['creator_name']) ?></p>

                <?php if ($content['creator_id'] == $user_id): ?>
                    <span class="delete-content" onclick="deleteContent(<?= $content['id'] ?>)">
                        <i class="fas fa-trash"></i>
                    </span>
                <?php endif; ?>

                <?php if ($content['is_approved'] == 1): ?>
                    <div class="approval-checkbox" title="Approved by editor">
                        <input type="checkbox" checked disabled>
                    </div>
                <?php endif; ?>

                <!-- Display Comments -->
                <?php
                // Fetch comments for the content
                $stmt = $pdo->prepare("SELECT c.comment, c.created_at, u.username FROM comments c INNER JOIN users u ON c.user_id = u.id WHERE c.content_id = ? ORDER BY c.created_at DESC");
                $stmt->execute([$content['id']]);
                $comments = $stmt->fetchAll();
                ?>

                <div class="comments-section">
                    <?php if (!empty($comments)): ?>
                        <h3>Comments:</h3>
                        <ul>
                            <?php foreach ($comments as $comment): ?>
                                <li>
                                    <strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                                    <p><?= htmlspecialchars($comment['comment']) ?></p>
                                    <small>Posted on <?= date('F j, Y, g:i a', strtotime($comment['created_at'])) ?></small>
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


    <script>
          function applyFilter() {
        const searchInput = document.querySelector('input[name="search"]').value;
        const approvalFilter = document.querySelector('#approval-filter').value;
        const url = new URL(window.location.href);
        url.searchParams.set('search', searchInput);
        url.searchParams.set('approval_filter', approvalFilter);
        window.location.href = url.toString();
    }

        function deleteContent(contentId) {
            window.location.href = 'public_view.php?delete_content_id=' + contentId;
        }
    </script>
</body>
</html>
