<?php
session_start();
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Msasa</title>
    <link rel="stylesheet" href="../assets/css/stylea.css">
</head>
<body>
    <div class="container">
        <!-- Background video -->
        <div class="video-background">
            <video autoplay loop muted>
                <source src="../assets/videos/earth-spinning.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>

        <!-- Left Section -->
        <div class="left-section">
            <div class="logo">
                Msasa<span class="highlight"></span>
            </div>
            <h1>Welcome to<br>Our Community</h1>
            <?php
            if(isset($_SESSION['success'])) {
                echo '<p class="success-message">' . $_SESSION['success'] . '</p>';
                unset($_SESSION['success']);
            }
            ?>
        </div>

        <!-- Right Section -->
        <div class="right-section">
            <h2>Welcome Back!</h2>
            <p>Login to your account</p>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <label for="username">Your username or email</label>
                <input type="text" id="username" name="username" placeholder="Your username or email" required>
                
                <label for="password">Your password</label>
                <input type="password" id="password" name="password" placeholder="Your password" required>
                
                <div class="options">
                    <div>
                        <input type="checkbox" id="remember-me" name="remember">
                        <label for="remember-me">Remember me</label>
                    </div>
                    <a href="forgot_password.php" class="forgot-password">Forgot password?</a>
                </div>
                
                <button type="submit" class="login-btn">Log In</button>
            </form>

            <p class="or">or</p>
            
            <div class="social-login">
                <button class="google">Continue with Google</button>
                <button class="linkedin">LinkedIn</button>
                <button class="github">GitHub</button>
                <button class="facebook">Facebook</button>
            </div>

            <p>Don't have an account? <a href="choose_role.php">Sign up</a></p>
        </div>
    </div>

    <?php
    // If there was a successful registration, show a welcome message
    if(isset($_SESSION['registration_success'])) {
        echo "<script>
            alert('Registration successful! Please login with your credentials.');
        </script>";
        unset($_SESSION['registration_success']);
    }
    ?>
</body>
</html>
