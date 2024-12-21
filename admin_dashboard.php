<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

// Ensure the logged-in user is an Admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
    $stmt->execute(['username' => $username, 'password' => $password, 'role' => $role]);
    $message = "User created successfully.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Admin Dashboard</h1>
    <?php if (isset($message)) echo "<p style='color: green;'>$message</p>"; ?>
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" required>
        <br>
        <label>Password:</label>
        <input type="password" name="password" required>
        <br>
        <label>Role:</label>
        <select name="role" required>
            <option value="content_creator">Content Creator</option>
            <option value="editor">Editor</option>
        </select>
        <br>
        <button type="submit">Create User</button>
    </form>
</body>
</html>
