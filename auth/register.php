<?php
session_start();
require_once '../db/config.php';

if(!isset($_GET['role']) || ($_GET['role'] !== 'teacher' && $_GET['role'] !== 'student')) {
    header('Location: choose_role.php');
    exit();
}
$role = $_GET['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as <?php echo ucfirst($role); ?> - Msasa Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Arimo:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
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
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .container {
            width: 100%;
            max-width: 1000px;
            display: flex;
            background: rgba(235, 229, 213, 0.03);
            border-radius: 20px;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
            overflow: hidden;
            position: relative;
        }
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
            z-index: 0;
        }

        .left-section {
            flex: 1;
            padding: 3rem;
            position: relative;
            z-index: 1;
            background: rgba(254, 206, 99, 0.1);
            border-right: 0.5px solid rgba(235, 229, 213, 0.2);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            font-family: "DM Serif Display", serif;
            font-size: 2.5rem;
            color: #FECE63;
            margin-bottom: 2rem;
        }

        .highlight {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #FECE63;
            border-radius: 50%;
            margin-left: 0.5rem;
        }

        .left-section h1 {
            font-family: "DM Serif Display", serif;
            color: #FECE63;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        .left-section p {
            font-size: 1.1rem;
            line-height: 1.6;
            opacity: 0.9;
        }

        .right-section {
            flex: 1.5;
            padding: 3rem;
            position: relative;
            z-index: 1;
        }

        h2 {
            font-family: "DM Serif Display", serif;
            color: #FECE63;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: #EBE5D5;
            opacity: 0.8;
            margin-bottom: 2rem;
        }

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

        .register-btn {
            width: 100%;
            padding: 1rem;
            background: #FECE63;
            color: #3A4E3C;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(254, 206, 99, 0.2);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #EBE5D5;
            opacity: 0.8;
        }

        .login-link a {
            color: #FECE63;
            text-decoration: none;
            transition: opacity 0.3s ease;
        }

        .login-link a:hover {
            opacity: 0.8;
        }

        .error-message {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: #EBE5D5;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .left-section {
                padding: 2rem;
                text-align: center;
            }

            .right-section {
                padding: 2rem;
            }

            .left-section h1 {
                font-size: 2rem;
            }

            h2 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-section">
            <div class="logo">
                Msasa<span class="highlight"></span>
            </div>
            <h1>Welcome to Msasa</h1>
            <p>Join our community of <?php echo $role; ?>s and unlock a world of learning opportunities. Start your journey with us today.</p>
        </div>

        <div class="right-section">
            <h2>Register as <?php echo ucfirst($role); ?></h2>
            <p class="subtitle">Please fill in your details to create your account</p>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php 
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="register_process.php" method="POST">
                <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           placeholder="Choose a username" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           placeholder="Enter your email" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Create a password" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           placeholder="Confirm your password" 
                           required>
                </div>
                
                <button type="submit" class="register-btn">Create Account</button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </div>
    </div>
</body>
</html>
