<?php
// view/forum/create_topic.php
require_once '../../utils/session_helper.php';
require_once '../../view/components/header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}
?>

<!DOCTYPE html>

<html>
    <head>
        <link href="../../assets/css/forum_topic.css" rel="stylesheet">
    </head>
    <body>
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