<?php

namespace Model;

class Trade extends ActiveRecord
{
    private static function inventoryCountsByCollector(int $collectorId): array
    {
        $counts = [];
        $items = Inventory::allByCollector($collectorId);

        foreach ($items as $item) {
            $cardId = (int)($item['id_card'] ?? 0);
            $quantity = (int)($item['quantity'] ?? 0);

            if ($cardId < 1 || $quantity < 1) {
                continue;
            }

            $counts[$cardId] = $quantity;
        }

        return $counts;
    }

    private static function hasEnoughRequestedCards(array $inventoryCounts, array $requestItems): bool
    {
        foreach ($requestItems as $item) {
            $cardId = (int)($item['id_card'] ?? 0);
            $quantity = (int)($item['quantity'] ?? 0);

            if ($cardId < 1 || $quantity < 1) {
                return false;
            }

            if (($inventoryCounts[$cardId] ?? 0) < $quantity) {
                return false;
            }
        }

        return true;
    }

    private static function formatNotificationItems(array $items): string
    {
        if (empty($items)) {
            return 'una estampa';
        }

        $parts = [];
        foreach ($items as $item) {
            $name = (string)($item['player_name'] ?? 'Estampa');
            $quantity = (int)($item['quantity'] ?? 1);
            $parts[] = $quantity > 1 ? ($name . ' x' . $quantity) : $name;
        }

        if (count($parts) === 1) {
            return $parts[0];
        }

        if (count($parts) === 2) {
            return $parts[0] . ' y ' . $parts[1];
        }

        $last = array_pop($parts);
        return implode(', ', $parts) . ' y ' . $last;
    }

    public static function acceptedNotificationsForCollector(int $collectorId, int $limit = 5): array
    {
        $safeLimit = max(1, min(20, $limit));
        $db = static::getDB();
        $status = 'accepted';

        $sql = 'SELECT id_trade, id_sender, id_receiver, updated_at
                FROM `Trade`
                WHERE status = ?
                  AND (id_sender = ? OR id_receiver = ?)
                ORDER BY updated_at DESC
                LIMIT ' . $safeLimit;

        $stmt = $db->prepare($sql);
        $stmt->bind_param('sii', $status, $collectorId, $collectorId);
        $stmt->execute();

        $result = $stmt->get_result();
        $trades = [];
        while ($row = $result->fetch_assoc()) {
            $trades[] = $row;
        }
        $stmt->close();

        if (empty($trades)) {
            return [];
        }

        $tradeIds = array_map(static function ($trade) {
            return (int)($trade['id_trade'] ?? 0);
        }, $trades);

        $placeholders = implode(',', array_fill(0, count($tradeIds), '?'));
        $types = str_repeat('i', count($tradeIds));
        $cardsSql = "SELECT tc.id_trade, tc.owner, tc.quantity, p.name AS player_name
                     FROM TradeCards tc
                     INNER JOIN Card c ON c.id_card = tc.id_card
                     INNER JOIN Player p ON p.id_player = c.id_player
                     WHERE tc.id_trade IN ($placeholders)
                     ORDER BY tc.id_trade ASC, tc.id_tradecard ASC";

        $cardsStmt = $db->prepare($cardsSql);
        $cardsStmt->bind_param($types, ...$tradeIds);
        $cardsStmt->execute();

        $cardsResult = $cardsStmt->get_result();
        $cardsByTrade = [];
        while ($row = $cardsResult->fetch_assoc()) {
            $tradeId = (int)($row['id_trade'] ?? 0);
            if (!isset($cardsByTrade[$tradeId])) {
                $cardsByTrade[$tradeId] = [
                    'sender' => [],
                    'receiver' => [],
                ];
            }

            $owner = (string)($row['owner'] ?? '');
            if ($owner === 'sender' || $owner === 'receiver') {
                $cardsByTrade[$tradeId][$owner][] = [
                    'player_name' => (string)($row['player_name'] ?? 'Estampa'),
                    'quantity' => (int)($row['quantity'] ?? 1),
                ];
            }
        }
        $cardsStmt->close();

        $notifications = [];
        foreach ($trades as $trade) {
            $tradeId = (int)($trade['id_trade'] ?? 0);
            $isSender = (int)($trade['id_sender'] ?? 0) === $collectorId;
            $tradeCards = $cardsByTrade[$tradeId] ?? ['sender' => [], 'receiver' => []];

            $received = $isSender ? $tradeCards['receiver'] : $tradeCards['sender'];
            $given = $isSender ? $tradeCards['sender'] : $tradeCards['receiver'];

            $notifications[] = [
                'id_trade' => $tradeId,
                'message' => 'Recibiste a ' . static::formatNotificationItems($received) . ' por ' . static::formatNotificationItems($given) . '.',
                'traded_at' => $trade['updated_at'] ?? null,
            ];
        }

        return $notifications;
    }

    private static function getNextId(): int
    {
        $db = static::getDB();
        $sql = 'SELECT COALESCE(MAX(id_trade), 0) + 1 AS next_id FROM `Trade`';
        $result = $db->query($sql);
        $row = $result->fetch_assoc();

        return (int)($row['next_id'] ?? 1);
    }

    public static function create(int $senderId, ?int $receiverId): int
    {
        $db = static::getDB();
        $nextId = static::getNextId();
        $status = 'pending';

        if ($receiverId === null) {
            $sql = 'INSERT INTO `Trade` (id_trade, id_sender, id_receiver, status, created_at, updated_at) VALUES (?, ?, NULL, ?, NOW(), NOW())';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('iis', $nextId, $senderId, $status);
        } else {
            $sql = 'INSERT INTO `Trade` (id_trade, id_sender, id_receiver, status, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('iiis', $nextId, $senderId, $receiverId, $status);
        }

        $stmt->execute();
        $stmt->close();

        return $nextId;
    }

    public static function findById(int $tradeId): ?array
    {
        $db = static::getDB();
        $sql = 'SELECT id_trade, id_sender, id_receiver, status, created_at, updated_at
                FROM `Trade`
                WHERE id_trade = ?
                LIMIT 1';

        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $tradeId);
        $stmt->execute();

        $result = $stmt->get_result();
        $trade = $result->fetch_assoc();
        $stmt->close();

        return $trade ?: null;
    }

    public static function feed(int $collectorId): array
    {
        $db = static::getDB();
        $status = 'pending';
        $sql = 'SELECT t.id_trade, t.id_sender, t.id_receiver, t.status, t.created_at, c.fullname AS sender_name, c.avatar AS sender_avatar
                FROM `Trade` t
                INNER JOIN Coleccionista c ON c.id_collector = t.id_sender
                WHERE t.status = ?
                  AND t.id_sender <> ?
                  AND (t.id_receiver IS NULL OR t.id_receiver = ?)
                ORDER BY t.created_at DESC';

        $stmt = $db->prepare($sql);
        $stmt->bind_param('sii', $status, $collectorId, $collectorId);
        $stmt->execute();

        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();

        return $rows;
    }

    public static function feedMine(int $collectorId): array
    {
        $db = static::getDB();
    $status = 'pending';
    $sql = 'SELECT t.id_trade, t.id_sender, t.id_receiver, t.status, t.created_at, c.fullname AS sender_name, c.avatar AS sender_avatar
                FROM `Trade` t
                INNER JOIN Coleccionista c ON c.id_collector = t.id_sender
        WHERE t.id_sender = ?
          AND t.status = ?
                ORDER BY t.created_at DESC';

    $stmt = $db->prepare($sql);
    $stmt->bind_param('is', $collectorId, $status);
        $stmt->execute();

        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();

        return $rows;
    }

    public static function feedCompletable(int $collectorId): array
    {
        $feed = static::feed($collectorId);
        if (empty($feed)) {
            return [];
        }

        $tradeIds = array_map(static function (array $trade): int {
            return (int)($trade['id_trade'] ?? 0);
        }, $feed);

        $tradeCards = TradeCard::detailsByTradeIds($tradeIds);
        $inventoryCounts = static::inventoryCountsByCollector($collectorId);

        $rows = [];
        foreach ($feed as $trade) {
            $tradeId = (int)($trade['id_trade'] ?? 0);
            $requestItems = $tradeCards[$tradeId]['request'] ?? [];

            if (static::hasEnoughRequestedCards($inventoryCounts, $requestItems)) {
                $rows[] = $trade;
            }
        }

        return $rows;
    }

    public static function feedCompleted(int $collectorId): array
    {
        $db = static::getDB();
        $status = 'accepted';
        $sql = 'SELECT t.id_trade, t.id_sender, t.id_receiver, t.status, t.created_at, c.fullname AS sender_name, c.avatar AS sender_avatar
                FROM `Trade` t
                INNER JOIN Coleccionista c ON c.id_collector = t.id_sender
                WHERE t.status = ?
                  AND (t.id_sender = ? OR t.id_receiver = ?)
                ORDER BY t.updated_at DESC, t.created_at DESC';

        $stmt = $db->prepare($sql);
        $stmt->bind_param('sii', $status, $collectorId, $collectorId);
        $stmt->execute();

        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();

        return $rows;
    }

    public static function feedByFilter(int $collectorId, string $filter): array
    {
        return match ($filter) {
            'mine' => static::feedMine($collectorId),
            'received' => static::feedCompletable($collectorId),
            'completed' => static::feedCompleted($collectorId),
            default => static::feed($collectorId),
        };
    }

    public static function updateStatus(int $tradeId, string $status): bool
    {
        $db = static::getDB();
        $sql = 'UPDATE `Trade` SET status = ?, updated_at = NOW() WHERE id_trade = ?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('si', $status, $tradeId);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();

        return $affectedRows > 0;
    }

    public static function reserveReceiverIfOpen(int $tradeId, int $receiverId): bool
    {
        $db = static::getDB();
        $status = 'pending';
        $sql = 'UPDATE `Trade`
                SET id_receiver = ?
                WHERE id_trade = ?
                  AND status = ?
                  AND id_receiver IS NULL';

        $stmt = $db->prepare($sql);
        $stmt->bind_param('iis', $receiverId, $tradeId, $status);
        $stmt->execute();
        $stmt->close();

        return true;
    }
}
