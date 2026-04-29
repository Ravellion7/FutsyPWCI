<?php

namespace Model;

class PackCard extends ActiveRecord
{
    private static function getNextId(): int
    {
        $db = static::getDB();
        $sql = 'SELECT COALESCE(MAX(id_packcard), 0) + 1 AS next_id FROM PackCards';
        $result = $db->query($sql);
        $row = $result->fetch_assoc();

        return (int)($row['next_id'] ?? 1);
    }

    public static function create(int $openingId, int $cardId): bool
    {
        $db = static::getDB();
        $nextId = static::getNextId();
        $sql = 'INSERT INTO PackCards (id_packcard, id_opening, id_card) VALUES (?, ?, ?)';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii', $nextId, $openingId, $cardId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}
