<?php
// view/forum/create_topic.php
require_once '../../utils/session_helper.php';
require_once '../../view/components/header.php';
?>

<div class="container">
    <h1>Create New Topic</h1>
    
    <form action="../../actions/forum/create_topic.php" method="POST">
        <div class="form-group">
            <label for="category">Category:</label>
            <select name="category" id="category" required>
                <option value="climate-science">Climate Science</option>
                <option value="solutions-innovation">Solutions & Innovation</option>
                <option value="policy-action">Policy & Action</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" required>
        </div>
        
        <div class="form-group">
            <label for="content">Content:</label>
            <textarea name="content" id="content" rows="10" required></textarea>
        </div>
        
        <button type="submit">Create Topic</button>
    </form>
</div>

<?php require_once '../../view/components/footer.php'; ?>