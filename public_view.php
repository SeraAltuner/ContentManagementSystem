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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public View</title>
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
            justify-content: center;
            margin-bottom: 20px;
        }
        input[type="text"] {
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            flex: 1;
            margin-right: 10px;
            transition: border-color 0.3s ease-in-out;
        }
        input[type="text"]:focus {
            border-color: #4e54c8;
            outline: none;
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
    </style>
</head>
<body>
    <!-- Header with Log In button -->
    <div class="header">
        <h1>Public Content</h1>
        <a href="login.php">Log In</a>
    </div>

    <div class="container">
        <!-- Search Form -->
        <form method="GET">
            <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search content...">
            <button type="submit">Search</button>
        </form>

        <!-- Content Grid -->
        <div class="content-grid">
            <?php if (!empty($contents)): ?>
                <?php foreach ($contents as $content): ?>
                    <div class="content-card">
                        <?php if (!empty($content['image_path']) && file_exists('uploads/' . $content['image_path'])): ?>
                            <img src="uploads/<?= htmlspecialchars($content['image_path']) ?>" alt="Content Image">
                        <?php else: ?>
                            <img src="uploads/default_image.jpg" alt="Default Image">
                        <?php endif; ?>
                        <h2><?= htmlspecialchars($content['title']) ?></h2>
                        <p><?= htmlspecialchars($content['body']) ?></p>
                        <p class="creator">By <?= htmlspecialchars($content['creator_name']) ?></p>
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
</body>
</html>



