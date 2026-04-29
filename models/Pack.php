<?php

namespace Model;

class Pack extends ActiveRecord
{
    public static function findByName(string $name): ?array
    {
        $db = static::getDB();
        $sql = 'SELECT id_pack,
                       pack_name AS name,
                       0 AS price,
                       cards_amount AS pack_size,
                       NULL AS drop_rates_json
                FROM Pack
                WHERE pack_name = ?
                LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s', $name);
        $stmt->execute();

        $result = $stmt->get_result();
        $pack = $result->fetch_assoc();
        $stmt->close();

        return $pack ?: null;
    }

    public static function findById(int $packId): ?array
    {
        $db = static::getDB();
        $sql = 'SELECT id_pack,
                       pack_name AS name,
                       0 AS price,
                       cards_amount AS pack_size,
                       NULL AS drop_rates_json
                FROM Pack
                WHERE id_pack = ?
                LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $packId);
        $stmt->execute();

        $result = $stmt->get_result();
        $pack = $result->fetch_assoc();
        $stmt->close();

        return $pack ?: null;
    }
}
