<?php
session_start();
require_once '../../db/database.php';
require_once '../../functions/admin_functions.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic_id = filter_input(INPUT_POST, 'topic_id', FILTER_VALIDATE_INT);
    $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING));
    $content = trim(filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING));
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);

    if (!$topic_id || !$title || !$content || !$category) {
        $_SESSION['error'] = "Invalid input data provided.";
        header("Location: ../../view/admin/manage_forum.php");
        exit();
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT topic_id, user_id FROM forum_topics WHERE topic_id = ?");
        $stmt->execute([$topic_id]);
        $topic = $stmt->fetch();
        
        if (!$topic) {
            throw new Exception("Topic not found.");
        }
        $stmt = $pdo->prepare("
            UPDATE forum_topics 
            SET 
                title = ?,
                content = ?,
                category = ?,
                last_updated_date = CURRENT_TIMESTAMP
            WHERE topic_id = ?
        ");
        
        $stmt->execute([
            $title,
            $content,
            $category,
            $topic_id
        ]);

        $pdo->commit();
        $_SESSION['success'] = "Topic updated successfully.";

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error updating topic: " . $e->getMessage());
        $_SESSION['error'] = "Database error while updating topic.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: ../../view/admin/manage_forum.php");
    exit();
}
else {
    $topic_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if (!$topic_id) {
        $_SESSION['error'] = "Invalid topic ID.";
        header("Location: ../../view/admin/manage_forum.php");
        exit();
    }
    $stmt = $pdo->prepare("
        SELECT t.*, u.username 
        FROM forum_topics t
        JOIN msasa_users u ON t.user_id = u.user_id
        WHERE t.topic_id = ?
    ");
    $stmt->execute([$topic_id]);
    $topic = $stmt->fetch();

    if (!$topic) {
        $_SESSION['error'] = "Topic not found.";
        header("Location: ../../view/admin/manage_forum.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Topic - Msasa Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Arimo:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Arimo", sans-serif;
        }

        body {
            background-color: #052B2B;
            color: #EBE5D5;
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #041f1f;
            padding: 30px;
            border-radius: 10px;
            border: 1px solid rgba(254, 206, 99, 0.1);
        }

        h1 {
            font-family: "DM Serif Display", serif;
            color: #FECE63;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #FECE63;
            margin-bottom: 8px;
            font-size: 0.9em;
        }

        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            background: #052B2B;
            border: 1px solid rgba(254, 206, 99, 0.1);
            border-radius: 5px;
            color: #EBE5D5;
            font-size: 16px;
        }

        textarea {
            height: 200px;
            resize: vertical;
        }

        .meta-info {
            background: rgba(254, 206, 99, 0.1);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9em;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: 1px solid #FECE63;
            background: transparent;
            color: #FECE63;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: #FECE63;
            color: #052B2B;
        }

        .btn-cancel {
            border-color: #e74c3c;
            color: #e74c3c;
        }

        .btn-cancel:hover {
            background: #e74c3c;
            color: #EBE5D5;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Topic</h1>

        <div class="meta-info">
            <p>Author: <?php echo htmlspecialchars($topic['username']); ?></p>
            <p>Created: <?php echo date('F j, Y g:i A', strtotime($topic['created_date'])); ?></p>
            <p>Last Updated: <?php echo date('F j, Y g:i A', strtotime($topic['last_updated_date'])); ?></p>
        </div>

        <form method="POST" action="update_topic.php">
            <input type="hidden" name="topic_id" value="<?php echo $topic['topic_id']; ?>">
            
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($topic['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="climate-science" <?php echo $topic['category'] === 'climate-science' ? 'selected' : ''; ?>>Climate Science</option>
                    <option value="solutions-innovation" <?php echo $topic['category'] === 'solutions-innovation' ? 'selected' : ''; ?>>Solutions & Innovation</option>
                    <option value="policy-action" <?php echo $topic['category'] === 'policy-action' ? 'selected' : ''; ?>>Policy & Action</option>
                </select>
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" required><?php echo htmlspecialchars($topic['content']); ?></textarea>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn">Save Changes</button>
                <a href="../../view/admin/manage_forum.php" class="btn btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>