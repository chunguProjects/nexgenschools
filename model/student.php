<?php
class Student {
    private $conn;
    private $parentCredentials = [];

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getParentCredentials(): array {
        return $this->parentCredentials;
    }

    public function addStudentsWithParents(array $students) {
        $this->conn->begin_transaction();

        try {
            $studentQuery = "
                INSERT INTO students (first_name, last_name, gender, class_id, school_id, contact_number, date_of_birth, username, password)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $stmtStudent = $this->conn->prepare($studentQuery);
            $accountList = [];

            foreach ($students as $s) {
                if (empty($s['first_name']) || empty($s['gender']) || empty($s['parent_phone'])) {
                    throw new Exception("Missing required student or parent data.");
                }

                $schoolId = $_SESSION['user']['school_id'] ?? null;
                if (!$schoolId) throw new Exception("School ID missing in session.");

                $firstName = trim($s['first_name']);
                $lastName  = trim($s['last_name'] ?? '');
                $gender    = $s['gender'];
                $classId   = $s['class_id'];
                $dob       = $s['date_of_birth'] ?? null;
                $parentPhone = trim($s['parent_phone']);
                $relationship = $s['relationship'] ?? 'Parent';
                $contactNumber = $parentPhone;

                $username = $parentPhone;
                $plainPassword = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

                $stmtStudent->bind_param("sssisssss", $firstName, $lastName, $gender, $classId, $schoolId, $contactNumber, $dob, $username, $hashedPassword);
                if (!$stmtStudent->execute()) throw new Exception("Failed to insert student: " . $stmtStudent->error);

                $this->parentCredentials[] = [
                    'student_name' => "$firstName $lastName",
                    'username'     => $username,
                    'password'     => $plainPassword,
                    'phone_number' => $parentPhone,
                ];

                $accountList[] = [
                    'student' => [
                        'first_name'   => $firstName,
                        'last_name'    => $lastName,
                        'date_of_birth'=> $dob,
                        'username'     => $username,
                        'raw_password' => $plainPassword,
                        'phone_number' => $parentPhone,
                        'parent_name'  => "$relationship of $firstName"
                    ]
                ];
            }

            $this->conn->commit();
            return ['success' => true, 'accounts' => $accountList];
        } catch (Exception $ex) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $ex->getMessage()];
        }
    }

    public function updateStudent(int $studentId, array $data): bool {
        $fields = [];
        $params = [];
        $types = '';

        $allowedFields = ['first_name', 'last_name', 'gender', 'class_id', 'date_of_birth', 'contact_number'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
                $types .= is_int($data[$field]) ? 'i' : 's';
            }
        }

        if (empty($fields)) return false;

        $query = "UPDATE students SET " . implode(', ', $fields) . " WHERE student_id = ?";
        $params[] = $studentId;
        $types .= 'i';

        $stmt = $this->conn->prepare($query);
        if (!$stmt) return false;

        $stmt->bind_param($types, ...$params);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function getStudentById(int $studentId): ?array {
        $query = "SELECT * FROM students WHERE student_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();

        return $student ?: null;
    }

    public function getStudentsByClass($classId) {
        $query = "SELECT student_id, first_name, last_name, gender, date_of_birth, contact_number
                  FROM students WHERE class_id = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $classId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getStudentsBySchool($schoolId) {
        $query = "SELECT s.*, c.name AS class_name 
                  FROM students s 
                  LEFT JOIN classes c ON s.class_id = c.class_id 
                  WHERE s.school_id = ? AND s.is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $schoolId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function deleteStudent(int $studentId): bool {
        $query = "UPDATE students SET is_deleted = 1 WHERE student_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $studentId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
   
}
?>
