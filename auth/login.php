<?php
session_start();
require_once '../db/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Msasa Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Arimo:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Arimo", sans-serif;
            background-color: #052B2B;
            color: #EBE5D5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .container {
            background: rgba(235, 229, 213, 0.03);
            border-radius: 20px;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        /* Mesh gradient background */
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at top left, rgba(254, 206, 99, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at bottom right, rgba(5, 43, 43, 0.15) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        .logo {
            font-family: "DM Serif Display", serif;
            color: #FECE63;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        h2 {
            font-family: "DM Serif Display", serif;
            color: #FECE63;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .subtitle {
            text-align: center;
            color: #EBE5D5;
            opacity: 0.8;
            margin-bottom: 2rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            color: #FECE63;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        input {
            width: 100%;
            padding: 0.8rem 1rem;
            background: rgba(235, 229, 213, 0.05);
            border: 1px solid rgba(235, 229, 213, 0.2);
            border-radius: 10px;
            color: #EBE5D5;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #FECE63;
            box-shadow: 0 0 0 2px rgba(254, 206, 99, 0.2);
        }

        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remember-me input {
            width: auto;
        }

        .forgot-password {
            color: #FECE63;
            text-decoration: none;
            opacity: 0.9;
            transition: opacity 0.3s ease;
        }

        .forgot-password:hover {
            opacity: 1;
        }

        .login-btn {
            width: 100%;
            padding: 0.8rem;
            background: #FECE63;
            color: #3A4E3C;
            border: none;
            border-radius: 10px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(254, 206, 99, 0.2);
        }

        .signup-link {
            text-align: center;
            font-size: 0.9rem;
        }

        .signup-link a {
            color: #FECE63;
            text-decoration: none;
            transition: opacity 0.3s ease;
        }

        .signup-link a:hover {
            opacity: 0.8;
        }

        /* Message Styles */
        .error-message {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: #EBE5D5;
            padding: 0.8rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: center;
        }

        .success-message {
            background: rgba(46, 204, 113, 0.1);
            border: 1px solid rgba(46, 204, 113, 0.3);
            color: #EBE5D5;
            padding: 0.8rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: center;
        }

        /* Mobile Styles */
        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
            }

            .options {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">Msasa Academy</div>
        <h2>Welcome Back!</h2>
        <p class="subtitle">Login to your account</p>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php 
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="success-message">
                <?php 
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="options">
                <div class="remember-me">
                    <input type="checkbox" id="remember-me" name="remember">
                    <label for="remember-me">Remember me</label>
                </div>
            </div>
            
            <button type="submit" class="login-btn">Log In</button>
        </form>
        
        <p class="signup-link">Don't have an account? <a href="choose_role.php">Sign up</a></p>
    </div>
</body>
</html>