<?php
// model/user.php
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function addUser($fullName, $countryCode, $phoneNumber, $username, $email, $password, $role) {
        $query = "INSERT INTO users (full_name, country_code, phone_number, username, email, password, role) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
    
        // Hash the password and store it in a variable
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
        // Bind the parameters
        $stmt->bind_param("sssssss", $fullName, $countryCode, $phoneNumber, $username, $email, $hashedPassword, $role);
    
        // Execute the query
        return $stmt->execute();
    }
    

    public function getAllUsers() {
        $query = "SELECT * FROM users";
        $result = $this->conn->query($query);
        return $result;
    }

    public function deleteUser($userId) {
        $query = "DELETE FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
}
?>
