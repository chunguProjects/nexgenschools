<?php
class Exams {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add new exam for a specific school (multi-tenancy)
    public function addExam($examName, $term, $academicYear, $schoolId) {
        $query = "INSERT INTO exams (exam_name, term, academic_year, school_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param("sssi", $examName, $term, $academicYear, $schoolId);
            return $stmt->execute();
        } else {
            error_log("Prepare failed in addExam: " . $this->conn->error);
            return false;
        }
    }

    // Get all exams, optionally filtered by school_id for multi-tenancy
    public function getAllExams($schoolId = null) {
        if ($schoolId !== null) {
            $query = "SELECT id, exam_name, term, academic_year, created_at FROM exams WHERE school_id = ? ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Prepare failed in getAllExams: " . $this->conn->error);
                return [];
            }
            $stmt->bind_param("i", $schoolId);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $query = "SELECT id, exam_name, term, academic_year, created_at FROM exams ORDER BY created_at DESC";
            $result = $this->conn->query($query);
            if (!$result) {
                error_log("Query failed in getAllExams: " . $this->conn->error);
                return [];
            }
        }

        $exams = [];
        while ($row = $result->fetch_assoc()) {
            $exams[] = $row;
        }
        return $exams;
    }

    // Get exam by ID
    public function getExamById($id) {
        $query = "SELECT * FROM exams WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed in getExamById: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    // Update exam details by ID
    public function updateExam($id, $examName, $term, $academicYear) {
        $query = "UPDATE exams SET exam_name = ?, term = ?, academic_year = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed in updateExam: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("sssi", $examName, $term, $academicYear, $id);
        return $stmt->execute();
    }

    // Delete exam by ID
    public function deleteExam($id) {
        $query = "DELETE FROM exams WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed in deleteExam: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Optional: Get exam by term (not filtered by school; consider adding school filter if needed)
    public function getExamByTerm($term) {
        $query = "SELECT * FROM exams WHERE term = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed in getExamByTerm: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("s", $term);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    // Get all exams by school and optional term
    public function getExamsBySchoolAndTerm($schoolId, $term = null) {
        if ($term) {
            $query = "SELECT id, exam_name FROM exams WHERE school_id = ? AND term = ?";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Prepare failed in getExamsBySchoolAndTerm: " . $this->conn->error);
                return [];
            }
            $stmt->bind_param("is", $schoolId, $term);
        } else {
            $query = "SELECT id, exam_name FROM exams WHERE school_id = ?";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("Prepare failed in getExamsBySchoolAndTerm: " . $this->conn->error);
                return [];
            }
            $stmt->bind_param("i", $schoolId);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $exams = [];
        while ($row = $result->fetch_assoc()) {
            $exams[] = $row;
        }

        return $exams;
    }
    public function getAcademicYearByClassAndTerm($classId, $term) {
    $query = "
        SELECT DISTINCT e.academic_year
        FROM exams e
        JOIN subjects s ON s.school_id = e.school_id
        JOIN classes c ON c.school_id = e.school_id
        WHERE c.class_id = ? AND e.term = ?
        ORDER BY e.id DESC
        LIMIT 1
    ";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("is", $classId, $term);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['academic_year'] : null;
}

}
?>
