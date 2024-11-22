<?php
// view/errors/maintenance.php
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Maintenance - Msasa Academy</title>
        <style>
        /* Same base CSS as other error pages */
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

        .maintenance-icon {
            font-size: 4rem;
            color: #f39c12;
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

        .estimated-time {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
            color: #666;
        }
        </style>
    </head>

    <body>
        <div class="error-container">
            <div class="maintenance-icon">🛠️</div>
            <h2 class="error-message">Scheduled Maintenance</h2>
            <p class="error-description">We're currently performing scheduled maintenance to improve our services.</p>
            <div class="estimated-time">
                Estimated completion time: <br>
                <strong><?php echo date('F j, Y H:i', strtotime('+2 hours')); ?> UTC</strong>
            </div>
            <p>Thank you for your patience!</p>
        </div>
    </body>

</html>
