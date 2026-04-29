<?php

namespace Model;

class Collector extends ActiveRecord
{
    public static function topCollectors(int $limit = 10): array
    {
        $safeLimit = max(1, min(50, $limit));
        $db = static::getDB();

        $sql = 'SELECT c.id_collector,
                       c.fullname,
                       c.avatar,
                       COALESCE(SUM(i.quantity), 0) AS total_cards
                FROM Coleccionista c
                LEFT JOIN Inventory i ON i.id_collector = c.id_collector
                WHERE c.rol = "collector"
                GROUP BY c.id_collector, c.fullname, c.avatar
                ORDER BY total_cards DESC, c.id_collector ASC
                LIMIT ' . $safeLimit;

        $result = $db->query($sql);
        if ($result === false) {
            return [];
        }

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        return $items;
    }

    public static function findById(int $collectorId): ?array
    {
        $db = static::getDB();
        $sql = 'SELECT id_collector, fullname, avatar, pack_balance AS packs_balance, last_pack_claim_at, updated_password_at FROM Coleccionista WHERE id_collector = ? LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $collectorId);
        $stmt->execute();

        $result = $stmt->get_result();
        $collector = $result->fetch_assoc();

        $stmt->close();

        return $collector ?: null;
    }

    public static function leaderboardStats(int $collectorId): array
    {
        $db = static::getDB();

        $sql = 'SELECT COALESCE(SUM(i.quantity), 0) AS total_cards
                FROM Inventory i
                WHERE i.id_collector = ?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $collectorId);
        $stmt->execute();

        $result = $stmt->get_result();
        $collectorTotals = $result->fetch_assoc() ?: [];
        $stmt->close();

        $totalCards = (int)($collectorTotals['total_cards'] ?? 0);

        $rankingSql = 'SELECT 1 + COUNT(*) AS ranking
                       FROM (
                           SELECT c.id_collector, COALESCE(SUM(i.quantity), 0) AS total_cards
                           FROM Coleccionista c
                           LEFT JOIN Inventory i ON i.id_collector = c.id_collector
                           GROUP BY c.id_collector
                       ) totals
                       WHERE totals.total_cards > ?';
        $rankingStmt = $db->prepare($rankingSql);
        $rankingStmt->bind_param('i', $totalCards);
        $rankingStmt->execute();

        $rankingResult = $rankingStmt->get_result();
        $rankingRow = $rankingResult->fetch_assoc() ?: [];
        $rankingStmt->close();

        return [
            'total_cards' => $totalCards,
            'ranking' => (int)($rankingRow['ranking'] ?? 1),
        ];
    }

    public static function updateAvatar(int $collectorId, ?string $avatarBinary): bool
    {
        $db = static::getDB();

        if ($avatarBinary === null || $avatarBinary === '') {
            $sql = 'UPDATE Coleccionista SET avatar = NULL WHERE id_collector = ?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i', $collectorId);
            $ok = $stmt->execute();
            $stmt->close();

            return $ok;
        }

        $sql = 'UPDATE Coleccionista SET avatar = ? WHERE id_collector = ?';
        $stmt = $db->prepare($sql);
        $avatarPlaceholder = '';
        $stmt->bind_param('bi', $avatarPlaceholder, $collectorId);
        $stmt->send_long_data(0, $avatarBinary);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function updatePassword(int $collectorId, string $passwordHash): bool
    {
        $db = static::getDB();
        $sql = 'UPDATE Coleccionista SET password = ?, updated_password_at = NOW() WHERE id_collector = ?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('si', $passwordHash, $collectorId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function incrementPacks(int $collectorId, int $amount): bool
    {
        $db = static::getDB();
        $sql = 'UPDATE Coleccionista SET pack_balance = pack_balance + ? WHERE id_collector = ?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii', $amount, $collectorId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function decrementPacks(int $collectorId, int $amount): bool
    {
        $db = static::getDB();
        $sql = 'UPDATE Coleccionista SET pack_balance = pack_balance - ? WHERE id_collector = ? AND pack_balance >= ?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii', $amount, $collectorId, $amount);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();

        return $affectedRows > 0;
    }

    public static function claimDailyPack(int $collectorId): bool
    {
        $db = static::getDB();
        $sql = 'UPDATE Coleccionista
                SET pack_balance = pack_balance + 1,
                    last_pack_claim_at = NOW()
                WHERE id_collector = ?
                  AND (last_pack_claim_at IS NULL OR last_pack_claim_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR))';

        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $collectorId);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();

        return $affectedRows > 0;
    }

    public static function secondsUntilNextClaim(int $collectorId): int
    {
        $db = static::getDB();
        $sql = 'SELECT GREATEST(0, 86400 - TIMESTAMPDIFF(SECOND, last_pack_claim_at, NOW())) AS seconds_left
                FROM Coleccionista
                WHERE id_collector = ?
                LIMIT 1';

        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $collectorId);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (int)($row['seconds_left'] ?? 0);
    }
}
