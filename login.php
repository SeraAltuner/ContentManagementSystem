<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        // Check the role of the user and redirect accordingly
        if ($user['role'] === 'admin') {
            header('Location: admin_dashboard.php');
        } elseif ($user['role'] === 'editor') {
            header('Location: editor_dashboard.php');
        } elseif ($user['role'] === 'content_creator') {
            header('Location: creator_dashboard.php');
        } else {
            // Redirect to a default dashboard or login page for users with unrecognized roles
            header('Location: default_dashboard.php');
        }
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" required>
        <br>
        <label>Password:</label>
        <input type="password" name="password" required>
        <br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
