<?php
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add new user
    public function addUser($fullName, $countryCode, $phoneNumber, $username, $email, $password, $role_id, $school_id) {
        $query = "INSERT INTO users (full_name, country_code, phone_number, username, email, password, role_id, school_id) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt->bind_param("ssssssii", 
            $fullName, 
            $countryCode, 
            $phoneNumber, 
            $username, 
            $email, 
            $hashedPassword, 
            $role_id, 
            $school_id
        );

        return $stmt->execute();
    }

    // Get all users for a specific school (all roles)
    public function getAllUsers($school_id) {
        $query = "
            SELECT u.*, r.name AS role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            WHERE u.school_id = ?
            ORDER BY u.full_name
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $school_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Get users by role name (e.g., 'teacher', 'student', 'schooladmin')
public function getUsersByRoleId($school_id, $role_id) {
    $query = "
        SELECT u.*, r.name AS role_name 
        FROM users u
        JOIN roles r ON u.role_id = r.role_id
        WHERE u.school_id = ? AND u.role_id = ?
        ORDER BY u.full_name
    ";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ii", $school_id, $role_id);
    $stmt->execute();
    return $stmt->get_result();
}


    // Delete user by ID
    public function deleteUser($userId) {
        $query = "DELETE FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    // Get user by ID
    public function getUserById($userId) {
        $query = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Update user basic details
    public function updateUser($userId, $fullName, $email, $phoneNumber) {
        $query = "UPDATE users SET full_name = ?, email = ?, phone_number = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssi", $fullName, $email, $phoneNumber, $userId);
        return $stmt->execute();
    }
}
?>
