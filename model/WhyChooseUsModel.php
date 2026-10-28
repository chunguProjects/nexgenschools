<?php

class WhyChooseUsModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * @param bool $activeOnly When true, only rows with is_active = 1
     *                         are returned (what the homepage should use).
     */
    public function getAll(bool $activeOnly = false): array
    {
        $sql = "SELECT * FROM why_choose_us";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, id ASC";

        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM why_choose_us WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO why_choose_us (title, body, image_path, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            'sssii',
            $data['title'],
            $data['body'],
            $data['image_path'],
            $data['sort_order'],
            $data['is_active']
        );
        return $stmt->execute();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE why_choose_us
             SET title = ?, body = ?, image_path = ?, sort_order = ?, is_active = ?
             WHERE id = ?"
        );
        $stmt->bind_param(
            'sssiii',
            $data['title'],
            $data['body'],
            $data['image_path'],
            $data['sort_order'],
            $data['is_active'],
            $id
        );
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM why_choose_us WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}