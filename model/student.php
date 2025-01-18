<!-- <?php
class Student1 {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function addStudent($firstName, $lastName, $gender, $classId, $dateOfBirth, $contactNumber, $address) {
        $query = "
            INSERT INTO students (first_name, last_name, gender, class_id, date_of_birth, contact_number, address) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bind_param("sssisss", $firstName, $lastName, $gender, $classId, $dateOfBirth, $contactNumber, $address);

        // Execute and return status
        return $stmt->execute();
    }
}
?> -->


<?php
class Student {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function addStudentsToClass($students, $classId) {
        $query = "
            INSERT INTO students (first_name, last_name, gender, class_id) 
            VALUES (?, ?, ?, ?)
        ";
        
        $stmt = $this->conn->prepare($query);

        foreach ($students as $student) {
            $firstName = $student['first_name'];
            $lastName = $student['last_name'];
            $gender = $student['gender'];
            
            // Bind parameters and execute the query for each student
            $stmt->bind_param("sssi", $firstName, $lastName, $gender, $classId);
            if (!$stmt->execute()) {
                return false; // Return false if any insert fails
            }
        }

        return true; // Return true if all inserts succeed
    }

    public function getStudentsByClass($class_id) {
        $query = "SELECT student_id, first_name, last_name FROM students WHERE class_id = ?";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            die("Error in statement preparation: " . $this->conn->error);
        }
        
        $stmt->bind_param('i', $class_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        if (!$result) {
            die("Error in query execution: " . $this->conn->error);
        }
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
}
?>
