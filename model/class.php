<?php
class ClassGroup {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ✅ Add new class AND assign all subjects from the school to this class
    public function addClass($name, $description, $teacherId, $schoolId) {
        // Check for duplicate class name in same school
        $checkQuery = "SELECT 1 FROM classes WHERE name = ? AND school_id = ?";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bind_param("si", $name, $schoolId);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            return false; // Duplicate class name in this school
        }

        // Insert class
        $query = "INSERT INTO classes (name, description, teacher_id, school_id)
                  VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssii", $name, $description, $teacherId, $schoolId);

        if ($stmt->execute()) {
            $classId = $this->conn->insert_id;

            // Assign all subjects from the school to the new class
            $subjectQuery = "SELECT subject_id FROM subjects WHERE school_id = ?";
            $subjectStmt = $this->conn->prepare($subjectQuery);
            $subjectStmt->bind_param("i", $schoolId);
            $subjectStmt->execute();
            $subjectResult = $subjectStmt->get_result();

            $insertClassSubject = $this->conn->prepare(
                "INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)"
            );

            while ($row = $subjectResult->fetch_assoc()) {
                $subjectId = $row['subject_id'];
                $insertClassSubject->bind_param("ii", $classId, $subjectId);
                $insertClassSubject->execute();
            }

            return true;
        }

        return false;
    }

    public function getAllClasses() {
        $query = "
            SELECT 
                c.class_id,
                c.name AS class_name,
                c.description,
                c.created_at,
                u.user_id AS teacher_id,
                u.full_name AS teacher_name,
                c.school_id
            FROM classes c
            INNER JOIN users u ON c.teacher_id = u.user_id
            ORDER BY c.created_at DESC
        ";
        $result = $this->conn->query($query);

        $classes = [];
        while ($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }
        return $classes;
    }

   public function getAllClassesBySchool($schoolId) {
    $query = "
        SELECT 
            c.class_id,
            c.name AS class_name,
            c.description,
            c.created_at,
            u.user_id AS teacher_id,
            u.full_name AS teacher_name
        FROM classes c
        LEFT JOIN users u ON c.teacher_id = u.user_id
        WHERE c.school_id = ?
        ORDER BY c.created_at DESC
    ";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $schoolId);
    $stmt->execute();
    $result = $stmt->get_result();

    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    return $classes;
}

    public function getClassById($class_id) {
        $query = "SELECT * FROM classes WHERE class_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    public function countStudentsInClass($classId) {
        $query = "SELECT COUNT(*) AS total FROM students WHERE class_id = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $classId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'] ?? 0;
    }

    public function updateClass($classId, $name, $description, $teacherId, $schoolId) {
    $query = "UPDATE classes 
              SET name = ?, description = ?, teacher_id = ? 
              WHERE class_id = ? AND school_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ssiii", $name, $description, $teacherId, $classId, $schoolId);
    return $stmt->execute();
}
    public function deleteClass($classId, $schoolId) {
    $this->conn->begin_transaction();
    try {
        //Delete from class_subjects
        $stmt1 = $this->conn->prepare("DELETE FROM class_subjects WHERE class_id = ?");
        $stmt1->bind_param("i", $classId);
        $stmt1->execute();

        //Get students in the class
        $studentQuery = "SELECT student_id FROM students WHERE class_id = ?";
        $stmt2 = $this->conn->prepare($studentQuery);
        $stmt2->bind_param("i", $classId);
        $stmt2->execute();
        $studentsResult = $stmt2->get_result();

        $studentIds = [];
        while ($row = $studentsResult->fetch_assoc()) {
            $studentIds[] = $row['student_id'];
        }

        //Delete student grades
        if (!empty($studentIds)) {
            $in = str_repeat('?,', count($studentIds) - 1) . '?';
            $gradeQuery = "DELETE FROM grades WHERE student_id IN ($in)";
            $stmt3 = $this->conn->prepare($gradeQuery);
            $stmt3->bind_param(str_repeat('i', count($studentIds)), ...$studentIds);
            $stmt3->execute();

            //Delete students
            $studentQuery = "DELETE FROM students WHERE student_id IN ($in)";
            $stmt4 = $this->conn->prepare($studentQuery);
            $stmt4->bind_param(str_repeat('i', count($studentIds)), ...$studentIds);
            $stmt4->execute();
        }

        // Delete the class
        $stmt6 = $this->conn->prepare("DELETE FROM classes WHERE class_id = ? AND school_id = ?");
        $stmt6->bind_param("ii", $classId, $schoolId);
        $stmt6->execute();

        $this->conn->commit();
        return true;
    } catch (Exception $e) {
        $this->conn->rollback();
        error_log("Class deletion failed: " . $e->getMessage());
        return false;
    }
}

}

