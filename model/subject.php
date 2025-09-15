<?php
class Subjects {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add subject
    public function addSubject($name, $code, $teacherId) {
        $query = "INSERT INTO subjects (subject_name, subject_code, teacher_id)
                  VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssi", $name, $code, $teacherId);

        try {
            if ($stmt->execute()) {
                $subjectId = $this->conn->insert_id;

                // Assign to all classes
                $classQuery = "SELECT class_id FROM classes";
                $classResult = $this->conn->query($classQuery);

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

    // Fetch all subjects
    public function getAllSubjects() {
        $query = "
            SELECT 
                s.subject_id,
                s.subject_name,
                s.subject_code,
                s.created_at,
                COALESCE(u.full_name, 'Unassigned') AS teacher_name
            FROM subjects s
            LEFT JOIN users u ON s.teacher_id = u.user_id
            ORDER BY s.created_at DESC
        ";
        $result = $this->conn->query($query);

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

    // Update subject
    public function updateSubject($subjectId, $name, $code, $teacherId) {
        $query = "UPDATE subjects SET subject_name = ?, subject_code = ?, teacher_id = ? WHERE subject_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssii", $name, $code, $teacherId, $subjectId);
        return $stmt->execute();
    }

    // Delete subject (and its mappings)
    public function deleteSubject(int $subjectId): bool {
        $this->conn->begin_transaction();

        try {
            // Delete mappings
            $deleteMappingsQuery = "DELETE FROM class_subjects WHERE subject_id = ?";
            $stmtMappings = $this->conn->prepare($deleteMappingsQuery);
            $stmtMappings->bind_param("i", $subjectId);
            $stmtMappings->execute();

            // Delete subject
            $deleteSubjectQuery = "DELETE FROM subjects WHERE subject_id = ?";
            $stmtSubject = $this->conn->prepare($deleteSubjectQuery);
            $stmtSubject->bind_param("i", $subjectId);
            $stmtSubject->execute();

            $this->conn->commit();
            return true;

        } catch (mysqli_sql_exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    // Assign subject to a class
    public function assignSubjectToClass($classId, $subjectId) {
        $query = "INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $classId, $subjectId);
        return $stmt->execute();
    }

    // Remove subject from class
    public function removeSubjectFromClass($classId, $subjectId) {
        $query = "DELETE FROM class_subjects WHERE class_id = ? AND subject_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $classId, $subjectId);
        return $stmt->execute();
    }

    // Get subjects by class
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

    // Check if subject is already assigned to a class
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
