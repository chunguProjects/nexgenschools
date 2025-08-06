<?php
class GradingCriteriaModel {
    private $conn;
    private $schoolId;

    public function __construct($conn, $schoolId) {
        $this->conn = $conn;
        $this->schoolId = $schoolId;
    }

    public function getByLevel($level) {
        $stmt = $this->conn->prepare("SELECT * FROM grading_criteria WHERE school_id = ? AND level = ? ORDER BY min_marks DESC");
        $stmt->bind_param("is", $this->schoolId, $level);
        $stmt->execute();
        $result = $stmt->get_result();
        $criteria = [];
        while ($row = $result->fetch_assoc()) {
            $criteria[] = $row;
        }
        $stmt->close();
        return $criteria;
    }

    public function deleteByLevel($level) {
        $stmt = $this->conn->prepare("DELETE FROM grading_criteria WHERE school_id = ? AND level = ?");
        $stmt->bind_param("is", $this->schoolId, $level);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function insertCriteriaBatch($criteriaList) {
        foreach ($criteriaList as $c) {
            $stmt = $this->conn->prepare("
                INSERT INTO grading_criteria 
                (school_id, level, grade, min_marks, max_marks, description, form_teacher_remark, head_teacher_remark)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "issiisss",
                $this->schoolId,
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
        return true;
    }
}
