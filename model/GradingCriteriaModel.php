<?php
class GradingCriteriaModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getByLevel($level) {
        $stmt = $this->conn->prepare("SELECT * FROM grading_criteria WHERE level = ? ORDER BY min_marks DESC");
        $stmt->bind_param("s", $level);
        $stmt->execute();
        $result = $stmt->get_result();
        $criteria = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $criteria;
    }

    public function deleteByLevel($level) {
        $stmt = $this->conn->prepare("DELETE FROM grading_criteria WHERE level = ?");
        $stmt->bind_param("s", $level);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

   public function saveCriteriaBatch($criteriaList) {
    foreach ($criteriaList as $c) {
        if (!empty($c['id']) && is_numeric($c['id'])) {
            // Update existing row
            $this->updateCriteria($c['id'], $c);
        } else {
            // Insert new row
            $stmt = $this->conn->prepare("
                INSERT INTO grading_criteria 
                (level, grade, min_marks, max_marks, description, form_teacher_remark, head_teacher_remark)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "ssiisss",
                $c['level'],
                $c['grade'],
                $c['min_marks'],
                $c['max_marks'],
                $c['description'],
                $c['form_teacher_remark'],
                $c['head_teacher_remark']
            );
            $stmt->execute();
            $stmt->close();
        }
    }
    return true;
}

    public function updateCriteria($id, $data) {
    $stmt = $this->conn->prepare("
        UPDATE grading_criteria 
        SET level = ?, grade = ?, min_marks = ?, max_marks = ?, description = ?, 
            form_teacher_remark = ?, head_teacher_remark = ?
        WHERE id = ?
    ");
    $stmt->bind_param(
        "ssiisssi",
        $data['level'],
        $data['grade'],
        $data['min_marks'],
        $data['max_marks'],
        $data['description'],
        $data['form_teacher_remark'],
        $data['head_teacher_remark'],
        $id
    );
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

}
