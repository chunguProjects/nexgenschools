<?php
class Student {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Add multiple students with parent user creation and linking
     * @param array $students Array of students with parent info
     * @return array ['success' => bool, 'message' => string]
     */
   public function addStudentsWithParents(array $students) {
    // Begin transaction for consistency
    $this->conn->begin_transaction();

    try {
        // Prepare insert statement for students
        $studentQuery = "
            INSERT INTO students (first_name, last_name, gender, class_id, school_id, contact_number)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmtStudent = $this->conn->prepare($studentQuery);

        // Prepare select and insert for parent user
        $selectParentQuery = "SELECT user_id FROM users WHERE phone_number = ? LIMIT 1";
        $stmtSelectParent = $this->conn->prepare($selectParentQuery);

        $insertParentQuery = "
            INSERT INTO users (school_id, full_name, phone_number, username, password, role_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ";
        $stmtInsertParent = $this->conn->prepare($insertParentQuery);

        // Prepare insert for parent-student link
        $insertLinkQuery = "
            INSERT INTO parent_student (parent_id, student_id, relationship, created_at)
            VALUES (?, ?, ?, NOW())
        ";
        $stmtInsertLink = $this->conn->prepare($insertLinkQuery);

        foreach ($students as $s) {
            // Validate minimal required fields
            if (empty($s['first_name']) || empty($s['gender']) || empty($s['parent_phone'])) {
                throw new Exception("Missing required student or parent data.");
            }

            // Assume school_id from session or passed in $s
            $schoolId = $_SESSION['user']['school_id'] ?? null;
            if (!$schoolId) {
                throw new Exception("School ID missing in session.");
            }

            $firstName = $s['first_name'];
            $lastName = $s['last_name'] ?? '';
            $gender = $s['gender'];
            $classId = $s['class_id'];
            $parentPhone = $s['parent_phone'];
            $relationship = $s['relationship'] ?? 'Parent';
            $contactNumber = $parentPhone; // Store parent's phone as student's contact

            // 1. Insert student
            $stmtStudent->bind_param("sssiss", $firstName, $lastName, $gender, $classId, $schoolId, $contactNumber);
            if (!$stmtStudent->execute()) {
                throw new Exception("Failed to insert student: " . $stmtStudent->error);
            }
            $studentId = $stmtStudent->insert_id;

           $parentId = null;
$stmtSelectParent->bind_param("s", $parentPhone);
$stmtSelectParent->execute();
$stmtSelectParent->store_result();

        // Declare $fetchedParentId before binding
        $fetchedParentId = null;

        if ($stmtSelectParent->num_rows > 0) {
            $stmtSelectParent->bind_result($fetchedParentId);
            if ($stmtSelectParent->fetch()) {
                $parentId = $fetchedParentId;
            } else {
                throw new Exception("Parent found but couldn't fetch ID.");
            }
                    } else {
                // 3. Create new parent
                $parentFullName = "Parent of $firstName $lastName";
                $username = $parentPhone;
                $defaultPassword = password_hash('changeme123', PASSWORD_DEFAULT);

                $parentRoleId = $this->getRoleIdByName('parent');
                if (!$parentRoleId) {
                    throw new Exception("Parent role not found in roles table.");
                }

                $stmtInsertParent->bind_param("issssi", $schoolId, $parentFullName, $parentPhone, $username, $defaultPassword, $parentRoleId);
                if (!$stmtInsertParent->execute()) {
                    throw new Exception("Failed to insert parent user: " . $stmtInsertParent->error);
                }

                $parentId = $stmtInsertParent->insert_id;
            }

            // 4. Link parent to student
            $stmtInsertLink->bind_param("iis", $parentId, $studentId, $relationship);
            if (!$stmtInsertLink->execute()) {
                if ($this->conn->errno == 1062) {
                    // Duplicate link, ignore silently
                } else {
                    throw new Exception("Failed to link parent and student: " . $stmtInsertLink->error);
                }
            }
        }

        $this->conn->commit();
        return ['success' => true];

    } catch (Exception $ex) {
        $this->conn->rollback();
        return ['success' => false, 'message' => $ex->getMessage()];
    }
}


    /**
     * Get role ID by role name
     * @param string $roleName
     * @return int|null
     */
   private function getRoleIdByName(string $roleName): ?int {
    $query = "SELECT role_id FROM roles WHERE name = ? LIMIT 1";
    $stmt = $this->conn->prepare($query);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("s", $roleName);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $roleId = null;            // Initialize before binding
    $stmt->bind_result($roleId);
    $found = $stmt->fetch();
    $stmt->close();

    if ($found && $roleId !== null) {
        return $roleId;
    }

    return null;
}

    public function getStudentsByClass($class_id) {
        $query = "SELECT student_id, first_name, last_name FROM students WHERE class_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
