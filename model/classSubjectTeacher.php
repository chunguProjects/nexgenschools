<?php
class ClassSubjectTeacher {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get current teacher assignments for class subjects
    public function getByClass($classId, $academicYearId) {
        $stmt = $this->conn->prepare(
            "SELECT cst.id, cst.teacher_id, cs.id AS class_subject_id, cs.subject_id
             FROM class_subject_teacher cst
             JOIN class_subject cs ON cs.id = cst.class_subject_id
             WHERE cs.class_id = ? AND cs.academic_year_id = ?"
        );
        $stmt->bind_param("ii", $classId, $academicYearId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

public function assignTeachers($assignments = [])
{
    foreach ($assignments as $classSubjectId => $teacherId) {

        $classSubjectId = intval($classSubjectId);
        $teacherId = intval($teacherId);

        // STEP 1: If empty selection → DELETE assignment (RESET)
        if ($teacherId <= 0) {

            $stmt = $this->conn->prepare("
                DELETE FROM class_subject_teacher 
                WHERE class_subject_id = ?
            ");
            $stmt->bind_param("i", $classSubjectId);
            $stmt->execute();

            continue;
        }

        // STEP 2: Check if exists
        $stmt = $this->conn->prepare("
            SELECT id 
            FROM class_subject_teacher 
            WHERE class_subject_id = ?
        ");
        $stmt->bind_param("i", $classSubjectId);
        $stmt->execute();
        $res = $stmt->get_result();

        // STEP 3: Update or insert
        if ($res->num_rows > 0) {

            $stmt2 = $this->conn->prepare("
                UPDATE class_subject_teacher 
                SET teacher_id = ? 
                WHERE class_subject_id = ?
            ");
            $stmt2->bind_param("ii", $teacherId, $classSubjectId);
            $stmt2->execute();

        } else {

            $stmt3 = $this->conn->prepare("
                INSERT INTO class_subject_teacher (class_subject_id, teacher_id)
                VALUES (?, ?)
            ");
            $stmt3->bind_param("ii", $classSubjectId, $teacherId);
            $stmt3->execute();
        }
    }

    return true;
}
}