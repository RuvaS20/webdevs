<?php
session_start();
?>

<header>
    <nav>
        <ul>
            <li><a href="/">Home</a></li>
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
