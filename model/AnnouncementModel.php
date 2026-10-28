<?php

class AnnouncementModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getAll(bool $onlyPublished = false): array
    {
        $sql = 'SELECT * FROM announcements';
        if ($onlyPublished) {
            $sql .= ' WHERE is_published = 1';
        }
        $sql .= ' ORDER BY created_at DESC';
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM announcements WHERE announcement_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function create(string $title, string $body, bool $isPublished): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO announcements (title, body, is_published) VALUES (?, ?, ?)'
        );
        $published = $isPublished ? 1 : 0;
        $stmt->bind_param('ssi', $title, $body, $published);
        return $stmt->execute();
    }

    public function update(int $id, string $title, string $body, bool $isPublished): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE announcements SET title = ?, body = ?, is_published = ? WHERE announcement_id = ?'
        );
        $published = $isPublished ? 1 : 0;
        $stmt->bind_param('ssii', $title, $body, $published, $id);
        return $stmt->execute();
    }

    public function togglePublished(int $id): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE announcements SET is_published = NOT is_published WHERE announcement_id = ?'
        );
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM announcements WHERE announcement_id = ?');
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    public function getPaginatedPublished($limit,$offset)
{
    $stmt=$this->conn->prepare("
        SELECT *
        FROM announcements
        WHERE is_published = 1
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ");

    $stmt->bind_param(
        "ii",
        $limit,
        $offset
    );

    $stmt->execute();

    return $stmt->get_result()
                ->fetch_all(MYSQLI_ASSOC);
}



public function countPublished()
{
    $result=$this->conn->query("
        SELECT COUNT(*) total
        FROM announcements
        WHERE is_published = 1
    ");

    return $result->fetch_assoc()['total'] ?? 0;
}

}