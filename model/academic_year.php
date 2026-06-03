<?php
class AcademicYear {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ✅ Create a new academic year
    public function create(string $name, string $start_date, string $end_date, bool $is_current = false): int {
        if ($is_current) {
            $this->unsetCurrentYear(); // Ensure only one current year
        }

        $stmt = $this->conn->prepare("
            INSERT INTO academic_years (name, start_date, end_date, is_current)
            VALUES (?, ?, ?, ?)
        ");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $is_currentInt = $is_current ? 1 : 0;
        $stmt->bind_param("sssi", $name, $start_date, $end_date, $is_currentInt);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return $stmt->insert_id;
    }

    // ✅ Update an existing academic year
    public function update(int $id, string $name, string $start_date, string $end_date, bool $is_current = false): bool {
        if ($is_current) {
            $this->unsetCurrentYear(); // Ensure only one current year
        }

        $stmt = $this->conn->prepare("
            UPDATE academic_years
            SET name=?, start_date=?, end_date=?, is_current=?
            WHERE academic_year_id=?
        ");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $is_currentInt = $is_current ? 1 : 0;
        $stmt->bind_param("sssii", $name, $start_date, $end_date, $is_currentInt, $id);

        return $stmt->execute();
    }

    // ✅ Delete an academic year
    public function delete(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM academic_years WHERE academic_year_id=?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // ✅ Get all academic years
    public function getAll(): array {
        $result = $this->conn->query("SELECT * FROM academic_years ORDER BY start_date DESC");
        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ✅ Get current academic year
    public function getCurrentYear(): ?array {
        $result = $this->conn->query("SELECT * FROM academic_years WHERE is_current = 1 LIMIT 1");
        return $result ? $result->fetch_assoc() : null;
    }

    // ✅ Get a single academic year by ID
    public function getById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM academic_years WHERE academic_year_id=? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // ✅ Set a year as current (unsets others)
    public function setCurrentYear(int $id): bool {
        $this->unsetCurrentYear();
        $stmt = $this->conn->prepare("UPDATE academic_years SET is_current = 1 WHERE academic_year_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // ✅ Unset all current years
    private function unsetCurrentYear(): bool {
        return $this->conn->query("UPDATE academic_years SET is_current = 0");
    }
}
?>
