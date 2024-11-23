<?php
// view/forum/topic.php
require_once '../../db/database.php';
require_once '../../functions/forum_functions.php';
require_once '../../utils/session_helper.php';
require_once '../components/header.php';

$topicId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$topicId) {
    header('Location: index.php');
    exit();
}

// Increment view count
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
            JOIN users u ON t.user_id = u.user_id
            WHERE t.topic_id = ?
        ");
        
        // Bind parameter
        $stmt->bind_param("i", $topicId);
        
        // Execute and get results
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch the result
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
            JOIN users u ON r.user_id = u.user_id
            WHERE r.topic_id = ?
            ORDER BY r.created_date ASC
            LIMIT ? OFFSET ?
        ");
        
        // Bind parameters
        $stmt->bind_param("iii", $topicId, $perPage, $offset);
        
        // Execute and get results
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch all results
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
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Community Forum - Msasa Academy</title>
        
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Arimo:wght@400;500;600&display=swap" rel="stylesheet">
        <link href="../../assets/css/forum.css" rel="stylesheet">
</head>

<nav class="nav-container">
    <div class="nav-links">
        <a href="index.php" class="active">Forum</a>
        <a href="../student/dashboard.php">Profile</a>
        <a href="../../auth/logout.php">Logout</a>
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
                            <a href="edit_reply.php?id=<?php echo $reply['reply_id']; ?>" class="button small">Edit</a>
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