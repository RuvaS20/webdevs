<?php
// functions/forum_functions.php
function getUserPostCount($userId) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Count both topics and replies by the user
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
        
        // Bind the parameter for mysqli
        $stmt->bind_param("i", $topicId); // "i" for integer
        
        // Execute without parameters for mysqli
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
            JOIN users u ON t.user_id = u.user_id
            WHERE t.category = ?
            ORDER BY t.is_pinned DESC, t.last_updated_date DESC
            LIMIT ? OFFSET ?
        ");
        
        // Bind parameters for mysqli
        $stmt->bind_param("sii", $category, $perPage, $offset); // s for string, i for integer
        
        // Execute and get results
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch all results
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting topics: " . $e->getMessage());
        return [];
    }
}

function moderateContent($content) {
    // Basic moderation - filter out common inappropriate words
    $inappropriateWords = ['fuck', 'bitch', 'cunt', 'shit', 'nigga', 'nigger', 'ass', 'motherfucker']; // Add your list
    $filteredContent = str_ireplace($inappropriateWords, '***', $content);
    
    // Check for spam patterns
    $spamPatterns = [
        '/\b(?:https?:\/\/)?(?:www\.)?[a-z0-9-]+\.[a-z]{2,}(?:\/[^\s]*)?/i', // URLs
        '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i' // Email addresses
    ];
    
    $containsSpam = false;
    foreach ($spamPatterns as $pattern) {
        if (preg_match_all($pattern, $content) > 2) { // More than 2 URLs/emails
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