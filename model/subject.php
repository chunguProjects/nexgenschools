<?php
class Subjects {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add subject for a specific school
    public function addSubject($name, $code, $teacherId, $schoolId) {
        $query = "INSERT INTO subjects (subject_name, subject_code, teacher_id, school_id)
                  VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssii", $name, $code, $teacherId, $schoolId);

        try {
            if ($stmt->execute()) {
                $subjectId = $this->conn->insert_id;

                // Assign to all existing classes of this school
                $classQuery = "SELECT class_id FROM classes WHERE school_id = ?";
                $classStmt = $this->conn->prepare($classQuery);
                $classStmt->bind_param("i", $schoolId);
                $classStmt->execute();
                $classResult = $classStmt->get_result();

                while ($class = $classResult->fetch_assoc()) {
                    $linkQuery = "INSERT IGNORE INTO class_subjects (class_id, subject_id) VALUES (?, ?)";
                    $linkStmt = $this->conn->prepare($linkQuery);
                    $linkStmt->bind_param("ii", $class['class_id'], $subjectId);
                    $linkStmt->execute();
                }

                return true;
            }
            return false;
        } catch (mysqli_sql_exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return false;
            }
            throw $e;
        }
    }

    // Fetch all subjects for a given school
    public function getAllSubjects($schoolId) {
        $query = "
            SELECT 
                s.subject_id,
                s.subject_name,
                s.subject_code,
                s.created_at,
                COALESCE(u.full_name, 'Unassigned') AS teacher_name
            FROM subjects s
            LEFT JOIN users u ON s.teacher_id = u.user_id
            WHERE s.school_id = ? 
            ORDER BY s.created_at DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $schoolId);
        $stmt->execute();
        $result = $stmt->get_result();

        $subjects = [];
        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row;
        }
        return $subjects;
    }

    // Get single subject by ID
   public function getSubjectById($subjectId) {
    $query = "SELECT * FROM subjects WHERE subject_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $subjectId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

public function updateSubject($subjectId, $name, $code, $teacherId) {
    $query = "UPDATE subjects SET subject_name = ?, subject_code = ?, teacher_id = ? WHERE subject_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ssii", $name, $code, $teacherId, $subjectId);
    return $stmt->execute();
}


    // Soft delete subject
    public function deleteSubject(int $subjectId, int $schoolId): bool {
    // Begin transaction to ensure atomic operation
    $this->conn->begin_transaction();

    try {
        // First, verify the subject belongs to the school (optional but good for security)
        $checkQuery = "SELECT 1 FROM subjects WHERE subject_id = ? AND school_id = ?";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bind_param("ii", $subjectId, $schoolId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows === 0) {
            // Subject does not belong to this school, cannot delete
            $this->conn->rollback();
            return false;
        }

        // Delete from class_subjects only for classes of this school
        $deleteMappingsQuery = "
            DELETE cs FROM class_subjects cs
            INNER JOIN classes c ON cs.class_id = c.class_id
            WHERE cs.subject_id = ? AND c.school_id = ?
        ";
        $stmtMappings = $this->conn->prepare($deleteMappingsQuery);
        $stmtMappings->bind_param("ii", $subjectId, $schoolId);
        $stmtMappings->execute();

        // Now delete the subject itself (only if it belongs to this school)
        $deleteSubjectQuery = "DELETE FROM subjects WHERE subject_id = ? AND school_id = ?";
        $stmtSubject = $this->conn->prepare($deleteSubjectQuery);
        $stmtSubject->bind_param("ii", $subjectId, $schoolId);
        $stmtSubject->execute();

        $this->conn->commit();

        return true;

    } catch (mysqli_sql_exception $e) {
        $this->conn->rollback();
        return false;
    }
}




    // Assign subject to a specific class
    public function assignSubjectToClass($classId, $subjectId) {
        $query = "INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $classId, $subjectId);
        return $stmt->execute();
    }

    // Remove subject from a specific class
    public function removeSubjectFromClass($classId, $subjectId) {
        $query = "DELETE FROM class_subjects WHERE class_id = ? AND subject_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $classId, $subjectId);
        return $stmt->execute();
    }

    // Get subjects assigned to a specific class
    public function getSubjectsByClass($classId) {
        $query = "
            SELECT 
                s.subject_id,
                s.subject_name,
                s.subject_code,
                COALESCE(u.full_name, 'Unassigned') AS teacher_name
            FROM class_subjects cs
            INNER JOIN subjects s ON cs.subject_id = s.subject_id
            LEFT JOIN users u ON s.teacher_id = u.user_id
            WHERE cs.class_id = ?
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $classId);
        $stmt->execute();
        $result = $stmt->get_result();

        $subjects = [];
        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row;
        }
        return $subjects;
    }

    // Check if subject is already assigned to class
    public function isSubjectAssignedToClass($classId, $subjectId) {
        $query = "SELECT 1 FROM class_subjects WHERE class_id = ? AND subject_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $classId, $subjectId);
        $stmt->execute();
        $result = $stmt->get_result();
        return ($result->num_rows > 0);
    }
    
}

?>
