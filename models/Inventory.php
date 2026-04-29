<?php

namespace Model;

class Inventory extends ActiveRecord
{
    public static function uniqueOwnedCardsCount(int $collectorId): int
    {
        $db = static::getDB();
        $sql = 'SELECT COALESCE(COUNT(*), 0) AS unique_cards
                FROM Inventory
                WHERE id_collector = ? AND quantity > 0';

        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $collectorId);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc() ?: [];
        $stmt->close();

        return (int)($row['unique_cards'] ?? 0);
    }

    public static function allByCollector(int $collectorId): array
    {
        $db = static::getDB();
        $sql = 'SELECT i.id_card, i.quantity,
                       c.rarity,
                       p.name AS player_name,
                       t.country AS team_name
                FROM Inventory i
                INNER JOIN Card c ON c.id_card = i.id_card
                LEFT JOIN Player p ON p.id_player = c.id_player
                LEFT JOIN Team t ON t.id_team = p.id_team
                WHERE i.id_collector = ?
                ORDER BY c.rarity ASC, p.name ASC';

        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $collectorId);
        $stmt->execute();

        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        $stmt->close();

        return $items;
    }

    public static function duplicateCardsByCollector(int $collectorId): array
    {
        $db = static::getDB();
        $sql = 'SELECT i.id_card, i.quantity,
                       c.rarity,
                       c.card_image,
                       p.name AS player_name,
                       t.country AS team_name
                FROM Inventory i
                INNER JOIN Card c ON c.id_card = i.id_card
                LEFT JOIN Player p ON p.id_player = c.id_player
                LEFT JOIN Team t ON t.id_team = p.id_team
                WHERE i.id_collector = ? AND i.quantity > 1
                ORDER BY i.quantity DESC, c.rarity ASC, p.name ASC';

        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $collectorId);
        $stmt->execute();

        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        $stmt->close();

        return $items;
    }

    public static function addCard(int $collectorId, int $cardId, int $quantity): bool
    {
        $db = static::getDB();
        $sql = 'INSERT INTO Inventory (id_collector, id_card, quantity)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)';

        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii', $collectorId, $cardId, $quantity);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function removeCard(int $collectorId, int $cardId, int $quantity): bool
    {
        $db = static::getDB();
        $sql = 'UPDATE Inventory
                SET quantity = quantity - ?
                WHERE id_collector = ? AND id_card = ? AND quantity >= ?';

        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiii', $quantity, $collectorId, $cardId, $quantity);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected < 1) {
            return false;
        }

        $cleanup = $db->prepare('DELETE FROM Inventory WHERE id_collector = ? AND id_card = ? AND quantity <= 0');
        $cleanup->bind_param('ii', $collectorId, $cardId);
        $cleanup->execute();
        $cleanup->close();

        return true;
    }

    public static function hasEnoughCards(int $collectorId, array $items): bool
    {
        $db = static::getDB();

        foreach ($items as $item) {
            $cardId = (int)($item['id_card'] ?? 0);
            $quantity = (int)($item['quantity'] ?? 0);

            if ($cardId < 1 || $quantity < 1) {
                return false;
            }

            $sql = 'SELECT quantity FROM Inventory WHERE id_collector = ? AND id_card = ? LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('ii', $collectorId, $cardId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if (!$row || (int)$row['quantity'] < $quantity) {
                return false;
            }
        }

        return true;
    }

    public static function deleteByCardId(int $cardId): bool
    {
        $db = static::getDB();
        $sql = 'DELETE FROM Inventory WHERE id_card = ?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $cardId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}
