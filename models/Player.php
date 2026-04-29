<?php

namespace Model;

class Player extends ActiveRecord
{
    public static function allByTeam(int $teamId): array
    {
        $db = static::getDB();
        $sql = 'SELECT id_player, name FROM Player WHERE id_team = ? ORDER BY name ASC';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $teamId);
        $stmt->execute();

        $result = $stmt->get_result();
        $players = [];
        while ($row = $result->fetch_assoc()) {
            $players[] = $row;
        }

        $stmt->close();
        return $players;
    }

    public static function findById(int $idPlayer): ?array
    {
        $db = static::getDB();
        $sql = 'SELECT id_player, name, shirtnumber, position, id_team, photo, registered_by FROM Player WHERE id_player = ? LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $idPlayer);
        $stmt->execute();

        $result = $stmt->get_result();
        $player = $result->fetch_assoc();

        $stmt->close();
        return $player ?: null;
    }

    public static function updateById(int $idPlayer, array $data): bool
    {
        $db = static::getDB();

        if (array_key_exists('photo', $data) && $data['photo'] !== null) {
            $photo = null;
            $sql = 'UPDATE Player SET name = ?, shirtnumber = ?, position = ?, id_team = ?, photo = ?, registered_by = ? WHERE id_player = ?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param(
                'sisibii',
                $data['name'],
                $data['shirtnumber'],
                $data['position'],
                $data['id_team'],
                $photo,
                $data['registered_by'],
                $idPlayer
            );
            $stmt->send_long_data(4, $data['photo']);
        } else {
            $sql = 'UPDATE Player SET name = ?, shirtnumber = ?, position = ?, id_team = ?, registered_by = ? WHERE id_player = ?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param(
                'sisiii',
                $data['name'],
                $data['shirtnumber'],
                $data['position'],
                $data['id_team'],
                $data['registered_by'],
                $idPlayer
            );
        }

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    private static function getNextId(): int
    {
        $db = static::getDB();
        $sql = 'SELECT COALESCE(MAX(id_player), 0) + 1 AS next_id FROM Player';
        $result = $db->query($sql);
        $row = $result->fetch_assoc();

        return (int)($row['next_id'] ?? 1);
    }

    public static function create(array $data): ?int
    {
        $db = static::getDB();
        $nextId = static::getNextId();
        $photo = null;

        $sql = 'INSERT INTO Player (id_player, name, shirtnumber, position, id_team, photo, registered_by) VALUES (?, ?, ?, ?, ?, ?, ?)';
        $stmt = $db->prepare($sql);
        $stmt->bind_param(
            'isisibi',
            $nextId,
            $data['name'],
            $data['shirtnumber'],
            $data['position'],
            $data['id_team'],
            $photo,
            $data['registered_by']
        );
        $stmt->send_long_data(5, $data['photo']);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok ? $nextId : null;
    }

    public static function deleteById(int $idPlayer): bool
    {
        $db = static::getDB();

        $sql = 'DELETE FROM Player WHERE id_player = ?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $idPlayer);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();

        return $affectedRows > 0;
    }
}
