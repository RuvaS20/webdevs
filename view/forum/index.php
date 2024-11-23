<?php
// view/forum/index.php
require_once '../../db/database.php';
require_once '../../functions/forum_functions.php';
require_once '../../utils/session_helper.php';

// Get category filter if set
$currentCategory = isset($_GET['category']) ? $_GET['category'] : null;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;

class ForumManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Get topics based on category
    public function getLatestTopics($category = null, $limit = 10) {
        try {
            $conn = $this->db->getConnection();
            
            if ($category) {
                $query = "
                    SELECT 
                        t.topic_id,
                        t.title,
                        t.created_date,
                        t.view_count,
                        t.reply_count,
                        u.username,
                        t.category
                    FROM forum_topics t
                    JOIN users u ON t.user_id = u.user_id
                    WHERE t.category = ?
                    ORDER BY t.last_updated_date DESC 
                    LIMIT ?
                ";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param('si', $category, $limit);
            } else {
                $query = "
                    SELECT 
                        t.topic_id,
                        t.title,
                        t.created_date,
                        t.view_count,
                        t.reply_count,
                        u.username,
                        t.category
                    FROM forum_topics t
                    JOIN users u ON t.user_id = u.user_id
                    ORDER BY t.last_updated_date DESC 
                    LIMIT ?
                ";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $limit);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $topics = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $topics;
        } catch (Exception $e) {
            error_log("Error fetching topics: " . $e->getMessage());
            return [];
        }
    }

    // Get category statistics
    public function getCategoryStats() {
        try {
            $conn = $this->db->getConnection();
            $query = "
                SELECT 
                    category,
                    COUNT(*) as topic_count,
                    SUM(reply_count) as total_replies
                FROM forum_topics
                GROUP BY category
            ";
            
            $result = $conn->query($query);
            if ($result) {
                return $result->fetch_all(MYSQLI_ASSOC);
            }
            return [];
        } catch (Exception $e) {
            error_log("Error fetching category stats: " . $e->getMessage());
            return [];
        }
    }
}

// Initialize the forum manager
$forumManager = new ForumManager();

// Get the data using the manager
$topics = $forumManager->getLatestTopics($currentCategory);
$categoryStats = $forumManager->getCategoryStats();

// Helper function for time ago display
function timeAgo($timestamp) {
    $datetime = new DateTime($timestamp);
    $now = new DateTime();
    $interval = $now->diff($datetime);
    
    if ($interval->y > 0) return $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
    if ($interval->m > 0) return $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
    if ($interval->d > 0) return $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
    if ($interval->h > 0) return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
    if ($interval->i > 0) return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Community Forum - Msasa Academy</title>
        
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Arimo:wght@400;500;600&display=swap" rel="stylesheet">
        <link href="../../assets/css/forum.css" rel="stylesheet">
    </head>


    <body>
        <nav class="nav-container">
            <div class="nav-links">
                <a href="../../news.html">News</a>
                <a href="index.php" class="active">Forum</a>
                <a href="../student/available_quizzes.php">Quizzes</a>
                <a href="../student/dashboard.php">Profile</a>
                <a href="../../auth/logout.php">Logout</a>
            </div>
        </nav>

        <main class="main-container">
            <div class="page-header">
                <h1>Community Forum</h1>
                <p>Join the conversation about climate science and environmental action</p>
            </div>

            <div class="forum-controls">
                <a href="create_topic.php" class="button primary">Start New Topic</a>
            </div>

            <div class="forum-categories">
                <?php foreach ($categoryStats as $stat): ?>
                <div class="category-card <?php echo $currentCategory === $stat['category'] ? 'active' : ''; ?>">
                    <a href="?category=<?php echo urlencode($stat['category']); ?>">
                        <h3><?php echo htmlspecialchars(ucwords(str_replace('-', ' ', $stat['category']))); ?></h3>
                        <div class="category-stats">
                            <span><?php echo $stat['topic_count']; ?> topics</span>
                            <span><?php echo $stat['total_replies']; ?> replies</span>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="topic-list">
                <?php if (empty($topics)): ?>
                <div class="no-topics">
                    <p>No topics found in this category. Be the first to start a discussion!</p>
                </div>
                <?php else: ?>
                    <?php foreach ($topics as $topic): ?>
                    <div class="topic-item">
                        <div class="topic-info">
                            <h3><a href="topic.php?id=<?php echo $topic['topic_id']; ?>">
                                <?php echo htmlspecialchars($topic['title']); ?>
                            </a></h3>
                            <div class="topic-meta">
                                Posted by <?php echo htmlspecialchars($topic['username']); ?> • 
                                <?php echo timeAgo($topic['created_date']); ?>
                            </div>
                        </div>
                        <div class="topic-stats">
                            <div>
                                <?php echo $topic['reply_count']; ?> replies
                            </div>
                            <div>
                                <?php echo $topic['view_count']; ?> views
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (count($topics) >= $perPage): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?><?php echo $currentCategory ? '&category=' . urlencode($currentCategory) : ''; ?>" class="button">Previous</a>
                <?php endif; ?>
                
                <a href="?page=<?php echo $page + 1; ?><?php echo $currentCategory ? '&category=' . urlencode($currentCategory) : ''; ?>" class="button">Next</a>
            </div>
            <?php endif; ?>
        </main>

        <?php require_once '../components/footer.php'; ?>
    </body>
</html>