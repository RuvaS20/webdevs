<?php
session_start();
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Role</title>
    <link rel="stylesheet" href="../assets/css/style.css">
   </head>
<body>
    <div class="split left">
        <div class="content">
            <h2>For <span>Teachers</span></h2>
            <p>Create and manage your classes, upload materials, and track student progress.</p>
            <a href="register.php?role=teacher" class="login-button">Continue as Teacher</a>
        </div>
    </div>
    <div class="split right">
        <div class="content">
            <h2>For <span>Students</span></h2>
            <p>Access learning materials, participate in classes, and track your progress.</p>
            <a href="register.php?role=student" class="login-button">Continue as Student</a>
        </div>
    </div>
</body>
</html>
