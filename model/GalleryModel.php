<?php

class GalleryModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getAll(): array
    {
        $result = $this->conn->query('SELECT * FROM school_gallery ORDER BY sort_order ASC, gallery_id ASC');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function create(string $imagePath, ?string $caption, int $sortOrder = 0): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO school_gallery (image_path, caption, sort_order) VALUES (?, ?, ?)'
        );
        $stmt->bind_param('ssi', $imagePath, $caption, $sortOrder);
        return $stmt->execute();
    }

    public function delete(int $galleryId): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM school_gallery WHERE gallery_id = ?');
        $stmt->bind_param('i', $galleryId);
        return $stmt->execute();
    }

    public function getById(int $galleryId): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM school_gallery WHERE gallery_id = ?');
        $stmt->bind_param('i', $galleryId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }
}