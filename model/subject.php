<?php
class Subjects {
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    //create a function for adding new subject
    public function addSubject($name, $code, $teacherId) {
        $query = "INSERT INTO subjects (subject_name, subject_code, teacher_id)
        VALUES (?,?,?)";

        //prepare a statement
        $stmt = $this->conn->prepare($query);

        //bind the parameters
        $stmt->bind_param("ssi", $name, $code, $teacherId);

        //executte the query
        return $stmt->execute();
    }
    
    public function getAllSubjects()
    {
        // SQL query to get classes along with teacher details
        $query = "
            SELECT 
                s.subject_id,
                s.subject_name,
                s.subject_code,
                s.created_at,
                u.user_id AS teacher_id,
                u.full_name AS teacher_name
            FROM 
                 subjects s
            INNER JOIN 
                  users u
            ON 
                  s.teacher_id = u.user_id
            ORDER BY 
           s.created_at DESC;

            ";
    
        // Execute the query
        $result = $this->conn->query($query);
    
        // Fetch all records into an associative array
        $subjects = [];
        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row;
        }
    
        return $subjects;
    }


   public function getSubjectsByClass($class_id)
{
    $query = "
        SELECT s.subject_id, s.subject_name, s.subject_code, u.full_name AS teacher_name
        FROM subjects s
        INNER JOIN users u ON s.teacher_id = u.user_id
    ";
    $stmt = $this->conn->prepare($query);

    if (!$stmt) {
        die("Query error: " . $this->conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
    return $subjects;
}

    
}

?>