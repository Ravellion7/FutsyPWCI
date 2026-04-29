<?php

namespace Model;

class Team extends ActiveRecord
{
    public static function all(): array
    {
        $db = static::getDB();
        $sql = 'SELECT id_team, country, players_amount, `group`, team_fact, flag FROM Team ORDER BY country ASC';
        $result = $db->query($sql);

        $teams = [];
        while ($row = $result->fetch_assoc()) {
            $teams[] = $row;
        }

        return $teams;
    }

    public static function findById(int $teamId): ?array
    {
        $db = static::getDB();
        $sql = 'SELECT id_team, country, players_amount, `group`, team_fact, flag
                FROM Team
                WHERE id_team = ?
                LIMIT 1';

        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $teamId);
        $stmt->execute();

        $result = $stmt->get_result();
        $team = $result->fetch_assoc();
        $stmt->close();

        return $team ?: null;
    }

    public static function rosterWithInventory(int $teamId, int $collectorId): array
    {
        $db = static::getDB();
        $sql = 'SELECT p.id_player,
                       p.name AS player_name,
                       p.shirtnumber,
                       p.position,
                       c.id_card,
                       c.rarity,
                       COALESCE(i.quantity, 0) AS inventory_quantity
                FROM Player p
                INNER JOIN Card c ON c.id_player = p.id_player
                LEFT JOIN Inventory i ON i.id_card = c.id_card AND i.id_collector = ?
                WHERE p.id_team = ?
                ORDER BY p.name ASC';

        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii', $collectorId, $teamId);
        $stmt->execute();

        $result = $stmt->get_result();
        $players = [];
        while ($row = $result->fetch_assoc()) {
            $players[] = $row;
        }

        $stmt->close();

        return $players;
    }

    private static function getNextId(): int
    {
        $db = static::getDB();
        $sql = 'SELECT COALESCE(MAX(id_team), 0) + 1 AS next_id FROM Team';
        $result = $db->query($sql);
        $row = $result->fetch_assoc();

        return (int)($row['next_id'] ?? 1);
    }

    public static function create(array $data): bool
    {
        $db = static::getDB();
        $nextId = static::getNextId();
        $flag = null;

        $sql = 'INSERT INTO Team (id_team, country, players_amount, `group`, team_fact, flag, registered_by) VALUES (?, ?, ?, ?, ?, ?, ?)';
        $stmt = $db->prepare($sql);
        $stmt->bind_param(
            'isissbi',
            $nextId,
            $data['country'],
            $data['players_amount'],
            $data['group'],
            $data['team_fact'],
            $flag,
            $data['registered_by']
        );
        $stmt->send_long_data(5, $data['flag']);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    
    public static function allWithCollectorProgress(int $collectorId): array
    {
        $db = static::getDB();
        $sql = 'SELECT t.id_team, t.country, t.players_amount, t.`group`, t.team_fact, t.flag,
                   COALESCE(COUNT(DISTINCT CASE WHEN i.quantity > 0 THEN c.id_card END), 0) AS owned_cards
            FROM Team t
            LEFT JOIN Player p ON p.id_team = t.id_team
            LEFT JOIN Card c ON c.id_player = p.id_player
            LEFT JOIN Inventory i ON i.id_card = c.id_card AND i.id_collector = ?
            GROUP BY t.id_team
            ORDER BY t.country ASC';

        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $collectorId);
        $stmt->execute();

        $result = $stmt->get_result();
        $teams = [];
        while ($row = $result->fetch_assoc()) {
            $teams[] = $row;
        }
        $stmt->close();

        return $teams;
    }
}
