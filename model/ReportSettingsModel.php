<?php
class ReportSettingsModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get settings for a specific school
    public function getSettings($schoolId) {
        $query = "SELECT * FROM report_settings WHERE school_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $schoolId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // return as associative array
    }

    // Insert or update report settings
    public function saveSettings($schoolId, $data) {
        $existing = $this->getSettings($schoolId);

        if ($existing) {
            $query = "UPDATE report_settings 
                      SET opening_date = ?, closing_date = ?, head_teacher_name = ?,
                          fee_boarding = ?, fee_self_boarding = ?, 
                          fee_uniform = ?, fee_registration = ?, include_fees = ?
                      WHERE school_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sssiiisii",
                $data['opening_date'], $data['closing_date'], $data['head_teacher_name'],
                $data['fee_boarding'], $data['fee_self_boarding'],
                $data['fee_uniform'], $data['fee_registration'],
                $data['include_fees'], $schoolId
            );
        } else {
            $query = "INSERT INTO report_settings 
                      (school_id, opening_date, closing_date, head_teacher_name, 
                       fee_boarding, fee_self_boarding, fee_uniform, fee_registration, include_fees)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("isssiiisi",
                $schoolId, $data['opening_date'], $data['closing_date'], $data['head_teacher_name'],
                $data['fee_boarding'], $data['fee_self_boarding'],
                $data['fee_uniform'], $data['fee_registration'], $data['include_fees']
            );
        }

        return $stmt->execute();
    }
}
