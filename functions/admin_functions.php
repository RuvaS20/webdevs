<?php
class AdminFunctions {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllUsers($page = 1, $limit = 10, $role = null) {
        try {
            $offset = ($page - 1) * $limit;
            $params = [];
            $query = "SELECT * FROM msasa_users";
            
            if ($role) {
                $query .= " WHERE role = ?";
                $params[] = $role;
            }
            
            $query .= " ORDER BY registration_date DESC LIMIT ? OFFSET ?";
            $stmt = $this->pdo->prepare($query);
            $paramNumber = 1;
            if ($role) {
                $stmt->bindValue($paramNumber++, $role, PDO::PARAM_STR);
            }
            $stmt->bindValue($paramNumber++, $limit, PDO::PARAM_INT);
            $stmt->bindValue($paramNumber++, $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching users: " . $e->getMessage());
            return false;
        }
    }

    public function updateUser($userId, $data) {
        try {
            $query = "UPDATE msasa_users SET 
                     username = ?, 
                     email = ?, 
                     role = ?,
                     is_active = ?
                     WHERE user_id = ?";
            
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([
                $data['username'],
                $data['email'],
                $data['role'],
                $data['is_active'],
                $userId
            ]);
        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }

    public function deleteUser($userId) {
        try {
            error_log("Starting deletion process for user ID: " . $userId);
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("
                DELETE qr FROM quiz_responses qr 
                INNER JOIN quiz_attempts qa ON qr.attempt_id = qa.attempt_id 
                WHERE qa.student_id = ?
            ");
            $stmt->execute([$userId]);
            error_log("Deleted quiz responses");
            $stmt = $this->pdo->prepare("DELETE FROM quiz_attempts WHERE student_id = ?");
            $stmt->execute([$userId]);
            error_log("Deleted quiz attempts");
            $stmt = $this->pdo->prepare("DELETE FROM forum_replies WHERE user_id = ?");
            $stmt->execute([$userId]);
            error_log("Deleted forum replies");
            $stmt = $this->pdo->prepare("DELETE FROM forum_topics WHERE user_id = ?");
            $stmt->execute([$userId]);
            error_log("Deleted forum topics");
            $stmt = $this->pdo->prepare("SELECT quiz_id FROM quizzes WHERE teacher_id = ?");
            $stmt->execute([$userId]);
            $quizzes = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($quizzes as $quizId) {
                $stmt = $this->pdo->prepare("
                    DELETE qr FROM quiz_responses qr 
                    INNER JOIN quiz_attempts qa ON qr.attempt_id = qa.attempt_id 
                    WHERE qa.quiz_id = ?
                ");
                $stmt->execute([$quizId]);
                $stmt = $this->pdo->prepare("DELETE FROM quiz_attempts WHERE quiz_id = ?");
                $stmt->execute([$quizId]);
                $stmt = $this->pdo->prepare("DELETE FROM quiz_questions WHERE quiz_id = ?");
                $stmt->execute([$quizId]);
            }
            $stmt = $this->pdo->prepare("DELETE FROM quizzes WHERE teacher_id = ?");
            $stmt->execute([$userId]);
            error_log("Deleted quizzes and related data");
            $stmt = $this->pdo->prepare("DELETE FROM msasa_users WHERE user_id = ?");
            $stmt->execute([$userId]);
            error_log("Deleted user");
            
            $this->pdo->commit();
            error_log("Transaction committed successfully");
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error in deleteUser method: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            return false;
        }
    }

    public function getAllQuizzes($page = 1, $limit = 10, $difficulty = null) {
        try {
            $offset = ($page - 1) * $limit;
            $params = [];
            
            $query = "SELECT 
                        q.*,
                        u.username as teacher_name,
                        COUNT(DISTINCT qq.question_id) as question_count,
                        COUNT(DISTINCT qa.attempt_id) as attempt_count,
                        COALESCE(AVG(qa.total_score), 0) as average_score
                     FROM quizzes q
                     LEFT JOIN msasa_users u ON q.teacher_id = u.user_id
                     LEFT JOIN quiz_questions qq ON q.quiz_id = qq.quiz_id
                     LEFT JOIN quiz_attempts qa ON q.quiz_id = qa.quiz_id";
            
            if ($difficulty) {
                $query .= " WHERE q.difficulty_level = ?";
                $params[] = $difficulty;
            }
            
            $query .= " GROUP BY q.quiz_id
                       ORDER BY q.created_date DESC
                       LIMIT ? OFFSET ?";
    
            $stmt = $this->pdo->prepare($query);
            
    
            $paramNumber = 1;
            if ($difficulty) {
                $stmt->bindValue($paramNumber++, $difficulty, PDO::PARAM_STR);
            }
            $stmt->bindValue($paramNumber++, $limit, PDO::PARAM_INT);
            $stmt->bindValue($paramNumber++, $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching quizzes: " . $e->getMessage());
            return false;
        }
    }
    
    public function getQuizzesCount($difficulty = null) {
        try {
            $query = "SELECT COUNT(*) FROM quizzes";
            if ($difficulty) {
                $query .= " WHERE difficulty_level = ?";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute([$difficulty]);
            } else {
                $stmt = $this->pdo->query($query);
            }
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting quizzes count: " . $e->getMessage());
            return 0;
        }
    }

    public function updateQuizStatus($quizId, $isActive) {
        try {
            $stmt = $this->pdo->prepare("UPDATE quizzes SET is_active = ? WHERE quiz_id = ?");
            return $stmt->execute([$isActive, $quizId]);
        } catch (PDOException $e) {
            error_log("Error updating quiz status: " . $e->getMessage());
            return false;
        }
    }

    public function deleteQuiz($quizId) {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("DELETE qr FROM quiz_responses qr 
                                       JOIN quiz_attempts qa ON qr.attempt_id = qa.attempt_id 
                                       WHERE qa.quiz_id = ?");
            $stmt->execute([$quizId]);
            $stmt = $this->pdo->prepare("DELETE FROM quiz_attempts WHERE quiz_id = ?");
            $stmt->execute([$quizId]);
            $stmt = $this->pdo->prepare("DELETE FROM quiz_questions WHERE quiz_id = ?");
            $stmt->execute([$quizId]);
            $stmt = $this->pdo->prepare("DELETE FROM quizzes WHERE quiz_id = ?");
            $stmt->execute([$quizId]);
            
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error deleting quiz: " . $e->getMessage());
            return false;
        }
    }

    public function getAllTopics($page = 1, $limit = 10, $category = null) {
        try {
            $offset = ($page - 1) * $limit;
            $params = [];
            
            $query = "SELECT 
                        t.*,
                        u.username,
                        COUNT(DISTINCT r.reply_id) as total_replies
                     FROM forum_topics t
                     JOIN msasa_users u ON t.user_id = u.user_id
                     LEFT JOIN forum_replies r ON t.topic_id = r.topic_id";
            
            if ($category) {
                $query .= " WHERE t.category = ?";
                $params[] = $category;
            }
            
            $query .= " GROUP BY t.topic_id
                       ORDER BY t.last_updated_date DESC
                       LIMIT ? OFFSET ?";
    
            $stmt = $this->pdo->prepare($query);
            $paramNumber = 1;
            if ($category) {
                $stmt->bindValue($paramNumber++, $category, PDO::PARAM_STR);
            }
            $stmt->bindValue($paramNumber++, $limit, PDO::PARAM_INT);
            $stmt->bindValue($paramNumber++, $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching topics: " . $e->getMessage());
            return false;
        }
    }

    public function getTopicsCount($category = null) {
        try {
            $query = "SELECT COUNT(*) FROM forum_topics";
            if ($category) {
                $query .= " WHERE category = ?";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute([$category]);
            } else {
                $stmt = $this->pdo->query($query);
            }
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting topics count: " . $e->getMessage());
            return 0;
        }
    }

    public function deleteTopic($topicId) {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("DELETE FROM forum_replies WHERE topic_id = ?");
            $stmt->execute([$topicId]);
            $stmt = $this->pdo->prepare("DELETE FROM forum_topics WHERE topic_id = ?");
            $stmt->execute([$topicId]);
            
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error deleting topic: " . $e->getMessage());
            return false;
        }
    }

    public function getSystemStats() {
        try {
            $stats = [];
            $stmt = $this->pdo->query("SELECT role, COUNT(*) as count 
                                     FROM msasa_users 
                                     GROUP BY role");
            $stats['users'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $stmt = $this->pdo->query("SELECT COUNT(*) as total_quizzes, 
                                     SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_quizzes 
                                     FROM quizzes");
            $stats['quizzes'] = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = $this->pdo->query("SELECT 
                                     (SELECT COUNT(*) FROM forum_topics) as total_topics,
                                     (SELECT COUNT(*) FROM forum_replies) as total_replies");
            $stats['forum'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting system stats: " . $e->getMessage());
            return false;
        }
    }
}
?>
