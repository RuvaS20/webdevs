<?php
function getUserPostCount($userId) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("
            SELECT 
                (SELECT COUNT(*) FROM forum_topics WHERE user_id = ?) +
                (SELECT COUNT(*) FROM forum_replies WHERE user_id = ?) as total_posts
        ");
        
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total_posts'] ?? 0;
    } catch (Exception $e) {
        error_log("Error getting user post count: " . $e->getMessage());
        return 0;
    }
}

function incrementViewCount($topicId) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE forum_topics 
            SET view_count = view_count + 1 
            WHERE topic_id = ?
        ");
        $stmt->bind_param("i", $topicId); 
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error incrementing view count: " . $e->getMessage());
        return false;
    }
}

function getTopicsByCategory($category, $page = 1, $perPage = 20) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $offset = ($page - 1) * $perPage;
        
        $stmt = $conn->prepare("
            SELECT t.*, u.username 
            FROM forum_topics t
            JOIN msasa_users u ON t.user_id = u.user_id
            WHERE t.category = ?
            ORDER BY t.is_pinned DESC, t.last_updated_date DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("sii", $category, $perPage, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting topics: " . $e->getMessage());
        return [];
    }
}

function moderateContent($content) {
    $inappropriateWords = ['fuck', 'bitch', 'cunt', 'shit', 'nigga', 'nigger', 'ass', 'motherfucker']; 
    $filteredContent = str_ireplace($inappropriateWords, '***', $content);
    $spamPatterns = [
        '/\b(?:https?:\/\/)?(?:www\.)?[a-z0-9-]+\.[a-z]{2,}(?:\/[^\s]*)?/i',
        '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i' 
    ];
    
    $containsSpam = false;
    foreach ($spamPatterns as $pattern) {
        if (preg_match_all($pattern, $content) > 2) { 
            $containsSpam = true;
            break;
        }
    }
    
    return [
        'content' => $filteredContent,
        'isSpam' => $containsSpam
    ];
}
?>
