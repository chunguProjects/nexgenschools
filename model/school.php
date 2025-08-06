<?php
class School {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function createSchoolWithAdmin($schoolName, $status, $adminData) {
        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Insert school
            $stmt = $this->conn->prepare("INSERT INTO schools (name, status) VALUES (?, ?)");
            $stmt->bind_param("ss", $schoolName, $status);
            $stmt->execute();
            $schoolId = $stmt->insert_id;

            // Get schooladmin role_id
            $roleStmt = $this->conn->prepare("SELECT role_id FROM roles WHERE name = 'schooladmin'");
            $roleStmt->execute();
            $roleResult = $roleStmt->get_result();
            $roleRow = $roleResult->fetch_assoc();
            $roleId = $roleRow['role_id'];

            // Hash password
            $hashedPassword = password_hash($adminData['password'], PASSWORD_DEFAULT);

            // Insert admin user
            $adminStmt = $this->conn->prepare("
                INSERT INTO users (
                    school_id, full_name, country_code, phone_number, username,
                    email, password, role_id, password_updated
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)
            ");
            $adminStmt->bind_param(
                "issssssi",
                $schoolId,
                $adminData['full_name'],
                $adminData['country_code'],
                $adminData['phone_number'],
                $adminData['username'],
                $adminData['email'],
                $hashedPassword,
                $roleId
            );
            $adminStmt->execute();

            // Commit transaction
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Failed to create school and admin: " . $e->getMessage());
        }
    }
public function getSchoolById($schoolId) {
    $stmt = $this->conn->prepare("SELECT name AS school_name FROM schools WHERE school_id = ?");
    $stmt->bind_param("i", $schoolId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc(); // returns ['school_name' => 'XYZ School']
}

}
