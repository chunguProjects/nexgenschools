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

     //Get users by role
    
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
    // 1. Remove teacher assignments
    $query1 = "DELETE FROM class_subject_teacher WHERE teacher_id = ?";
    $stmt1 = $this->conn->prepare($query1);
    $stmt1->bind_param("i", $userId);
    $stmt1->execute();

    // 2. Now delete user
    $query2 = "DELETE FROM users WHERE user_id = ?";
    $stmt2 = $this->conn->prepare($query2);
    $stmt2->bind_param("i", $userId);

    return $stmt2->execute();
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

    /**
     * Verify a plaintext password against the stored hash for a user.
     * Used by the self-service "change password" flow before allowing
     * a new password to be set.
     */
    public function verifyPassword($userId, $plainPassword) {
        $query = "SELECT password FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            return false;
        }

        return password_verify($plainPassword, $row['password']);
    }

    /**
     * Change only the password (separate from updateUser so the
     * self-service password form doesn't need to resend name/email/phone).
     */
    public function changePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $hashedPassword, $userId);
        return $stmt->execute();
    }

    /**
     * Save/replace the user's profile photo path.
     */
    public function updateProfilePhoto($userId, $photoPath) {
        $query = "UPDATE users SET profile_photo = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $photoPath, $userId);
        return $stmt->execute();
    }

    /**
     * Stamp last_login = now. Call this from your login controller
     * right after a successful login check.
     */
    public function updateLastLogin($userId) {
        $query = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

}
?>