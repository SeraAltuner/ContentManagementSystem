<?php
session_start();

// Include the configuration file
require_once 'config.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $view_all_contents = isset($_POST['permissions']) && in_array('view_all_contents', $_POST['permissions']) ? 1 : 0;
    
    // Use $pdo to prepare and execute the query
    $stmt = $pdo->prepare("UPDATE users SET view_all_contents = :view_all_contents WHERE username = :username");
    $stmt->execute([
        'username' => $username,
        'view_all_contents' => $view_all_contents
    ]);
    $message = "User permissions updated successfully.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Content Creator Permissions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, #4e54c8, #8f94fb);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }
        .container {
            background: #fff;
            border-radius: 15px;
            padding: 40px;
            width: 400px;
            text-align: center;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        h1 {
            font-size: 28px;
            color: #4e54c8;
            margin-bottom: 20px;
        }
        label {
            font-size: 14px;
            text-align: left;
            color: #555;
            display: block;
            margin-bottom: 8px;
        }
        input[type="text"], input[type="password"] {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.3s ease-in-out;
            margin-bottom: 15px;
        }
        input:focus {
            border-color: #4e54c8;
            outline: none;
        }
        .checkbox-group {
            text-align: left;
            margin-bottom: 15px;
        }
        .checkbox-group label {
            display: block;
        }
        button {
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            color: #fff;
            border: none;
            border-radius: 25px;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0px 6px 15px rgba(78, 84, 200, 0.4);
        }
        .message {
            background: #e0f7fa;
            color: #00796b;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 8px;
            font-size: 14px;
        }
    </style>
    <script>
        function toggleCheckbox(element) {
            const checkboxes = document.querySelectorAll('.checkbox-group input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                if (checkbox !== element) {
                    checkbox.checked = false;
                }
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Change Content Creator Permissions</h1>
        <?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>
        
        <!-- Form to change user permissions -->
        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter username" required>
            
            <label for="permissions">Permissions:</label>
            <div class="checkbox-group">
                <label><input type="checkbox" name="permissions[]" value="view_all_contents" onclick="toggleCheckbox(this)"> View All Contents</label>
                <label><input type="checkbox" name="permissions[]" value="view_only_their_contents" onclick="toggleCheckbox(this)"> View Only Their Contents</label>
            </div>
            
            <button type="submit">Change</button>
        </form>
    </div>
</body>
</html>
