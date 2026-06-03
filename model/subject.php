<?php
class Subjects {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add subject
    public function addSubject($name, $code) {
        $query = "INSERT INTO subjects (subject_name, subject_code)
                  VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $name, $code);

        return $stmt->execute();
    }

    // Get all subjects
    public function getAllSubjects() {
        $query = "SELECT subject_id, subject_name, subject_code, created_at
                  FROM subjects
                  ORDER BY created_at DESC";

        $result = $this->conn->query($query);

        $subjects = [];
        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row;
        }

        return $subjects;
    }

    // Get single subject
    public function getSubjectById($subjectId) {
        $query = "SELECT * FROM subjects WHERE subject_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $subjectId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    // Update subject
    public function updateSubject($subjectId, $name, $code) {
        $query = "UPDATE subjects 
                  SET subject_name = ?, subject_code = ?
                  WHERE subject_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssi", $name, $code, $subjectId);

        return $stmt->execute();
    }

    // Delete subject
    public function deleteSubject($subjectId) {
        $query = "DELETE FROM subjects WHERE subject_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $subjectId);

        return $stmt->execute();
    }
    // Get subjects assigned to a class
public function getSubjectsByClass($classId, $academicYearId)
{
    $query = "SELECT 
                s.subject_id, 
                s.subject_name, 
                s.subject_code,
                cs.id AS class_subject_id,
                COALESCE(u.full_name, 'N/A') AS teacher_name
              FROM class_subject cs
              INNER JOIN subjects s 
                    ON s.subject_id = cs.subject_id
              LEFT JOIN class_subject_teacher cst
                    ON cst.class_subject_id = cs.id
              LEFT JOIN users u
                    ON u.user_id = cst.teacher_id
              WHERE cs.class_id = ?
              AND cs.academic_year_id = ?
              ORDER BY s.subject_name ASC";

    $stmt = $this->conn->prepare($query);

    if (!$stmt) {
        die("Prepare failed: " . $this->conn->error);
    }

    $stmt->bind_param("ii", $classId, $academicYearId);

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();

    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }

    $stmt->close();

    return $subjects;
}

}
