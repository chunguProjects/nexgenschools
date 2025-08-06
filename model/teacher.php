<?php
class TeacherModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ✅ 1. Get all classes assigned to the teacher via class_subjects + subjects
    public function getClassesByTeacher($teacherId) {
        $query = "
            SELECT DISTINCT c.class_id, c.name AS class_name, c.description, c.created_at
            FROM classes c
            INNER JOIN class_subjects cs ON cs.class_id = c.class_id
            INNER JOIN subjects s ON cs.subject_id = s.subject_id
            WHERE s.teacher_id = ?
            ORDER BY c.created_at DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ 2. Get subjects assigned to the teacher (with class info)
    public function getSubjectsByTeacher($teacherId) {
        $query = "
            SELECT s.subject_id, s.subject_name, c.class_id, c.name AS class_name
            FROM subjects s
            INNER JOIN class_subjects cs ON s.subject_id = cs.subject_id
            INNER JOIN classes c ON cs.class_id = c.class_id
            WHERE s.teacher_id = ?
            ORDER BY c.name, s.subject_name
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ 3. Get students in a subject (based on class of subject)
    public function getStudentsInSubject($subjectId) {
        $query = "
            SELECT stu.student_id, CONCAT(stu.first_name, ' ', stu.last_name) AS full_name
            FROM students stu
            INNER JOIN classes c ON stu.class_id = c.class_id
            INNER JOIN class_subjects cs ON cs.class_id = c.class_id
            WHERE cs.subject_id = ? AND stu.is_deleted = 0
            ORDER BY stu.first_name, stu.last_name
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$subjectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ 4. Get the class ID for a given subject (useful for saving grades)
    public function getClassBySubject($subjectId) {
        $query = "
            SELECT c.class_id, c.name AS class_name
            FROM classes c
            INNER JOIN class_subjects cs ON cs.class_id = c.class_id
            WHERE cs.subject_id = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$subjectId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
