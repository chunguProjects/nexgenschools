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

  public function countStudentsInClass(int $classId, ?int $academicYearId = null, bool $currentOnly = true): int {
    $academicYearId = $academicYearId ?? 0; // pass 0 if not filtering by year
    $query = "SELECT COUNT(*) AS total FROM student_enrollments WHERE class_id = ?";
    $types = "i";
    $params = [$classId];

    if ($academicYearId) {
        $query .= " AND academic_year_id = ?";
        $types .= "i";
        $params[] = $academicYearId;
    }

    if ($currentOnly) {
        $query .= " AND promoted = 0"; // only students currently in this class
    }

    $stmt = $this->conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return (int)($res['total'] ?? 0);
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

public function getOrCreateNextClass(int $currentClassId, int $targetAcademicYearId): ?array {
    // 1. Get current class
    $currentClass = $this->getClassById($currentClassId);
    if (!$currentClass) return null;

    $currentName = trim($currentClass['name']); // e.g., "Form 2B"
    $description = $currentClass['description'] ?? '';
    $teacherId   = $currentClass['teacher_id'] ?? null;

    // 2. Extract level (number) from class name
    $currentLevel = 1;
    if (preg_match('/(\d+)/', $currentName, $matches)) {
        $currentLevel = (int)$matches[1];
    }
    $nextLevel = $currentLevel + 1;

    // 3. Extract section (A, B, etc.)
    $section = '';
    if (preg_match('/([A-Z])$/i', $currentName, $secMatch)) {
        $section = strtoupper($secMatch[1]); // Keep same section
    }

    // 4. Build next class name safely
    if (preg_match('/(\d+)/', $currentName)) {
        // Replace only the first number occurrence
        $nextClassName = preg_replace('/\d+/', $nextLevel, $currentName, 1);
    } else {
        // Fallback if no number found in class name
        $nextClassName = "Form {$nextLevel}{$section}";
    }

    // 5. Check if class already exists in target year
    $query = "SELECT * FROM classes WHERE name = ? AND academic_year_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("si", $nextClassName, $targetAcademicYearId);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingClass = $result->fetch_assoc();
    $stmt->close();

    if ($existingClass) {
        return $existingClass; // ✅ Return existing class if found
    }

    // 6. Create new class (use NULL for teacher if not set)
    $insertQuery = "INSERT INTO classes (name, description, academic_year_id, teacher_id) VALUES (?, ?, ?, ?)";
    $stmt = $this->conn->prepare($insertQuery);
    if ($teacherId === null) {
        // bind NULL explicitly
        $null = null;
        $stmt->bind_param("ssii", $nextClassName, $description, $targetAcademicYearId, $null);
    } else {
        $stmt->bind_param("ssii", $nextClassName, $description, $targetAcademicYearId, $teacherId);
    }

    if ($stmt->execute()) {
        $newClassId = $this->conn->insert_id;
        $stmt->close();

        // 7. Assign all subjects to the new class
        $subjectQuery = "SELECT subject_id FROM subjects";
        $subjectResult = $this->conn->query($subjectQuery);
        $insertClassSubject = $this->conn->prepare("INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)");
        while ($row = $subjectResult->fetch_assoc()) {
            $subjectId = $row['subject_id'];
            $insertClassSubject->bind_param("ii", $newClassId, $subjectId);
            $insertClassSubject->execute();
        }
        $insertClassSubject->close();

        // 8. Return newly created class
        return $this->getClassById($newClassId);
    }

    $stmt->close();
    return null;
}
public function getNextLevelClasses(int $currentClassId, int $targetAcademicYearId): array {
    $currentClass = $this->getClassById($currentClassId);
    if (!$currentClass) return [];

    $currentName = trim($currentClass['name']);
    $currentLevel = 1;

    if (preg_match('/(\d+)/', $currentName, $matches)) {
        $currentLevel = (int)$matches[1];
    }
    $nextLevel = $currentLevel + 1;

    // Build pattern for LIKE query (find all classes in target year with this level)
    $likePattern = "%{$nextLevel}%";

    $query = "SELECT * FROM classes WHERE name LIKE ? AND academic_year_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("si", $likePattern, $targetAcademicYearId);
    $stmt->execute();
    $result = $stmt->get_result();

    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    $stmt->close();

    return $classes;
}
public function getClassesByYear(int $academicYearId): array {
    $stmt = $this->conn->prepare("SELECT * FROM classes WHERE academic_year_id = ? ORDER BY name ASC");
    $stmt->bind_param("i", $academicYearId);
    $stmt->execute();
    $result = $stmt->get_result();
    $classes = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $classes;
}
public function getTeacherClasses(int $teacherId, int $academicYearId): array {
    $stmt = $this->conn->prepare("
        SELECT *
        FROM classes
        WHERE teacher_id = ? AND academic_year_id = ?
        ORDER BY name
    ");
    $stmt->bind_param("ii", $teacherId, $academicYearId);
    $stmt->execute();
    $result = $stmt->get_result();
    $classes = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $classes;
}


}
?>
