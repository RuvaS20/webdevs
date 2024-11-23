<?php
session_start();
require_once '../db/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Role - Msasa Academy</title>
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
            background: #052B2B;
            color: #EBE5D5;
            min-height: 100vh;
            overflow: hidden;
        }

        /* Split Container Styles */
        .split {
            position: absolute;
            width: 50%;
            height: 100%;
            overflow: hidden;
        }

        .left {
            left: 0;
            background: rgba(5, 43, 43, 0.7);
        }

        .right {
            right: 0;
            background: rgba(5, 43, 43, 0.7);
        }

        /* Glassmorphism Effects */
        .content {
            position: relative;
            height: 100%;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            background: rgba(235, 229, 213, 0.03);
            backdrop-filter: blur(10px);
            transition: transform 0.5s ease;
        }

        /* Mesh Gradient Overlays */
        .left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 30%, rgba(254, 206, 99, 0.1) 0%, transparent 70%),
                        radial-gradient(circle at 70% 70%, rgba(5, 43, 43, 0.15) 0%, transparent 70%);
            z-index: -1;
        }

        .right::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 70% 30%, rgba(254, 206, 99, 0.1) 0%, transparent 70%),
                        radial-gradient(circle at 30% 70%, rgba(5, 43, 43, 0.15) 0%, transparent 70%);
            z-index: -1;
        }

        /* Hover Effects */
        .split:hover .content {
            transform: scale(1.02);
        }

        .left .content {
            border-right: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .right .content {
            border-left: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        /* Content Styles */
        h2 {
            font-family: "DM Serif Display", serif;
            font-size: 3rem;
            color: #EBE5D5;
            margin-bottom: 1.5rem;
        }

        h2 span {
            color: #FECE63;
            display: block;
            font-size: 3.5rem;
            margin-top: 0.5rem;
        }

        p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2.5rem;
            opacity: 0.9;
            max-width: 400px;
        }

        .login-button {
            display: inline-block;
            background: #FECE63;
            color: #3A4E3C;
            padding: 1rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(254, 206, 99, 0.2);
        }

        /* Decorative Elements */
        .split::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(254, 206, 99, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            z-index: -1;
        }

        .left::after {
            bottom: 10%;
            left: 10%;
        }

        .right::after {
            top: 10%;
            right: 10%;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .split {
                position: relative;
                width: 100%;
                height: 50vh;
            }

            .content {
                padding: 2rem;
                align-items: center;
                text-align: center;
            }

            h2 {
                font-size: 2.5rem;
            }

            h2 span {
                font-size: 3rem;
            }

            p {
                font-size: 1rem;
            }

            .login-button {
                padding: 0.8rem 1.5rem;
                font-size: 1rem;
            }
        }
    </style>
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