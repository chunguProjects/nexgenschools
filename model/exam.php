<?php
class Exams {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add new exam
    public function addExam($examName, $term, $academicYearId) {
        $query = "INSERT INTO exams (exam_name, term, academic_year_id) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            error_log("Prepare failed in addExam: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("ssi", $examName, $term, $academicYearId);
        return $stmt->execute();
    }

    // Get all exams
    public function getAllExams() {
        $query = "
            SELECT e.id, e.exam_name, e.term, e.academic_year_id, a.name AS academic_year_name, e.created_at
            FROM exams e
            LEFT JOIN academic_years a ON e.academic_year_id = a.academic_year_id
            ORDER BY e.created_at DESC
        ";
        $result = $this->conn->query($query);

        if (!$result) {
            error_log("Query failed in getAllExams: " . $this->conn->error);
            return [];
        }

        $exams = [];
        while ($row = $result->fetch_assoc()) {
            $exams[] = $row;
        }
        return $exams;
    }

    // Get exam by ID
    public function getExamById($id) {
        $query = "
            SELECT e.*, a.name AS academic_year_name
            FROM exams e
            LEFT JOIN academic_years a ON e.academic_year_id = a.academic_year_id
            WHERE e.id = ?
        ";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed in getExamById: " . $this->conn->error);
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    // Update exam details by ID
    public function updateExam($id, $examName, $term, $academicYearId) {
        $query = "UPDATE exams SET exam_name = ?, term = ?, academic_year_id = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed in updateExam: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("ssii", $examName, $term, $academicYearId, $id);
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

    // Get exams filtered by term or academic year
    public function getExams($term = null, $academicYearId = null) {
        $query = "
            SELECT e.id, e.exam_name, e.term, e.academic_year_id, a.name AS academic_year_name
            FROM exams e
            LEFT JOIN academic_years a ON e.academic_year_id = a.academic_year_id
            WHERE 1=1
        ";

        $params = [];
        $types = "";

        if ($term) {
            $query .= " AND e.term = ?";
            $types .= "s";
            $params[] = $term;
        }
        if ($academicYearId) {
            $query .= " AND e.academic_year_id = ?";
            $types .= "i";
            $params[] = $academicYearId;
        }

        $query .= " ORDER BY e.created_at DESC";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed in getExams: " . $this->conn->error);
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $exams = [];
        while ($row = $result->fetch_assoc()) {
            $exams[] = $row;
        }

        return $exams;
    }
}
?>
