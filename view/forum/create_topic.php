<?php
require_once '../../utils/session_helper.php';
require_once '../../view/components/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}
?>

<!DOCTYPE html>

<html>
    <head>
        <link href="../../assets/css/forum_topic.css" rel="stylesheet">
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
        }

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

        main {
            margin-top: 5rem;
        }

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
                    <li><a href="index.php">Forum</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>
        <div class="container">
            <h1>Create New Topic</h1>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <form action="../../actions/forum/create_topic.php" method="POST">
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select name="category" id="category" required>
                        <option value="" disabled selected>Select a category</option>
                        <option value="climate-science">Climate Science</option>
                        <option value="solutions-innovation">Solutions & Innovation</option>
                        <option value="policy-action">Policy & Action</option>
                    </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" 
                            name="title" 
                            id="title" 
                            required
                            placeholder="Enter your topic title"
                            maxlength="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Content:</label>
                        <textarea name="content" 
                                id="content" 
                                rows="10" 
                                required
                                placeholder="Share your thoughts..."
                                maxlength="5000"></textarea>
                    </div>
                    
                    <button type="submit">Create Topic</button>
                </form>
            </div>

            <?php require_once '../../view/components/footer.php'; ?>
    </body>
</html>
