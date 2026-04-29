<?php

namespace Model;

class PackOpening extends ActiveRecord
{
    private static function getNextId(): int
    {
        $db = static::getDB();
        $sql = 'SELECT COALESCE(MAX(id_opening), 0) + 1 AS next_id FROM PackOpening';
        $result = $db->query($sql);
        $row = $result->fetch_assoc();

        return (int)($row['next_id'] ?? 1);
    }

    public static function create(int $collectorId, int $packId): int
    {
        $db = static::getDB();
        $nextId = static::getNextId();

        $sql = 'INSERT INTO PackOpening (id_opening, id_collector, id_pack, open_date) VALUES (?, ?, ?, NOW())';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii', $nextId, $collectorId, $packId);
        $stmt->execute();
        $stmt->close();

        return $nextId;
    }
}
