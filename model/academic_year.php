<?php
class AcademicYear {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ✅ Create new academic year
    public function create($name, $start_date, $end_date, $is_current) {
        $stmt = $this->conn->prepare("
            INSERT INTO academic_years (name, start_date, end_date, is_current)
            VALUES (?, ?, ?, ?)
        ");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        $stmt->bind_param("sssi", $name, $start_date, $end_date, $is_current);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        return $stmt->insert_id;
    }

    // ✅ Update existing academic year
    public function update($id, $name, $start_date, $end_date, $is_current) {
        $stmt = $this->conn->prepare("
            UPDATE academic_years
            SET name=?, start_date=?, end_date=?, is_current=?
            WHERE academic_year_id=?
        ");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        $stmt->bind_param("sssii", $name, $start_date, $end_date, $is_current, $id);
        return $stmt->execute();
    }

    // ✅ Delete academic year
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM academic_years WHERE academic_year_id=?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // ✅ Get all years
    public function getAll() {
        $result = $this->conn->query("SELECT * FROM academic_years ORDER BY start_date DESC");
        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }
        return $result;
    }

    // ✅ Get current academic year (very useful for enrollment)
    public function getCurrentYear() {
        $stmt = $this->conn->query("SELECT * FROM academic_years WHERE is_current = 1 LIMIT 1");
        return $stmt ? $stmt->fetch_assoc() : null;
    }

    // ✅ Get a single year by ID
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM academic_years WHERE academic_year_id=? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
