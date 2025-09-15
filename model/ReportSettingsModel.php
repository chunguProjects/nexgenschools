<?php
class ReportSettingsModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get settings (only one row in single-tenant setup)
    public function getSettings() {
        $query = "SELECT * FROM settings LIMIT 1";
        $result = $this->conn->query($query);
        return $result->fetch_assoc();
    }

    // Insert or update report settings
    public function saveSettings($data) {
        $existing = $this->getSettings();

        if ($existing) {
            $query = "UPDATE settings 
                      SET opening_date = ?, closing_date = ?, head_teacher_name = ?,
                          fee_boarding = ?, fee_self_boarding = ?, 
                          fee_uniform = ?, fee_registration = ?, include_fees = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                "sssiiiis",
                $data['opening_date'], 
                $data['closing_date'], 
                $data['head_teacher_name'],
                $data['fee_boarding'], 
                $data['fee_self_boarding'],
                $data['fee_uniform'], 
                $data['fee_registration'],
                $data['include_fees']
            );
        } else {
            $query = "INSERT INTO settings 
                      (opening_date, closing_date, head_teacher_name, 
                       fee_boarding, fee_self_boarding, fee_uniform, fee_registration, include_fees)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                "sssiiiis",
                $data['opening_date'], 
                $data['closing_date'], 
                $data['head_teacher_name'],
                $data['fee_boarding'], 
                $data['fee_self_boarding'],
                $data['fee_uniform'], 
                $data['fee_registration'],
                $data['include_fees']
            );
        }

        return $stmt->execute();
    }
}
