<?php
class GeneralSettingsModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get settings for a specific school
    public function getSettings($schoolId) {
        $query = "SELECT * FROM school_settings WHERE school_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $schoolId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc() ?: null;
    }

    // Insert new settings (logo_path can be null)
    public function insertSettings($data) {
        $query = "INSERT INTO school_settings 
            (school_id, logo_path, address, contact_email, contact_phone, motto)
            VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        // Bind params - logo_path may be null
        $logo_path = $data['logo_path'] ?? null;

        $stmt->bind_param(
            "isssss",
            $data['school_id'],
            $logo_path,
            $data['address'],
            $data['contact_email'],
            $data['contact_phone'],
            $data['motto']
        );

        return $stmt->execute();
    }

    // Update existing settings, logo_path optional (update only if set)
    public function updateSettings($data) {
        if (isset($data['logo_path'])) {
            // Update including logo_path
            $query = "UPDATE school_settings 
                      SET logo_path = ?, address = ?, contact_email = ?, contact_phone = ?, motto = ?
                      WHERE school_id = ?";
            $stmt = $this->conn->prepare($query);

            $stmt->bind_param(
                "sssssi",
                $data['logo_path'],
                $data['address'],
                $data['contact_email'],
                $data['contact_phone'],
                $data['motto'],
                $data['school_id']
            );
        } else {
            // Update without logo_path
            $query = "UPDATE school_settings 
                      SET address = ?, contact_email = ?, contact_phone = ?, motto = ?
                      WHERE school_id = ?";
            $stmt = $this->conn->prepare($query);

            $stmt->bind_param(
                "ssssi",
                $data['address'],
                $data['contact_email'],
                $data['contact_phone'],
                $data['motto'],
                $data['school_id']
            );
        }

        return $stmt->execute();
    }

    // Check if settings exist
    public function exists($schoolId) {
        $query = "SELECT id FROM school_settings WHERE school_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $schoolId);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
}
