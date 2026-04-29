<?php

namespace Model;

class TradeCard extends ActiveRecord
{
    private static function getNextId(): int
    {
        $db = static::getDB();
        $sql = 'SELECT COALESCE(MAX(id_tradecard), 0) + 1 AS next_id FROM TradeCards';
        $result = $db->query($sql);
        $row = $result->fetch_assoc();

        return (int)($row['next_id'] ?? 1);
    }

    public static function add(int $tradeId, int $cardId, int $quantity, string $owner): bool
    {
        $db = static::getDB();
        $nextId = static::getNextId();
        $sql = 'INSERT INTO TradeCards (id_tradecard, id_trade, id_card, quantity, owner) VALUES (?, ?, ?, ?, ?)';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiiis', $nextId, $tradeId, $cardId, $quantity, $owner);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function allByTrade(int $tradeId): array
    {
        $db = static::getDB();
        $sql = 'SELECT id_card, quantity, owner FROM TradeCards WHERE id_trade = ?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $tradeId);
        $stmt->execute();

        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();

        return $rows;
    }

    public static function splitBySide(int $tradeId): array
    {
        $rows = static::allByTrade($tradeId);
        $offer = [];
        $request = [];

        foreach ($rows as $row) {
            if (($row['owner'] ?? '') === 'sender') {
                $offer[] = [
                    'id_card' => (int)$row['id_card'],
                    'quantity' => (int)$row['quantity'],
                ];
            }

            if (($row['owner'] ?? '') === 'receiver') {
                $request[] = [
                    'id_card' => (int)$row['id_card'],
                    'quantity' => (int)$row['quantity'],
                ];
            }
        }

        return [
            'offer' => $offer,
            'request' => $request,
        ];
    }

    public static function detailsByTradeIds(array $tradeIds): array
    {
        $tradeIds = array_values(array_filter(array_map('intval', $tradeIds), static function ($id) {
            return $id > 0;
        }));

        if (empty($tradeIds)) {
            return [];
        }

        $db = static::getDB();
        $placeholders = implode(',', array_fill(0, count($tradeIds), '?'));
        $types = str_repeat('i', count($tradeIds));

        $sql = "SELECT tc.id_trade, tc.id_card, tc.quantity, tc.owner, c.rarity, p.name AS player_name, t.country AS team_name
                FROM TradeCards tc
                INNER JOIN Card c ON c.id_card = tc.id_card
                INNER JOIN Player p ON p.id_player = c.id_player
                INNER JOIN Team t ON t.id_team = p.id_team
                WHERE tc.id_trade IN ($placeholders)
                ORDER BY tc.id_trade ASC, tc.id_tradecard ASC";

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$tradeIds);
        $stmt->execute();

        $result = $stmt->get_result();
        $byTrade = [];

        while ($row = $result->fetch_assoc()) {
            $tradeId = (int)$row['id_trade'];
            if (!isset($byTrade[$tradeId])) {
                $byTrade[$tradeId] = [
                    'offer' => [],
                    'request' => [],
                ];
            }

            $item = [
                'id_card' => (int)$row['id_card'],
                'quantity' => (int)$row['quantity'],
                'rarity' => (string)$row['rarity'],
                'player_name' => (string)$row['player_name'],
                'team_name' => (string)$row['team_name'],
            ];

            if (($row['owner'] ?? '') === 'sender') {
                $byTrade[$tradeId]['offer'][] = $item;
            } elseif (($row['owner'] ?? '') === 'receiver') {
                $byTrade[$tradeId]['request'][] = $item;
            }
        }

        $stmt->close();

        return $byTrade;
    }
}
