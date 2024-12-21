<?php
    require 'config.php';

    // Handle search
    $search_query = isset($_GET['search']) ? $_GET['search'] : '';

    // Base SQL query to fetch all content
    $sql = "SELECT contents.*, users.username AS creator_name 
            FROM contents 
            JOIN users ON contents.creator_id = users.id";

    // Append search condition if search query exists
    if ($search_query) {
        $sql .= " WHERE contents.title LIKE :search_query OR contents.body LIKE :search_query";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search_query' => '%' . $search_query . '%']);
    } else {
        $stmt = $pdo->query($sql);
    }

    // Fetch all data
    $contents = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html>
<head>
    <title>Public View</title>
</head>
<body>
    <h1>Public Content</h1>

    <!-- Search Form -->
    <form method="GET">
        <label>Search:</label>
        <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>">
        <button type="submit">Search</button>
    </form>

    <!-- Display Approved Contents -->
    <ul>
        <?php foreach ($contents as $content): ?>
            <li>
                <strong><?= htmlspecialchars($content['title']) ?></strong> by <?= htmlspecialchars($content['creator_name']) ?>
                <p><?= htmlspecialchars($content['body']) ?></p>
                <?php if ($content['image_path']): ?>
                    <img src="<?= htmlspecialchars('uploads/' . basename($content['image_path'])) ?>" alt="Content Image" width="100">
                <?php endif; ?>
            </li>
    <?php endforeach; ?>

    </ul>
</body>
</html>
