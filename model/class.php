<?php
class ClassGroup {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add a new class and assign all subjects
    public function addClass($name, $description, $academicYearId, $teacherId = null) {
        // Prevent duplicate class name in the same academic year
        $checkQuery = "SELECT 1 FROM classes WHERE name = ? AND academic_year_id = ?";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bind_param("si", $name, $academicYearId);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0) return false;

        // Insert class
        $query = "INSERT INTO classes (name, description, academic_year_id, teacher_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssii", $name, $description, $academicYearId, $teacherId);
        if ($stmt->execute()) {
            $classId = $this->conn->insert_id;

            // Assign all subjects
            $subjectQuery = "SELECT subject_id FROM subjects";
            $subjectResult = $this->conn->query($subjectQuery);

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

    // Fetch all classes, optionally filter by academic year
    public function getAllClasses($academicYearId = null) {
        $query = "
            SELECT 
                c.class_id,
                c.name AS class_name,
                c.description,
                c.created_at,
                u.user_id AS teacher_id,
                u.full_name AS teacher_name,
                c.academic_year_id,
                ay.name AS academic_year_name
            FROM classes c
            LEFT JOIN users u ON c.teacher_id = u.user_id
            LEFT JOIN academic_years ay ON c.academic_year_id = ay.academic_year_id
        ";
        if ($academicYearId !== null) {
            $query .= " WHERE c.academic_year_id = " . (int)$academicYearId;
        }
        $query .= " ORDER BY c.created_at DESC";

        $result = $this->conn->query($query);
        $classes = [];
        while ($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }
        return $classes;
    }

    public function getClassById($classId) {
        $query = "SELECT * FROM classes WHERE class_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $classId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    public function countStudentsInClass($classId) {
        $query = "SELECT COUNT(*) AS total FROM students WHERE class_id = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $classId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'] ?? 0;
    }

    public function updateClass($classId, $name, $description, $academicYearId, $teacherId = null) {
        $query = "UPDATE classes SET name = ?, description = ?, academic_year_id = ?, teacher_id = ? WHERE class_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssiii", $name, $description, $academicYearId, $teacherId, $classId);
        return $stmt->execute();
    }

    public function deleteClass($classId) {
        $this->conn->begin_transaction();
        try {
            // Delete class subjects
            $stmt1 = $this->conn->prepare("DELETE FROM class_subjects WHERE class_id = ?");
            $stmt1->bind_param("i", $classId);
            $stmt1->execute();

            // Fetch students
            $studentQuery = "SELECT student_id FROM students WHERE class_id = ?";
            $stmt2 = $this->conn->prepare($studentQuery);
            $stmt2->bind_param("i", $classId);
            $stmt2->execute();
            $studentsResult = $stmt2->get_result();

            $studentIds = [];
            while ($row = $studentsResult->fetch_assoc()) {
                $studentIds[] = $row['student_id'];
            }

            if (!empty($studentIds)) {
                $in = str_repeat('?,', count($studentIds) - 1) . '?';

                // Delete grades
                $gradeQuery = "DELETE FROM grades WHERE student_id IN ($in)";
                $stmt3 = $this->conn->prepare($gradeQuery);
                $stmt3->bind_param(str_repeat('i', count($studentIds)), ...$studentIds);
                $stmt3->execute();

                // Delete students
                $studentQuery = "DELETE FROM students WHERE student_id IN ($in)";
                $stmt4 = $this->conn->prepare($studentQuery);
                $stmt4->bind_param(str_repeat('i', count($studentIds)), ...$studentIds);
                $stmt4->execute();
            }

            // Delete class
            $stmt5 = $this->conn->prepare("DELETE FROM classes WHERE class_id = ?");
            $stmt5->bind_param("i", $classId);
            $stmt5->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Class deletion failed: " . $e->getMessage());
            return false;
        }
    }
}
?>
