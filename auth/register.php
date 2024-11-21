<?php
session_start();
require_once '../includes/config.php';

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
    <title>Register as <?php echo ucfirst($role); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #00416A, #E4E5E6);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            display: flex;
            overflow: hidden;
        }

        .left-section {
            flex: 1;
            background: linear-gradient(135deg, #3c8f3c, #2d682d);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo .highlight {
            width: 10px;
            height: 10px;
            background-color: white;
            border-radius: 50%;
        }

        .left-section h1 {
            font-size: 28px;
            margin-bottom: 20px;
        }

        .left-section p {
            font-size: 16px;
            line-height: 1.6;
            opacity: 0.9;
        }

        .right-section {
            flex: 1.5;
            padding: 40px;
            background: white;
        }

        h2 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #555;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #3c8f3c;
            outline: none;
        }

        .register-btn {
            width: 100%;
            padding: 14px;
            background: #3c8f3c;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 20px;
        }

        .register-btn:hover {
            background: #2d682d;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .login-link a {
            color: #3c8f3c;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .left-section {
                padding: 30px;
            }

            .right-section {
                padding: 30px;
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
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="register_process.php" method="POST">
                <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
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
