
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

    public function getStudentsByClass($class_id)
{
    $query = "SELECT student_id, first_name, last_name FROM students WHERE class_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
  
}
?>
