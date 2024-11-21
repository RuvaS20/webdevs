<?php
// functions/forum_functions.php
function incrementViewCount($topicId) {
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        $stmt = $conn->prepare("
            UPDATE forum_topics 
            SET view_count = view_count + 1 
            WHERE topic_id = ?
        ");
        return $stmt->execute([$topicId]);
    } catch (PDOException $e) {
        error_log("Error incrementing view count: " . $e->getMessage());
        return false;
    }
}

function getTopicsByCategory($category, $page = 1, $perPage = 20) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $offset = ($page - 1) * $perPage;
    
    try {
        $stmt = $conn->prepare("
            SELECT t.*, u.username 
            FROM forum_topics t
            JOIN users u ON t.user_id = u.user_id
            WHERE t.category = ?
            ORDER BY t.is_pinned DESC, t.last_updated_date DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$category, $perPage, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
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