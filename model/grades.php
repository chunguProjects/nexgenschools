<?php

class Grades
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Insert a new grade record
    public function insertGrade($exam_id, $class_id, $subject_id, $student_id, $marks)
    {
        $query = "INSERT INTO grades (exam_id, class_id, subject_id, student_id, marks)
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiiis", $exam_id, $class_id, $subject_id, $student_id, $marks);
        return $stmt->execute();
    }

    // Update existing grade record
    public function updateGrade($exam_id, $class_id, $subject_id, $student_id, $marks)
    {
        $query = "UPDATE grades 
                  SET marks = ? 
                  WHERE exam_id = ? AND class_id = ? AND subject_id = ? AND student_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("siiii", $marks, $exam_id, $class_id, $subject_id, $student_id);
        return $stmt->execute();
    }

    // Check if a grade already exists
    public function getGrade($exam_id, $class_id, $subject_id, $student_id)
    {
        $query = "SELECT * FROM grades 
                  WHERE exam_id = ? AND class_id = ? AND subject_id = ? AND student_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiii", $exam_id, $class_id, $subject_id, $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    // Get grades by class, term, and exam
    public function getGradesByClassTermExam($class_id, $term, $exam_id)
    {
        $query = "
            SELECT g.student_id, g.subject_id, g.marks, c.name AS class_name
            FROM grades g
            INNER JOIN exams e ON g.exam_id = e.id
            INNER JOIN classes c ON g.class_id = c.class_id
            WHERE g.class_id = ? AND e.term = ? AND g.exam_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isi", $class_id, $term, $exam_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $grades = [];
        while ($row = $result->fetch_assoc()) {
            $row['grade'] = $this->calculateGrade($row['marks'], $row['class_name']);
            $grades[$row['student_id']][$row['subject_id']] = $row;
        }

        return $grades;
    }

    // Helper function: calculate letter grade from numeric mark and class level
    private function calculateGrade($marks, $class_name)
    {
        // Extract level from class name like "1A", "Form 2", etc.
        preg_match('/\d+/', $class_name, $matches);
        $class_level = isset($matches[0]) ? (int)$matches[0] : 1;

        // Determine grading level
        $grading_level = in_array($class_level, [1, 2]) ? 'Junior' : 'Senior';

        $query = "SELECT grade FROM grading_criteria 
                  WHERE level = ? AND ? BETWEEN min_marks AND max_marks LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $grading_level, $marks);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row['grade'];
        }

        return 'N/A';
    }

    // Get all grades for an exam and class in map format
    public function getGradesMap($exam_id, $class_id)
    {
        $query = "SELECT student_id, subject_id, marks 
                  FROM grades 
                  WHERE exam_id = ? AND class_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $exam_id, $class_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $gradesMap = [];
        while ($row = $result->fetch_assoc()) {
            $gradesMap[$row['student_id']][$row['subject_id']] = $row['marks'];
        }

        return $gradesMap;
    }
}
?>
