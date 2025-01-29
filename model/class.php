<?php
class ClassGroup {
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    //create a function for adding new class
    public function addClass($name, $description, $teacherId) {
        $query = "INSERT INTO classes (name, description, teacher_id)
        VALUES (?,?,?)";

        //prepare a statement
        $stmt = $this->conn->prepare($query);

        //bind the parameters
        $stmt->bind_param("ssi", $name, $description, $teacherId);

        //executte the query
        return $stmt->execute();
    }
    
    public function getAllClasses()
    {
        // SQL query to get classes along with teacher details
        $query = "
            SELECT 
                c.class_id,
                c.name AS class_name,
                c.description,
                c.created_at,
                u.user_id AS teacher_id,
                u.full_name AS teacher_name
            FROM 
                classes c
            INNER JOIN 
                users u
            ON 
                c.teacher_id = u.user_id
            ORDER BY 
                c.created_at DESC;
        ";
    
        // Execute the query
        $result = $this->conn->query($query);
    
        // Fetch all records into an associative array
        $classes = [];
        while ($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }
    
        return $classes;
    }
    
    public function getClassById($class_id)
{
    $query = "SELECT * FROM classes WHERE class_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

}

?>