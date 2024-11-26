<?php
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Server Error - Msasa Academy</title>
        <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f7f9fc;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #e74c3c;
            margin: 0;
        }

        .error-message {
            font-size: 1.5rem;
            color: #2c3e50;
            margin: 1rem 0;
        }

        .error-description {
            color: #7f8c8d;
            margin-bottom: 2rem;
        }

        .home-button {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .home-button:hover {
            background-color: #2980b9;
        }
        </style>
    </head>

    <body>
        <div class="error-container">
            <h1 class="error-code">500</h1>
            <h2 class="error-message">Server Error</h2>
            <p class="error-description">Oops! Something went wrong on our end. We're working to fix it.</p>
            <a href="/" class="home-button">Return to Homepage</a>
        </div>
    </body>

</html>
