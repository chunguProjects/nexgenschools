<?php
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /** 
     * Add new user (password always hashed)
     */
    public function addUser($fullName, $countryCode, $phoneNumber, $username, $email, $password, $role_id) {
        $query = "INSERT INTO users (full_name, country_code, phone_number, username, email, password, role_id) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt->bind_param("ssssssi", 
            $fullName, 
            $countryCode, 
            $phoneNumber, 
            $username, 
            $email, 
            $hashedPassword, 
            $role_id
        );

        return $stmt->execute();
    }

    /**
     * Get all users with their role name
     */
    public function getAllUsers() {
        $query = "
            SELECT u.*, r.name AS role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            ORDER BY u.full_name
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Get users by role
     */
    public function getUsersByRoleId($role_id) {
        $query = "
            SELECT u.*, r.name AS role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            WHERE u.role_id = ?
            ORDER BY u.full_name
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $role_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Delete user
     */
    public function deleteUser($userId) {
        $query = "DELETE FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    /**
     * Get single user
     */
    public function getUserById($userId) {
        $query = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Update user details (with optional password update)
     */
    public function updateUser($userId, $fullName, $email, $phoneNumber, $password = null) {
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET full_name = ?, email = ?, phone_number = ?, password = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssssi", $fullName, $email, $phoneNumber, $hashedPassword, $userId);
        } else {
            $query = "UPDATE users SET full_name = ?, email = ?, phone_number = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sssi", $fullName, $email, $phoneNumber, $userId);
        }
        return $stmt->execute();
    }

    
}
?>
