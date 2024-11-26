<?php
session_start();

require_once '../../db/database.php';
require_once '../../functions/forum_functions.php';
require_once '../../utils/session_helper.php';

$topicId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$topicId) {
    header('Location: index.php');
    exit();
}

incrementViewCount($topicId);

function getTopicDetails($topicId) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT 
                t.*,
                u.username,
                u.role
            FROM forum_topics t
            JOIN msasa_users u ON t.user_id = u.user_id
            WHERE t.topic_id = ?
        ");
        
        $stmt->bind_param("i", $topicId);

        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Error fetching topic: " . $e->getMessage());
        return null;
    }
}

function getTopicReplies($topicId, $page = 1, $perPage = 10) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $offset = ($page - 1) * $perPage;
        
        $stmt = $conn->prepare("
            SELECT 
                r.*,
                u.username,
                u.role
            FROM forum_replies r
            JOIN msasa_users u ON r.user_id = u.user_id
            WHERE r.topic_id = ?
            ORDER BY r.created_date ASC
            LIMIT ? OFFSET ?
        ");

        $stmt->bind_param("iii", $topicId, $perPage, $offset);

        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching replies: " . $e->getMessage());
        return [];
    }
}

$topic = getTopicDetails($topicId);
if (!$topic) {
    header('Location: index.php');
    exit();
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$replies = getTopicReplies($topicId, $page);
?>

<?php

?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Topic: <?php echo htmlspecialchars($topic['title']); ?> - Msasa Academy</title>

        <link
            href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Arimo:wght@400;500;600&display=swap"
            rel="stylesheet">

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
            line-height: 1.6;
        }

        .main-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem 4rem;
        }

        .topic-header {
            background: rgba(235, 229, 213, 0.05);
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .topic-header h1 {
            font-family: "DM Serif Display", serif;
            color: #FECE63;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .topic-meta {
            color: #EBE5D5;
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-left: 0.5rem;
        }

        .badge.teacher {
            background: rgba(254, 206, 99, 0.2);
            color: #FECE63;
            border: 1px solid rgba(254, 206, 99, 0.3);
        }

        .post-container {
            display: flex;
            margin-bottom: 1.5rem;
            background: rgba(235, 229, 213, 0.03);
            border-radius: 20px;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
            overflow: hidden;
        }

        .post-author {
            width: 200px;
            padding: 1.5rem;
            background: rgba(235, 229, 213, 0.02);
            border-right: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .author-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .author-info strong {
            color: #FECE63;
            font-size: 1.1rem;
        }

        .post-count {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .post-body {
            flex: 1;
            padding: 1.5rem;
            word-break: break-word;
        }

        .reply-form {
            background: rgba(235, 229, 213, 0.03);
            padding: 2rem;
            border-radius: 20px;
            margin-top: 2rem;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .reply-form h3 {
            font-family: "DM Serif Display", serif;
            color: #FECE63;
            margin-bottom: 1.5rem;
        }

        .reply-form textarea {
            width: 100%;
            padding: 1rem;
            background: rgba(235, 229, 213, 0.05);
            border: 1px solid rgba(235, 229, 213, 0.2);
            border-radius: 10px;
            color: #EBE5D5;
            font-family: "Arimo", sans-serif;
            font-size: 1rem;
            margin-bottom: 1rem;
            resize: vertical;
            min-height: 150px;
        }

        .reply-form textarea:focus {
            outline: none;
            border-color: #FECE63;
            box-shadow: 0 0 0 2px rgba(254, 206, 99, 0.2);
        }

        .button {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .button.primary {
            background: #FECE63;
            color: #3A4E3C;
        }

        .button.small {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .button:hover {
            transform: translateY(-2px);
        }

        .button.danger {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        .post-meta {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 0.5px solid rgba(235, 229, 213, 0.2);
            color: #EBE5D5;
            opacity: 0.8;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .post-actions {
            display: flex;
            gap: 0.8rem;
        }

        .login-prompt {
            text-align: center;
            padding: 3rem;
            background: rgba(235, 229, 213, 0.03);
            border-radius: 20px;
            margin-top: 2rem;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .login-prompt a {
            color: #FECE63;
            text-decoration: none;
        }

        .login-prompt a:hover {
            text-decoration: underline;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }

        .pagination .button {
            background: rgba(235, 229, 213, 0.05);
            color: #EBE5D5;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .inline {
            display: inline;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem 4rem;
            border-bottom: 0.5px solid rgba(235, 229, 213, 0.4);
        }

        .nav-container .logo {
            font-family: "DM Serif Display", serif;
            color: #FECE63;
            font-size: 1.5rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: #EBE5D5;
            text-decoration: none;
            padding: 0.5rem 1rem;
            transition: color 0.3s ease;
        }

        .nav-links a.active {
            color: #FECE63;
        }

        .logout-btn {
            color: #FECE63;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            border: 1px solid #FECE63;
            transition: all 0.3s;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .post-container {
                flex-direction: column;
            }

            .post-author {
                width: 100%;
                border-right: none;
                border-bottom: 0.5px solid rgba(235, 229, 213, 0.2);
                padding: 1rem;
            }

            .post-body {
                padding: 1rem;
            }

            .topic-header h1 {
                font-size: 2rem;
            }

            .post-meta {
                flex-direction: column;
                gap: 1rem;
            }

            .post-actions {
                justify-content: flex-start;
            }
        }
        </style>
    </head>

    <body>

        <nav class="nav-container">
            <div class="nav-links">
                <a href="index.php" class="active">Forum</a>
                <?php if($_SESSION['role'] == 'teacher'): ?>
                    <a href="../teacher/dashboard.php">Profle</a>
                <?php else: ?>
                    <a href="../student/dashboard.php">Profile</a>  
                <?php endif; ?>
                <a href="../../auth/logout.php" class="logout-btn">Logout</a>
            </div>
        </nav>

        <main class="main-container">
            <div class="topic-header">
                <h1><?php echo htmlspecialchars($topic['title']); ?></h1>
                <div class="topic-meta">
                    Posted by <?php echo htmlspecialchars($topic['username']); ?>
                    <?php if ($topic['role'] === 'teacher'): ?>
                    <span class="badge teacher">Teacher</span>
                    <?php endif; ?>
                    • <?php echo date('F j, Y', strtotime($topic['created_date'])); ?>
                </div>
            </div>

            <div class="topic-content">
                <div class="post-container">
                    <div class="post-author">
                        <div class="author-info">
                            <strong><?php echo htmlspecialchars($topic['username']); ?></strong>
                            <span class="post-count">Posts: <?php echo getUserPostCount($topic['user_id']); ?></span>
                        </div>
                    </div>
                    <div class="post-body">
                        <?php echo nl2br(htmlspecialchars($topic['content'])); ?>
                    </div>
                </div>

                <?php foreach ($replies as $reply): ?>
                <div class="post-container reply">
                    <div class="post-author">
                        <div class="author-info">
                            <strong><?php echo htmlspecialchars($reply['username']); ?></strong>
                            <?php if ($reply['role'] === 'teacher'): ?>
                            <span class="badge teacher">Teacher</span>
                            <?php endif; ?>
                            <span class="post-count">Posts: <?php echo getUserPostCount($reply['user_id']); ?></span>
                        </div>
                    </div>
                    <div class="post-body">
                        <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
                        <div class="post-meta">
                            Posted <?php echo date('F j, Y g:i A', strtotime($reply['created_date'])); ?>
                            <?php if (isset($_SESSION['user_id']) && 
                            ($_SESSION['user_id'] == $reply['user_id'] || 
                            $_SESSION['role'] === 'teacher')): ?>
                            <div class="post-actions">
                                <a href="edit_reply.php?id=<?php echo $reply['reply_id']; ?>"
                                    class="button small">Edit</a>
                                <form action="../../actions/forum/delete_reply.php" method="POST" class="inline">
                                    <input type="hidden" name="reply_id" value="<?php echo $reply['reply_id']; ?>">
                                    <button type="submit" class="button small danger"
                                        onclick="return confirm('Are you sure you want to delete this reply?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="reply-form">
                    <h3>Post a Reply</h3>
                    <form action="../../actions/forum/post_reply.php" method="POST">
                        <input type="hidden" name="topic_id" value="<?php echo $topicId; ?>">
                        <textarea name="content" rows="5" required placeholder="Write your reply..."></textarea>
                        <button type="submit" class="button primary">Post Reply</button>
                    </form>
                </div>
                <?php else: ?>
                <div class="login-prompt">
                    <p>Please <a href="../auth/login.php">login</a> to post a reply.</p>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($topic['reply_count'] > 10): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?id=<?php echo $topicId; ?>&page=<?php echo $page - 1; ?>" class="button">Previous</a>
                <?php endif; ?>

                <a href="?id=<?php echo $topicId; ?>&page=<?php echo $page + 1; ?>" class="button">Next</a>
            </div>
            <?php endif; ?>
        </main>

        <?php require_once '../components/footer.php'; ?>
    </body>

</html>
