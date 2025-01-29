<?php
class Exams {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function addExam($examName, $term, $academicYear) {
        $query = "INSERT INTO exams (exam_name, term, academic_year) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param("sss", $examName, $term, $academicYear);
            return $stmt->execute();
        } else {
            return false; // Return false if the statement couldn't be prepared
        }
    }

    public function getAllExams() {
        $query = "SELECT id, exam_name, term, academic_year, created_at FROM exams ORDER BY created_at DESC";

        $result = $this->conn->query($query);

        if ($result) {
            $exams = [];
            while ($row = $result->fetch_assoc()) {
                $exams[] = $row;
            }
            return $exams;
        } else {
            error_log("Error retrieving exams: " . $this->conn->error);
            return [];
        }
    }

    public function getExamByTerm($term)
    {
        $query = "SELECT * FROM exams WHERE term = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $term);
        $stmt->execute();
        $result = $stmt->get_result();
    
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }
    
    
}
?>
