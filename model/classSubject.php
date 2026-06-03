<?php
class ClassSubject {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get subjects assigned to a class for a given academic year
    public function getByClass($classId, $academicYearId) {
        $stmt = $this->conn->prepare(
            "SELECT cs.id as class_subject_id, s.subject_id, s.subject_name
             FROM class_subject cs
             JOIN subjects s ON cs.subject_id = s.subject_id
             WHERE cs.class_id = ? AND cs.academic_year_id = ?"
        );
        $stmt->bind_param("ii", $classId, $academicYearId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Save subjects for a class (replace current)
 public function saveClassSubjects($classId, $academicYearId, $subjectIds = [])
{
    $subjectIds = array_map('intval', $subjectIds);

    // 1. Get existing subjects
    $stmt = $this->conn->prepare("
        SELECT subject_id, id 
        FROM class_subject 
        WHERE class_id=? AND academic_year_id=?
    ");
    $stmt->bind_param("ii", $classId, $academicYearId);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $existingMap = [];
    foreach ($res as $row) {
        $existingMap[$row['subject_id']] = $row['id'];
    }

    // 2. DELETE removed subjects SAFELY (check FK first)
    $toDelete = array_diff(array_keys($existingMap), $subjectIds);

    if (!empty($toDelete)) {

        foreach ($toDelete as $subjectId) {
            $classSubjectId = $existingMap[$subjectId];

            // ❗ FIRST delete teacher assignment (child table)
            $stmt1 = $this->conn->prepare("
                DELETE FROM class_subject_teacher 
                WHERE class_subject_id = ?
            ");
            $stmt1->bind_param("i", $classSubjectId);
            $stmt1->execute();

            // THEN delete class_subject
            $stmt2 = $this->conn->prepare("
                DELETE FROM class_subject 
                WHERE id = ?
            ");
            $stmt2->bind_param("i", $classSubjectId);
            $stmt2->execute();
        }
    }

    // 3. INSERT new subjects
    $toInsert = array_diff($subjectIds, array_keys($existingMap));

    if (!empty($toInsert)) {
        $stmtIns = $this->conn->prepare("
            INSERT INTO class_subject (class_id, subject_id, academic_year_id)
            VALUES (?, ?, ?)
        ");

        foreach ($toInsert as $subId) {
            $stmtIns->bind_param("iii", $classId, $subId, $academicYearId);
            $stmtIns->execute();
        }
    }

    return true;
}
    // Get all subjects
    public function getAllSubjects() {
        $result = $this->conn->query("SELECT * FROM subjects ORDER BY subject_name ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}