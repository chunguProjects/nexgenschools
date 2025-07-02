<?php
class ClassGroup {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add new class for a specific school
    public function addClass($name, $description, $teacherId, $schoolId) {
        $query = "INSERT INTO classes (name, description, teacher_id, school_id)
                  VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssii", $name, $description, $teacherId, $schoolId);
        return $stmt->execute();
    }

    // Get all classes (across schools — used only by superadmin if needed)
    public function getAllClasses() {
        $query = "
            SELECT 
                c.class_id,
                c.name AS class_name,
                c.description,
                c.created_at,
                u.user_id AS teacher_id,
                u.full_name AS teacher_name,
                c.school_id
            FROM classes c
            INNER JOIN users u ON c.teacher_id = u.user_id
            ORDER BY c.created_at DESC
        ";
        $result = $this->conn->query($query);

        $classes = [];
        while ($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }
        return $classes;
    }

    // ✅ Get all classes for a specific school
    public function getAllClassesBySchool($schoolId) {
        $query = "
            SELECT 
                c.class_id,
                c.name AS class_name,
                c.description,
                c.created_at,
                u.user_id AS teacher_id,
                u.full_name AS teacher_name
            FROM classes c
            INNER JOIN users u ON c.teacher_id = u.user_id
            WHERE c.school_id = ?
            ORDER BY c.created_at DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $schoolId);
        $stmt->execute();
        $result = $stmt->get_result();

        $classes = [];
        while ($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }
        return $classes;
    }

    // Get single class by ID
    public function getClassById($class_id) {
        $query = "SELECT * FROM classes WHERE class_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    // Determine grading level based on class name
    public function determineGradingLevel($className) {
        if (preg_match('/\d+/', $className, $matches)) {
            $classNumber = (int)$matches[0];
            return ($classNumber <= 2) ? 'Junior' : 'Senior';
        }
        return 'Junior'; // default
    }
    public function countStudentsInClass($classId) {
    $query = "SELECT COUNT(*) AS total FROM students WHERE class_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $classId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'] ?? 0;
}

}

?>
