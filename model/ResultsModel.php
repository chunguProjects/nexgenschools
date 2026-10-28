<?php

class ResultsModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    private function query(string $sql, string $types = '', array $params = []): array
    {
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Query failed to prepare: ' . $this->conn->error);
        }
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    public function getSchoolName(): string
    {
        $result = $this->conn->query('SELECT school_name FROM settings LIMIT 1');
        $row = $result ? $result->fetch_assoc() : null;
        return ($row && !empty($row['school_name'])) ? $row['school_name'] : 'School Results Portal';
    }

    public function getAcademicYears(): array
    {
        return $this->query(
            'SELECT academic_year_id, name, is_current FROM academic_years ORDER BY start_date DESC'
        );
    }

    public function getExams(int $academicYearId): array
    {
        return $this->query(
            'SELECT id, exam_name, term FROM exams WHERE academic_year_id = ? ORDER BY created_at',
            'i',
            [$academicYearId]
        );
    }

    public function getClasses(int $academicYearId): array
    {
        return $this->query(
            'SELECT class_id, name FROM classes WHERE academic_year_id = ? ORDER BY name',
            'i',
            [$academicYearId]
        );
    }

    public function getSubjectsForClass(int $classId, int $academicYearId): array
    {
        return $this->query(
            'SELECT s.subject_id, s.subject_name, s.subject_code
             FROM class_subject cs
             JOIN subjects s ON s.subject_id = cs.subject_id
             WHERE cs.class_id = ? AND cs.academic_year_id = ?
             ORDER BY s.subject_name',
            'ii',
            [$classId, $academicYearId]
        );
    }

    public function getEnrolledStudents(int $classId, int $academicYearId): array
    {
        return $this->query(
            "SELECT st.student_id, st.first_name, st.last_name, st.gender
             FROM students st
             JOIN student_enrollments se ON se.student_id = st.student_id
             WHERE se.class_id = ? AND se.academic_year_id = ?
               AND se.status = 'active' AND st.is_deleted = 0
             ORDER BY st.last_name, st.first_name",
            'ii',
            [$classId, $academicYearId]
        );
    }

    // NOTE: filtered by level (JCE / MSCE) as set up by the admin under
    // grading criteria management.
    public function getGradingCriteria(string $level): array
    {
        return $this->query(
            'SELECT grade, min_marks, max_marks, description
             FROM grading_criteria
             WHERE level = ?
             ORDER BY min_marks DESC',
            's',
            [$level]
        );
    }

    public function getGrades(int $examId, int $classId): array
    {
        return $this->query(
            'SELECT student_id, subject_id, marks FROM grades WHERE exam_id = ? AND class_id = ?',
            'ii',
            [$examId, $classId]
        );
    }
}