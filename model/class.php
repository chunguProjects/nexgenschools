<?php
class ClassGroup {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /* =====================================================
       CREATE CLASS
    ===================================================== */
    public function addClass($name, $description, $academicYearId, $formTeacherId = null) {
        $check = $this->conn->prepare(
            "SELECT 1 FROM classes WHERE name = ? AND academic_year_id = ?"
        );
        $check->bind_param("si", $name, $academicYearId);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) return false;

        $stmt = $this->conn->prepare(
            "INSERT INTO classes (name, description, form_teacher_id, academic_year_id)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssii", $name, $description, $formTeacherId, $academicYearId);
        return $stmt->execute();
    }

    /* =====================================================
       GET ALL CLASSES
    ===================================================== */
    public function getAllClasses($academicYearId = null): array {
        $classes = [];
        if ($academicYearId !== null) {
            $query = "
                SELECT 
                    c.class_id,
                    c.name AS class_name,
                    c.description,
                    c.created_at,
                    c.academic_year_id,
                    ay.name AS academic_year_name,
                    u.user_id AS form_teacher_id,
                    u.full_name AS form_teacher_name
                FROM classes c
                LEFT JOIN users u ON c.form_teacher_id = u.user_id
                LEFT JOIN academic_years ay ON c.academic_year_id = ay.academic_year_id
                WHERE c.academic_year_id = ?
                ORDER BY c.created_at DESC
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $academicYearId);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $query = "
                SELECT 
                    c.class_id,
                    c.name AS class_name,
                    c.description,
                    c.created_at,
                    c.academic_year_id,
                    ay.name AS academic_year_name,
                    u.user_id AS form_teacher_id,
                    u.full_name AS form_teacher_name
                FROM classes c
                LEFT JOIN users u ON c.form_teacher_id = u.user_id
                LEFT JOIN academic_years ay ON c.academic_year_id = ay.academic_year_id
                ORDER BY c.created_at DESC
            ";
            $result = $this->conn->query($query);
        }

        while ($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }
        return $classes;
    }

    /* =====================================================
       GET CLASS BY ID
    ===================================================== */
    public function getClassById($classId): ?array {
        $stmt = $this->conn->prepare(
            "SELECT * FROM classes WHERE class_id = ?"
        );
        $stmt->bind_param("i", $classId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    /* =====================================================
       UPDATE CLASS
    ===================================================== */
    public function updateClass($classId, $name, $description, $academicYearId, $formTeacherId = null): bool {
        $stmt = $this->conn->prepare(
            "UPDATE classes 
             SET name = ?, description = ?, form_teacher_id = ?, academic_year_id = ?
             WHERE class_id = ?"
        );
        $stmt->bind_param("ssiii", $name, $description, $formTeacherId, $academicYearId, $classId);
        return $stmt->execute();
    }

    /* =====================================================
       DELETE CLASS
    ===================================================== */
public function deleteClass($classId): array {
    $this->conn->begin_transaction();

    try {

        // 1. Delete teacher assignments FIRST (depends on class_subject)
        $stmt1 = $this->conn->prepare("
            DELETE cst FROM class_subject_teacher cst
            INNER JOIN class_subject cs ON cst.class_subject_id = cs.id
            WHERE cs.class_id = ?
        ");
        $stmt1->bind_param("i", $classId);
        $stmt1->execute();

        // 2. Delete class_subject records
        $stmt2 = $this->conn->prepare("
            DELETE FROM class_subject WHERE class_id = ?
        ");
        $stmt2->bind_param("i", $classId);
        $stmt2->execute();

        // 3. Delete student enrollments
        $stmt3 = $this->conn->prepare("
            DELETE FROM student_enrollments WHERE class_id = ?
        ");
        $stmt3->bind_param("i", $classId);
        $stmt3->execute();

        // 4. Finally delete class
        $stmt4 = $this->conn->prepare("
            DELETE FROM classes WHERE class_id = ?
        ");
        $stmt4->bind_param("i", $classId);
        $stmt4->execute();

        $this->conn->commit();

        return [
            "success" => true,
            "message" => "Class deleted successfully"
        ];

    } catch (Exception $e) {

        $this->conn->rollback();

        return [
            "success" => false,
            "message" => $e->getMessage()
        ];
    }
}
 public function getClassesByYear($academicYearId) {
        $stmt = $this->conn->prepare("SELECT c.*, u.full_name AS form_teacher_name 
                                      FROM classes c
                                      LEFT JOIN users u ON c.form_teacher_id = u.user_id
                                      WHERE c.academic_year_id = ?
                                      ORDER BY c.name ASC");
        $stmt->bind_param("i", $academicYearId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    /* =====================================================
       COUNT STUDENTS IN CLASS
    ===================================================== */
public function countStudentsInClass(int $classId, int $academicYearId): int {
    $stmt = $this->conn->prepare("
        SELECT COUNT(*) AS total
        FROM student_enrollments se
        INNER JOIN students s ON se.student_id = s.student_id
        WHERE se.class_id = ?
        AND se.academic_year_id = ?
        AND se.status = 'active'
        AND s.is_deleted = 0
    ");
    $stmt->bind_param("ii", $classId, $academicYearId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return (int)($result['total'] ?? 0);
}
    /* =====================================================
       GET TEACHER CLASSES (BY SUBJECT)
    ===================================================== */
 public function getTeacherClasses(int $teacherId, int $academicYearId): array {
    $stmt = $this->conn->prepare("
        SELECT DISTINCT c.*
        FROM class_subject_teacher cst
        JOIN class_subject cs ON cst.class_subject_id = cs.id
        JOIN classes c ON cs.class_id = c.class_id
        WHERE cst.teacher_id = ? 
        AND cs.academic_year_id = ?
        ORDER BY c.name ASC
    ");

    $stmt->bind_param("ii", $teacherId, $academicYearId);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

    /* =====================================================
       GET FORM TEACHER CLASSES
    ===================================================== */
    public function getFormTeacherClasses(int $teacherId, int $academicYearId): array {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM classes
            WHERE form_teacher_id = ? AND academic_year_id = ?
            ORDER BY name ASC
        ");
        $stmt->bind_param("ii", $teacherId, $academicYearId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /* =====================================================
       ASSIGN OR UPDATE CLASS-SUBJECT-TEACHER
    ===================================================== */
    public function assignSubjectTeacher(int $classId, int $subjectId, int $teacherId, int $academicYearId): bool {
        $stmt = $this->conn->prepare("
            INSERT INTO class_subject_teacher (class_id, subject_id, teacher_id, academic_year_id)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id)
        ");
        $stmt->bind_param("iiii", $classId, $subjectId, $teacherId, $academicYearId);
        return $stmt->execute();
    }

    /* =====================================================
       DELETE CLASS-SUBJECT-TEACHER ASSIGNMENT
    ===================================================== */
    public function deleteClassSubjectTeacher(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM class_subject_teacher WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /* =====================================================
       GET ALL SUBJECT TEACHER ASSIGNMENTS FOR A CLASS
    ===================================================== */
    public function getClassSubjectTeachers(int $classId, int $academicYearId): array {
        $stmt = $this->conn->prepare("
            SELECT cst.id, cst.class_id, cst.subject_id, cst.teacher_id,
                   s.subject_name, s.subject_code,
                   u.full_name AS teacher_name
            FROM class_subject_teacher cst
            JOIN subjects s ON cst.subject_id = s.subject_id
            LEFT JOIN users u ON cst.teacher_id = u.user_id
            WHERE cst.class_id = ? AND cst.academic_year_id = ?
            ORDER BY s.subject_name ASC
        ");
        $stmt->bind_param("ii", $classId, $academicYearId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getTeacherSubjectsByClass(int $teacherId, int $classId, int $academicYearId): array {
    $stmt = $this->conn->prepare("
        SELECT 
            s.subject_id,
            s.subject_name,
            s.subject_code
        FROM class_subject_teacher cst
        JOIN class_subject cs ON cst.class_subject_id = cs.id
        JOIN subjects s ON cs.subject_id = s.subject_id
        WHERE cst.teacher_id = ?
        AND cs.class_id = ?
        AND cs.academic_year_id = ?
        ORDER BY s.subject_name ASC
    ");

    $stmt->bind_param("iii", $teacherId, $classId, $academicYearId);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
}
?>
