<?php
session_start();

// Include the configuration file
require_once 'config.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .dashboard-container {
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
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        label {
            font-size: 14px;
            text-align: left;
            color: #555;
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
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
        
        <!-- Redirect to Create Account Page -->
        <form method="get" action="create_account.php">
            <button type="submit">Create Account</button>
        </form>

        <!-- Redirect to Edit Creator Page -->
        <form method="get" action="edit_creator.php">
            <button type="submit">Edit Creator</button>
        </form>
    </div>
</body>
</html>
