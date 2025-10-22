<?php
class Student {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // -----------------------------
    // Add Multiple Students
    // -----------------------------
    public function addStudents(array $students) {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO students
                (first_name, last_name, gender, class_id, date_of_birth, contact_number, username, password)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($students as $s) {
                if (empty($s['first_name']) || empty($s['gender']) || empty($s['contact_number']) || empty($s['class_id']) || empty($s['date_of_birth'])) {
                    throw new Exception("Missing required student data.");
                }

                $firstName = trim($s['first_name']);
                $lastName  = trim($s['last_name'] ?? '');
                $gender    = $s['gender'];
                $classId   = (int)$s['class_id'];
                $dob       = $s['date_of_birth'];
                $contact   = trim($s['contact_number']);

                $username = $this->generateRegistrationNumber($classId);
                $password = password_hash(date('Ymd', strtotime($dob)), PASSWORD_DEFAULT);

                $stmt->bind_param("sssissss", $firstName, $lastName, $gender, $classId, $dob, $contact, $username, $password);
                if (!$stmt->execute()) {
                    throw new Exception("Adding new students failed: " . $stmt->error);
                }

                $studentId = $this->conn->insert_id;
                $this->enrollStudent($studentId, $classId);
            }

            $this->conn->commit();
            return ['success' => true];
        } catch (Exception $ex) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $ex->getMessage()];
        }
    }

    // -----------------------------
    // Enroll Student
    // -----------------------------
    private function enrollStudent(int $studentId, int $classId) {
        $academicYearId = $this->getAcademicYearIdByClass($classId);

        // Check if already enrolled
        $stmt = $this->conn->prepare("
            SELECT enrollment_id 
            FROM student_enrollments 
            WHERE student_id = ? AND academic_year_id = ? 
            LIMIT 1
        ");
        $stmt->bind_param("ii", $studentId, $academicYearId);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($exists) return;

        $stmt = $this->conn->prepare("
            INSERT INTO student_enrollments (student_id, class_id, academic_year_id)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iii", $studentId, $classId, $academicYearId);
        if (!$stmt->execute()) {
            throw new Exception("Enrollment failed: " . $stmt->error);
        }
        $stmt->close();
    }

    // -----------------------------
    // Update Enrollment / Promotion
    // -----------------------------
    public function updateEnrollment(int $studentId, int $classId, ?int $academicYearId = null): bool {
    // Use provided academic year or get it from class
    $academicYearId = $academicYearId ?? $this->getAcademicYearIdByClass($classId);

    // Check if enrollment exists
    $stmt = $this->conn->prepare("
        SELECT enrollment_id 
        FROM student_enrollments 
        WHERE student_id = ? AND academic_year_id = ? 
        LIMIT 1
    ");
    $stmt->bind_param("ii", $studentId, $academicYearId);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existing) {
        // Update class_id and mark as promoted
        $stmt = $this->conn->prepare("
            UPDATE student_enrollments
            SET class_id = ?, promoted = 1
            WHERE enrollment_id = ?
        ");
        $stmt->bind_param("ii", $classId, $existing['enrollment_id']);
    } else {
        // Insert new enrollment with promoted = 1
        $stmt = $this->conn->prepare("
            INSERT INTO student_enrollments (student_id, class_id, academic_year_id, promoted)
            VALUES (?, ?, ?, 1)
        ");
        $stmt->bind_param("iii", $studentId, $classId, $academicYearId);
    }

    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

    // -----------------------------
    // Helpers
    // -----------------------------
    private function getAcademicYearIdByClass(int $classId): int {
        $stmt = $this->conn->prepare("SELECT academic_year_id FROM classes WHERE class_id = ? LIMIT 1");
        $stmt->bind_param("i", $classId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($res['academic_year_id'] ?? 0);
    }

    public function getCurrentAcademicYearId(): int {
        $res = $this->conn->query("SELECT academic_year_id FROM academic_years WHERE is_current = 1 LIMIT 1");
        $row = $res->fetch_assoc();
        return (int)($row['academic_year_id'] ?? 0);
    }

    // -----------------------------
    // Get Students By Class (current year)
    // -----------------------------
    public function getStudentsByClass(int $classId, ?int $academicYearId = null): array {
        $academicYearId = $academicYearId ?? $this->getCurrentAcademicYearId();

        $stmt = $this->conn->prepare("
            SELECT s.student_id, s.first_name, s.last_name, s.gender, s.date_of_birth, s.contact_number, s.username
            FROM students s
            JOIN student_enrollments se ON s.student_id = se.student_id
            WHERE se.class_id = ? AND se.academic_year_id = ? AND s.is_deleted = 0
        ");
        $stmt->bind_param("ii", $classId, $academicYearId);
        $stmt->execute();
        $result = $stmt->get_result();
        $students = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $students;
    }

    // -----------------------------
    // Update Student Info
    // -----------------------------
    public function updateStudent(int $studentId, array $data): bool {
        $fields = [];
        $params = [];
        $types = '';

        $allowed = ['first_name', 'last_name', 'gender', 'class_id', 'date_of_birth', 'contact_number'];
        $current = $this->getStudentById($studentId);
        $oldClassId = $current['class_id'] ?? null;

        foreach ($allowed as $f) {
            if (isset($data[$f])) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
                $types .= is_int($data[$f]) ? 'i' : 's';
            }
        }
        if (empty($fields)) return false;

        $query = "UPDATE students SET " . implode(', ', $fields) . " WHERE student_id = ?";
        $params[] = $studentId;
        $types .= 'i';

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $success = $stmt->execute();
        $stmt->close();

        if ($success && isset($data['class_id']) && $data['class_id'] != $oldClassId) {
            $this->updateEnrollment($studentId, $data['class_id']);
        }

        return $success;
    }

    public function getStudentById(int $studentId): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM students WHERE student_id = ? LIMIT 1");
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $student ?: null;
    }

    // -----------------------------
    // Registration Number Generator
    // -----------------------------
    private function generateRegistrationNumber(int $classId): string {
        $stmt = $this->conn->prepare("SELECT name FROM classes WHERE class_id = ? LIMIT 1");
        $stmt->bind_param("i", $classId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $className = $res['name'] ?? 'Form1';
        preg_match('/(\d+)/', $className, $matches);
        $level = $matches[1] ?? 1;

        $year = date('Y') - ($level - 1);
        $like = "STU{$year}-%";

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS cnt FROM students WHERE username LIKE ?");
        $stmt->bind_param("s", $like);
        $stmt->execute();
        $cnt = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
        $stmt->close();

        return sprintf("STU%s-%03d", $year, $cnt + 1);
    }

    // -----------------------------
    // Delete Student
    // -----------------------------
    public function deleteStudent(int $studentId): bool {
        $stmt = $this->conn->prepare("UPDATE students SET is_deleted = 1 WHERE student_id = ?");
        $stmt->bind_param("i", $studentId);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    public function promoteStudents(array $studentIds, int $targetClassId, int $targetAcademicYearId): array {
    $this->conn->begin_transaction();

    try {
        $currentYearId = $this->getCurrentAcademicYearId();

        foreach ($studentIds as $studentId) {
            // ✅ Mark current enrollment as promoted/completed
            $stmt = $this->conn->prepare("
                UPDATE student_enrollments 
                SET promoted = 1, status = 'completed'
                WHERE student_id = ? AND academic_year_id = ?
            ");
            $stmt->bind_param("ii", $studentId, $currentYearId);
            $stmt->execute();
            $stmt->close();

            // ✅ Insert new enrollment for the next academic year
            $stmt = $this->conn->prepare("
                INSERT INTO student_enrollments (student_id, class_id, academic_year_id)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE class_id = VALUES(class_id)
            ");
            $stmt->bind_param("iii", $studentId, $targetClassId, $targetAcademicYearId);
            if (!$stmt->execute()) {
                throw new Exception("Failed to enroll student {$studentId}: " . $stmt->error);
            }
            $stmt->close();

            // ✅ Update student's main record
            $stmt = $this->conn->prepare("UPDATE students SET class_id = ? WHERE student_id = ?");
            $stmt->bind_param("ii", $targetClassId, $studentId);
            $stmt->execute();
            $stmt->close();
        }

        $this->conn->commit();
        return ['success' => true, 'message' => 'Students promoted successfully'];

    } catch (Exception $e) {
        $this->conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}


}
?>
