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

        if ($user['role'] === 'admin') {
            header('Location: admin_dashboard.php');
        } elseif ($user['role'] === 'editor') {
            header('Location: public_view.php');
        } elseif ($user['role'] === 'content_creator') {
            header('Location: public_view.php');
        } else {
            header('Location: default_dashboard.php');
        }
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
            overflow: hidden;
            position: relative;
        }

        /* Background gradient with animation */
        .floating-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #4e54c8, #8f94fb, #6a82fb);
            background-size: 400% 400%;
            animation: gradientAnimation 8s ease infinite;
            z-index: -1;
        }

        @keyframes gradientAnimation {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        /* Floating particles effect */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            animation: floatParticles 6s ease-in-out infinite;
        }

        @keyframes floatParticles {
            0% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            50% {
                transform: translateY(-30px) scale(1.2);
                opacity: 0.6;
            }
            100% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        .login-container {
            background: #fff;
            border-radius: 15px;
            padding: 40px;
            width: 400px;
            text-align: center;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
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

        input[type="text"], input[type="password"] {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.3s ease-in-out, transform 0.2s ease;
        }

        input:focus {
            border-color: #4e54c8;
            outline: none;
            transform: scale(1.05);
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
            color: red;
            font-size: 14px;
            margin-bottom: 20px;
        }

    </style>
</head>
<body>
    <div class="floating-background"></div> <!-- Background with animated gradient -->
    <div class="particles"></div> <!-- Floating particles -->

    <div class="login-container">
        <h1>Login</h1>
        <?php if (isset($error)) echo "<p class='message'>$error</p>"; ?>
        <form method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Type your username" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Type your password" required>
            <a href="#" class="forgot-password">Forgot password?</a>
            <button type="submit">Login</button>
        </form>
     
    </div>

    <script>
        // Particle generation
        function createParticles() {
            const particleCount = 50; // Number of particles
            const particlesContainer = document.querySelector('.particles');

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');

                // Randomize size and position of particles
                const size = Math.random() * 6 + 4; // Size between 4px and 10px
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.top = `${Math.random() * 100}%`;
                particle.style.left = `${Math.random() * 100}%`;

                // Randomize animation delay
                const delay = Math.random() * 4;
                particle.style.animationDelay = `${delay}s`;

                particlesContainer.appendChild(particle);
            }
        }

        createParticles(); // Call function to generate particles
    </script>
</body>
</html>
