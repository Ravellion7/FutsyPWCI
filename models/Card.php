<?php

namespace Model;

class Card extends ActiveRecord
{
    private static function getNextId(): int
    {
        $db = static::getDB();
        $sql = 'SELECT COALESCE(MAX(id_card), 0) + 1 AS next_id FROM Card';
        $result = $db->query($sql);
        $row = $result->fetch_assoc();

        return (int)($row['next_id'] ?? 1);
    }

    public static function createForPlayer(int $playerId, string $rarity, ?string $cardImage = null): bool
    {
        $db = static::getDB();
        $nextId = static::getNextId();
        $image = null;
        $sql = 'INSERT INTO Card (id_card, id_player, rarity, card_image) VALUES (?, ?, ?, ?)';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iisb', $nextId, $playerId, $rarity, $image);

        if ($cardImage !== null) {
            $stmt->send_long_data(3, $cardImage);
        }

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function randomByRarity(string $rarity): ?array
    {
        $db = static::getDB();
        $sql = 'SELECT c.id_card, c.id_player, c.rarity, c.card_image,
                       p.name AS player_name,
                       t.country AS team_name
                FROM Card c
                LEFT JOIN Player p ON p.id_player = c.id_player
                LEFT JOIN Team t ON t.id_team = p.id_team
                WHERE c.rarity = ?
                ORDER BY RAND()
                LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s', $rarity);
        $stmt->execute();

        $result = $stmt->get_result();
        $card = $result->fetch_assoc();
        $stmt->close();

        return $card ?: null;
    }

    public static function randomAny(): ?array
    {
        $db = static::getDB();
        $sql = 'SELECT c.id_card, c.id_player, c.rarity, c.card_image,
                       p.name AS player_name,
                       t.country AS team_name
                FROM Card c
                LEFT JOIN Player p ON p.id_player = c.id_player
                LEFT JOIN Team t ON t.id_team = p.id_team
                ORDER BY RAND()
                LIMIT 1';
        $result = $db->query($sql);
        $card = $result->fetch_assoc();

        return $card ?: null;
    }

    public static function cardsByRarities(array $rarities = []): array
    {
        $db = static::getDB();

        $sql = 'SELECT c.id_card, c.rarity, c.id_player,
                       p.name AS player_name,
                       t.country AS team_name
                FROM Card c
                LEFT JOIN Player p ON p.id_player = c.id_player
                LEFT JOIN Team t ON t.id_team = p.id_team';

        if (!empty($rarities)) {
            $allowedRarities = [];
            foreach ($rarities as $rarity) {
                if (!is_string($rarity) || $rarity === '') {
                    continue;
                }

                $allowedRarities[] = $db->real_escape_string($rarity);
            }

            if (!empty($allowedRarities)) {
                $quotedRarities = array_map(static function (string $rarity): string {
                    return "'" . $rarity . "'";
                }, array_values(array_unique($allowedRarities)));

                $sql .= ' WHERE c.rarity IN (' . implode(',', $quotedRarities) . ')';
            }
        }

        $sql .= ' ORDER BY c.rarity ASC, c.id_card ASC';

        $result = $db->query($sql);
        if ($result === false) {
            return [];
        }

        $cardsByRarity = [];
        while ($row = $result->fetch_assoc()) {
            $rarity = (string)($row['rarity'] ?? '');
            if ($rarity === '') {
                continue;
            }

            if (!isset($cardsByRarity[$rarity])) {
                $cardsByRarity[$rarity] = [];
            }

            $cardsByRarity[$rarity][] = $row;
        }

        return $cardsByRarity;
    }

    public static function imagesByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $db = static::getDB();
        $safeIds = array_map('intval', $ids);
        $safeIds = array_values(array_filter($safeIds, static fn (int $id): bool => $id > 0));

        if (empty($safeIds)) {
            return [];
        }

        $in = implode(',', $safeIds);
        $sql = 'SELECT id_card, card_image
                FROM Card
                WHERE id_card IN (' . $in . ')';

        $result = $db->query($sql);
        if ($result === false) {
            return [];
        }

        $images = [];
        while ($row = $result->fetch_assoc()) {
            $images[(int)$row['id_card']] = $row['card_image'] ?? null;
        }

        return $images;
    }

    public static function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $db = static::getDB();
        $safeIds = array_map('intval', $ids);
        $in = implode(',', $safeIds);

            $sql = "SELECT c.id_card, c.rarity, c.id_player,
                       p.name AS player_name,
                       t.country AS team_name
                FROM Card c
                LEFT JOIN Player p ON p.id_player = c.id_player
                LEFT JOIN Team t ON t.id_team = p.id_team
                WHERE c.id_card IN ($in)";

        $result = $db->query($sql);

        $cards = [];
        while ($row = $result->fetch_assoc()) {
            $cards[(int)$row['id_card']] = $row;
        }

        return $cards;
    }

    public static function catalog(): array
    {
        $db = static::getDB();
        $sql = 'SELECT c.id_card, c.rarity,
                       p.name AS player_name,
                       t.country AS team_name
                FROM Card c
                LEFT JOIN Player p ON p.id_player = c.id_player
                LEFT JOIN Team t ON t.id_team = p.id_team
                ORDER BY p.name ASC, c.id_card ASC';

        $result = $db->query($sql);
        if ($result === false) {
            return [];
        }

        $cards = [];
        while ($row = $result->fetch_assoc()) {
            $cards[] = $row;
        }

        return $cards;
    }

    public static function findImageById(int $cardId): ?array
    {
        $db = static::getDB();
        $sql = 'SELECT id_card, card_image FROM Card WHERE id_card = ? LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $cardId);
        $stmt->execute();

        $result = $stmt->get_result();
        $card = $result->fetch_assoc();
        $stmt->close();

        return $card ?: null;
    }

    public static function findByPlayerId(int $playerId): ?array
    {
        $db = static::getDB();
        $sql = 'SELECT id_card, id_player, rarity, card_image
                FROM Card
                WHERE id_player = ?
                LIMIT 1';

        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $playerId);
        $stmt->execute();

        $result = $stmt->get_result();
        $card = $result->fetch_assoc();
        $stmt->close();

        return $card ?: null;
    }

    public static function updateRarityByPlayerId(int $playerId, string $rarity): bool
    {
        $db = static::getDB();
        $sql = 'UPDATE Card SET rarity = ? WHERE id_player = ?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('si', $rarity, $playerId);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();

        return $affectedRows > 0;
    }

    public static function deleteByPlayerId(int $playerId): bool
    {
        $db = static::getDB();
        $sql = 'DELETE FROM Card WHERE id_player = ?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $playerId);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();

        return $affectedRows > 0;
    }
}
