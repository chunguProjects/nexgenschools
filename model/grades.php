<?php

class Grades
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function insertGrade($exam_id, $class_id, $subject_id, $student_id, $marks)
    {
        $query = "INSERT INTO grades (exam_id, class_id, subject_id, student_id, marks) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiiss", $exam_id, $class_id, $subject_id, $student_id, $marks);
        return $stmt->execute();
    }

    public function getGradesByClassTerm($class_id, $term)
    {
        $query = "
            SELECT g.student_id, g.subject_id, g.marks, c.name AS class_name
            FROM grades g
            INNER JOIN exams e ON g.exam_id = e.id
            INNER JOIN classes c ON g.class_id = c.class_id
            WHERE g.class_id = ? AND e.term = ?
        ";
        $stmt = $this->conn->prepare($query);
    
        if (!$stmt) {
            die("SQL Error: " . $this->conn->error);
        }
    
        $stmt->bind_param("is", $class_id, $term);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if (!$result) {
            die("Execution Error: " . $this->conn->error);
        }
    
        $grades = [];
        while ($row = $result->fetch_assoc()) {
            $row['grade'] = $this->calculateGrade($row['marks'], $row['class_name']);
            $grades[$row['student_id']][$row['subject_id']] = $row;
        }
    
        return $grades;
    }

    private function calculateGrade($marks, $class_name)
    {
        // Extract the first character of the class name to determine the level
        $class_level = intval(substr($class_name, 0, 1));

        // Determine grading system based on class level
        $grading_level = in_array($class_level, [1, 2]) ? 'Junior' : 'Senior';

        // Fetch grading criteria from the database
        $query = "SELECT grade, description FROM grading_criteria WHERE level = ? AND ? BETWEEN min_marks AND max_marks";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $grading_level, $marks);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row['grade']; // Return only the grade
        }

        // Default if no match is found
        return 'N/A';
    }
        public function getGradesByStudentTerm($student_id, $term)
    {
        $query = "
            SELECT g.subject_id, g.marks, c.name AS class_name
            FROM grades g
            INNER JOIN exams e ON g.exam_id = e.id
            INNER JOIN classes c ON g.class_id = c.class_id
            WHERE g.student_id = ? AND e.term = ?
        ";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("SQL Error: " . $this->conn->error);
        }
        $stmt->bind_param("is", $student_id, $term);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result) {
            die("Execution Error: " . $this->conn->error);
        }

        $grades = [];
        while ($row = $result->fetch_assoc()) {
            $row['grade'] = $this->calculateGrade($row['marks'], $row['class_name']);
            $grades[] = $row;
        }

        return $grades;
    }


}
?>
