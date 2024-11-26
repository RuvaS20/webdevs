<?php
session_start();
require_once '../../db/database.php';
require_once '../../utils/session_helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$replyId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$replyId) {
    header('Location: index.php');
    exit();
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT r.*, t.title as topic_title, t.topic_id
        FROM forum_replies r
        JOIN forum_topics t ON r.topic_id = t.topic_id
        WHERE r.reply_id = ?
    ");
    
    $stmt->bind_param("i", $replyId);
    $stmt->execute();
    $result = $stmt->get_result();
    $reply = $result->fetch_assoc();

    if (!$reply || ($reply['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'teacher')) {
        header('Location: index.php');
        exit();
    }
} catch (Exception $e) {
    error_log("Error fetching reply: " . $e->getMessage());
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Reply - Msasa Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Arimo:wght@400;500;600&display=swap" rel="stylesheet">
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
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
        }

        .edit-form {
            background: rgba(235, 229, 213, 0.03);
            padding: 2rem;
            border-radius: 20px;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .edit-form h1 {
            font-family: "DM Serif Display", serif;
            color: #FECE63;
            margin-bottom: 1rem;
        }

        .topic-title {
            color: #EBE5D5;
            opacity: 0.8;
            margin-bottom: 2rem;
        }

        .topic-title a {
            color: #FECE63;
            text-decoration: none;
        }

        .topic-title a:hover {
            text-decoration: underline;
        }

        textarea {
            width: 100%;
            min-height: 200px;
            padding: 1rem;
            background: rgba(235, 229, 213, 0.05);
            border: 1px solid rgba(235, 229, 213, 0.2);
            border-radius: 10px;
            color: #EBE5D5;
            font-family: "Arimo", sans-serif;
            font-size: 1rem;
            margin-bottom: 1rem;
            resize: vertical;
        }

        textarea:focus {
            outline: none;
            border-color: #FECE63;
            box-shadow: 0 0 0 2px rgba(254, 206, 99, 0.2);
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
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
            font-size: 1rem;
        }

        .button.primary {
            background: #FECE63;
            color: #3A4E3C;
        }

        .button.secondary {
            background: rgba(235, 229, 213, 0.05);
            color: #EBE5D5;
            border: 0.5px solid rgba(235, 229, 213, 0.2);
        }

        .button:hover {
            transform: translateY(-2px);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem 4rem;
            border-bottom: 0.5px solid rgba(235, 229, 213, 0.4);
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

            .nav-container {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }

            .button-group {
                flex-direction: column;
            }

            .button {
                width: 100%;
                text-align: center;
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
                <a href="../student/dashboard.php">Profile</a><   
            <?php endif; ?>
            <a href="../../auth/logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <main class="main-container">
        <div class="edit-form">
            <h1>Edit Reply</h1>
            <p class="topic-title">
                In topic: <a href="topic.php?id=<?php echo $reply['topic_id']; ?>"><?php echo htmlspecialchars($reply['topic_title']); ?></a>
            </p>

            <form action="../../actions/forum/edit_reply.php" method="POST">
                <input type="hidden" name="reply_id" value="<?php echo $replyId; ?>">
                <textarea name="content" required><?php echo htmlspecialchars($reply['content']); ?></textarea>
                
                <div class="button-group">
                    <button type="submit" class="button primary">Save Changes</button>
                    <a href="topic.php?id=<?php echo $reply['topic_id']; ?>" class="button secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
