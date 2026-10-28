<?php
class GeneralSettingsModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

     /**
     * Get active academic year
     */
    public function getActiveAcademicYear(): ?array {
        $query = "SELECT * FROM academic_years WHERE is_current = 1 LIMIT 1";
        $result = $this->conn->query($query);
        if ($result && $row = $result->fetch_assoc()) {
            return $row;
        }
        return null;
    }
    // Get settings (single-tenant, always the first row)
    public function getSettings()
{
    $result = $this->conn->query("SELECT * FROM settings LIMIT 1");

    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    // No row exists - create a default one automatically
    $stmt = $this->conn->prepare(
        "INSERT INTO settings (school_name) VALUES (?)"
    );

    $defaultName = 'NextGen Schools';
    $stmt->bind_param("s", $defaultName);
    $stmt->execute();

    // Return the newly created row
    $id = $this->conn->insert_id;

    $stmt = $this->conn->prepare(
        "SELECT * FROM settings WHERE id = ? LIMIT 1"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

    // Insert new settings (logo_path can be null)
    public function insertSettings($data) {
        $query = "INSERT INTO settings 
            (school_name, logo_path, address, contact_email, contact_phone, motto)
            VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        $logo_path = $data['logo_path'] ?? null;

        $stmt->bind_param(
            "ssssss",
            $data['school_name'],
            $logo_path,
            $data['address'],
            $data['contact_email'],
            $data['contact_phone'],
            $data['motto']
        );

        return $stmt->execute();
    }

    // Update existing settings, logo_path optional
    public function updateSettings($data) {
        if (isset($data['logo_path'])) {
            $query = "UPDATE settings 
                      SET school_name = ?, logo_path = ?, address = ?, contact_email = ?, contact_phone = ?, motto = ?
                      WHERE id = 1";
            $stmt = $this->conn->prepare($query);

            $stmt->bind_param(
                "ssssss",
                $data['school_name'],
                $data['logo_path'],
                $data['address'],
                $data['contact_email'],
                $data['contact_phone'],
                $data['motto']
            );
        } else {
            $query = "UPDATE settings 
                      SET school_name = ?, address = ?, contact_email = ?, contact_phone = ?, motto = ?
                      WHERE id = 1";
            $stmt = $this->conn->prepare($query);

            $stmt->bind_param(
                "sssss",
                $data['school_name'],
                $data['address'],
                $data['contact_email'],
                $data['contact_phone'],
                $data['motto']
            );
        }

        return $stmt->execute();
    }

    // Check if settings exist
    public function exists() {
        $query = "SELECT id FROM settings WHERE id = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
}
