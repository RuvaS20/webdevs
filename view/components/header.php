<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Arimo:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Arimo", sans-serif;
            background-color: #052B2B;
            color: #EBE5D5;
        }

        /* Header Styles */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(to bottom, rgba(5, 43, 43, 0.95), rgba(5, 43, 43, 0.85));
            backdrop-filter: blur(8px);
            border-bottom: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 4rem;
        }

        nav ul {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 3rem;
            list-style: none;
        }

        nav ul li:first-child {
            margin-right: auto;
        }

        nav ul li a {
            color: #EBE5D5;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            position: relative;
            letter-spacing: 0.5px;
        }

        nav ul li:first-child a {
            color: #FECE63;
            font-family: "DM Serif Display", serif;
            font-size: 1.5rem;
            padding: 0;
        }

        nav ul li a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 1px;
            background-color: #FECE63;
            transition: width 0.3s ease;
        }

        nav ul li a:hover::after {
            width: 80%;
        }

        nav ul li:first-child a::after {
            display: none;
        }

        /* Login/Register Buttons */
        nav ul li:last-child a {
            background-color: #FECE63;
            color: #3A4E3C;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            transition: transform 0.3s ease;
        }

        nav ul li:last-child a:hover {
            transform: translateY(-2px);
        }

        nav ul li:last-child a::after {
            display: none;
        }

        /* Add spacing below header for fixed positioning */
        main {
            margin-top: 5rem;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            nav {
                padding: 1rem;
            }

            nav ul {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            nav ul li:first-child {
                margin-right: 0;
                margin-bottom: 1rem;
            }

            nav ul li a {
                display: block;
                padding: 0.8rem 1rem;
            }

            nav ul li:last-child a {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="/">Msasa Academy</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="/view/dashboard.php">Dashboard</a></li>
                    <li><a href="/actions/auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="/view/auth/login.php">Login</a></li>
                    <li><a href="/view/auth/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>