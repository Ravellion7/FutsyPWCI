<?php

namespace Controllers;

use Model\Card;
use Model\Collector;
use Model\Inventory;
use Model\Pack;
use Model\PackCard;
use Model\PackOpening;
use Model\Trade;
use Model\TradeCard;
use Middleware\SessionContext;
use MVC\Router;

class ApiController
{
    private static function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private static function parseJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private static function pickRarity(array $dropRates): string
    {
        $total = 0.0;
        foreach ($dropRates as $weight) {
            $total += (float)$weight;
        }

        if ($total <= 0.0) {
            return 'Common';
        }

        $target = random_int(1, 1000000) / 1000000 * $total;
        $running = 0.0;

        foreach ($dropRates as $rarity => $weight) {
            $running += (float)$weight;
            if ($target <= $running) {
                return (string)$rarity;
            }
        }

        return (string)array_key_first($dropRates);
    }

    private static function normalizeTradeItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $cardId = (int)($item['id_card'] ?? 0);
            $quantity = (int)($item['quantity'] ?? 0);

            if ($cardId < 1 || $quantity < 1) {
                continue;
            }

            if (!isset($normalized[$cardId])) {
                $normalized[$cardId] = 0;
            }

            $normalized[$cardId] += $quantity;
        }

        $result = [];
        foreach ($normalized as $cardId => $quantity) {
            $result[] = [
                'id_card' => (int)$cardId,
                'quantity' => (int)$quantity,
            ];
        }

        return $result;
    }

    private static function binaryToDataUrl(string $binary): ?string
    {
        if (!is_string($binary) || $binary === '') {
            return null;
        }

        $mimeType = 'image/jpeg';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $detectedType = finfo_buffer($finfo, $binary);
                unset($finfo);

                if (is_string($detectedType) && strpos($detectedType, 'image/') === 0) {
                    $mimeType = $detectedType;
                }
            }
        }

        return 'data:' . $mimeType . ';base64,' . base64_encode($binary);
    }

    private static function cardImageApiUrl(int $cardId): string
    {
        return '/api/cards/image?id_card=' . $cardId;
    }

    private static function pickCardFromPools(array $cardsByRarity, string $rarity, array $fallbackCards = []): ?array
    {
        $pool = $cardsByRarity[$rarity] ?? [];
        if (empty($pool)) {
            $pool = $fallbackCards;
        }

        if (empty($pool)) {
            return null;
        }

        $randomKey = array_rand($pool);
        return is_int($randomKey) || is_string($randomKey) ? $pool[$randomKey] : null;
    }

    private static function openSinglePack(int $collectorId, int $packId, int $packSize, array $dropRates): array
    {
        $openingId = PackOpening::create($collectorId, $packId);
        $openedCards = [];
        $cardsByRarity = Card::cardsByRarities(array_keys($dropRates));
        $fallbackCards = [];

        foreach ($cardsByRarity as $cards) {
            foreach ($cards as $card) {
                $fallbackCards[] = $card;
            }
        }

        if (empty($fallbackCards)) {
            $allCardsByRarity = Card::cardsByRarities();
            foreach ($allCardsByRarity as $cards) {
                foreach ($cards as $card) {
                    $fallbackCards[] = $card;
                }
            }
        }

        for ($slot = 1; $slot <= $packSize; $slot++) {
            $rarity = static::pickRarity($dropRates);
            $card = static::pickCardFromPools($cardsByRarity, $rarity, $fallbackCards);

            if (!$card) {
                throw new \RuntimeException('No cards available in Card table.');
            }

            $cardId = (int)$card['id_card'];

            Inventory::addCard($collectorId, $cardId, 1);
            PackCard::create($openingId, $cardId);

            $openedCards[] = [
                'slot' => $slot,
                'id_card' => $cardId,
                'rarity' => $card['rarity'] ?? $rarity,
                'player_name' => $card['player_name'] ?? null,
                'team_name' => $card['team_name'] ?? null,
            ];
        }

        $imagesById = Card::imagesByIds(array_column($openedCards, 'id_card'));
        foreach ($openedCards as &$openedCard) {
            $cardId = (int)($openedCard['id_card'] ?? 0);
            $openedCard['image_url'] = static::binaryToDataUrl($imagesById[$cardId] ?? null);
        }
        unset($openedCard);

        return [
            'opening_id' => $openingId,
            'cards' => $openedCards,
        ];
    }

    public static function packsBalance(Router $router): void
    {
        $collectorId = SessionContext::userId();
        $collector = Collector::findById($collectorId);

        if (!$collector) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'Collector not found.'
            ], 404);
        }

        static::jsonResponse([
            'ok' => true,
            'data' => [
                'collector_id' => (int)$collector['id_collector'],
                'packs_balance' => (int)($collector['packs_balance'] ?? 0),
                'last_pack_claim_at' => $collector['last_pack_claim_at'] ?? null,
            ]
        ]);
    }

    public static function claimDailyPack(Router $router): void
    {
        $collectorId = SessionContext::userId();
        $claimed = Collector::claimDailyPack($collectorId);
        $collector = Collector::findById($collectorId);

        if (!$collector) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'Collector not found.'
            ], 404);
        }

        if (!$claimed) {
            $secondsLeft = Collector::secondsUntilNextClaim($collectorId);
            static::jsonResponse([
                'ok' => false,
                'error' => 'Daily pack already claimed.',
                'seconds_until_next_claim' => $secondsLeft,
                'packs_balance' => (int)($collector['packs_balance'] ?? 0)
            ], 409);
        }

        static::jsonResponse([
            'ok' => true,
            'message' => 'Daily pack claimed successfully.',
            'packs_balance' => (int)($collector['packs_balance'] ?? 0)
        ]);
    }

    public static function openPack(Router $router): void
    {
        $collectorId = SessionContext::userId();
        $body = static::parseJsonBody();

        $packId = (int)($body['pack_id'] ?? 0);
        if ($packId < 1) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'pack_id is required.'
            ], 422);
        }

        $pack = Pack::findById($packId);
        if (!$pack) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'Pack not found.'
            ], 404);
        }

        $packSize = (int)($pack['pack_size'] ?? 0);
        if ($packSize < 1) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'Invalid pack_size configuration.'
            ], 500);
        }

        $dropRates = json_decode((string)($pack['drop_rates_json'] ?? '{}'), true);
        if (!is_array($dropRates) || empty($dropRates)) {
            $dropRates = [
                'Common' => 0.7,
                'Rare' => 0.22,
                'Epic' => 0.07,
                'Legendary' => 0.01,
            ];
        }

        $db = \Model\ActiveRecord::getDatabaseConnection();
        $db->begin_transaction();

        try {
            if (!Collector::decrementPacks($collectorId, 1)) {
                throw new \RuntimeException('Not enough packs balance.');
            }

            $opening = static::openSinglePack($collectorId, $packId, $packSize, $dropRates);

            $db->commit();

            $collector = Collector::findById($collectorId);
            static::jsonResponse([
                'ok' => true,
                'message' => 'Pack opened successfully.',
                'data' => [
                    'opening_id' => $opening['opening_id'],
                    'cards' => $opening['cards'],
                    'packs_balance' => (int)($collector['packs_balance'] ?? 0),
                ]
            ]);
        } catch (\Throwable $e) {
            $db->rollback();

            static::jsonResponse([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 409);
        }
    }

    public static function openAllPacks(Router $router): void
    {
        $collectorId = SessionContext::userId();
        $body = static::parseJsonBody();

        $packId = (int)($body['pack_id'] ?? 0);
        if ($packId < 1) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'pack_id is required.'
            ], 422);
        }

        $pack = Pack::findById($packId);
        if (!$pack) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'Pack not found.'
            ], 404);
        }

        $collector = Collector::findById($collectorId);
        $balance = (int)($collector['packs_balance'] ?? 0);
        if ($balance < 1) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'No packs available.'
            ], 409);
        }

        $packSize = (int)($pack['pack_size'] ?? 0);
        $dropRates = json_decode((string)($pack['drop_rates_json'] ?? '{}'), true);
        if (!is_array($dropRates) || empty($dropRates)) {
            $dropRates = [
                'Common' => 0.7,
                'Rare' => 0.22,
                'Epic' => 0.07,
                'Legendary' => 0.01,
            ];
        }

        $db = \Model\ActiveRecord::getDatabaseConnection();
        $db->begin_transaction();

        try {
            if (!Collector::decrementPacks($collectorId, $balance)) {
                throw new \RuntimeException('Could not decrement packs balance.');
            }

            $openings = [];
            for ($i = 0; $i < $balance; $i++) {
                $openings[] = static::openSinglePack($collectorId, $packId, $packSize, $dropRates);
            }

            $db->commit();

            static::jsonResponse([
                'ok' => true,
                'message' => 'All packs opened successfully.',
                'data' => [
                    'opened_count' => $balance,
                    'openings' => $openings,
                    'packs_balance' => 0,
                ]
            ]);
        } catch (\Throwable $e) {
            $db->rollback();

            static::jsonResponse([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 409);
        }
    }

    public static function inventory(Router $router): void
    {
        $collectorId = SessionContext::userId();
        $items = Inventory::allByCollector($collectorId);

        foreach ($items as &$item) {
            $item['id_card'] = (int)($item['id_card'] ?? 0);
            $item['quantity'] = (int)($item['quantity'] ?? 0);
            $item['image_url'] = static::cardImageApiUrl((int)$item['id_card']);
        }
        unset($item);

        static::jsonResponse([
            'ok' => true,
            'data' => [
                'collector_id' => $collectorId,
                'items' => $items,
            ]
        ]);
    }

    public static function cardsCatalog(Router $router): void
    {
        SessionContext::userId();
        $cards = Card::catalog();

        foreach ($cards as &$card) {
            $card['id_card'] = (int)($card['id_card'] ?? 0);
            $card['image_url'] = static::cardImageApiUrl((int)$card['id_card']);
        }
        unset($card);

        static::jsonResponse([
            'ok' => true,
            'data' => [
                'items' => $cards,
            ],
        ]);
    }

    public static function cardImage(Router $router): void
    {
        SessionContext::userId();
        $cardId = (int)($_GET['id_card'] ?? 0);

        if ($cardId < 1) {
            http_response_code(422);
            exit;
        }

        $card = Card::findImageById($cardId);
        if (!$card || !is_string($card['card_image'] ?? null) || $card['card_image'] === '') {
            http_response_code(404);
            exit;
        }

        $binary = $card['card_image'];
        $mimeType = 'image/jpeg';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $detectedType = finfo_buffer($finfo, $binary);
                unset($finfo);

                if (is_string($detectedType) && strpos($detectedType, 'image/') === 0) {
                    $mimeType = $detectedType;
                }
            }
        }

        header('Content-Type: ' . $mimeType);
        header('Cache-Control: public, max-age=300');
        echo $binary;
        exit;
    }

    public static function teamFlag(Router $router): void
    {
        SessionContext::userId();
        $teamId = (int)($_GET['id_team'] ?? 0);

        if ($teamId < 1) {
            http_response_code(422);
            exit;
        }

        $team = \Model\Team::findById($teamId);
        if (!$team || !is_string($team['flag'] ?? null) || $team['flag'] === '') {
            http_response_code(404);
            exit;
        }

        $binary = $team['flag'];
        $mimeType = 'image/png';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $detectedType = finfo_buffer($finfo, $binary);
                unset($finfo);

            if (is_string($detectedType) && strpos($detectedType, 'image/') === 0) {
                $mimeType = $detectedType;
            }
        }
    }

    header('Content-Type: ' . $mimeType);
    header('Cache-Control: public, max-age=300');
    echo $binary;
    exit;
}

    public static function createTrade(Router $router): void
    {
        $collectorId = SessionContext::userId();
        $body = static::parseJsonBody();

        $receiverId = isset($body['id_receiver']) ? (int)$body['id_receiver'] : 0;
        $receiverId = $receiverId > 0 ? $receiverId : null;
        $offerItems = static::normalizeTradeItems((array)($body['offer'] ?? []));
        $requestItems = static::normalizeTradeItems((array)($body['request'] ?? []));

        // Requested cards are always one-for-one in this trading flow.
        foreach ($requestItems as &$requestItem) {
            $requestItem['quantity'] = 1;
        }
        unset($requestItem);

        if ($receiverId !== null && $receiverId === $collectorId) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'id_receiver cannot be the same collector as sender.'
            ], 422);
        }

        if (empty($offerItems) || empty($requestItems)) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'offer and request arrays are required.'
            ], 422);
        }

        if (!Inventory::hasEnoughCards($collectorId, $offerItems)) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'No tienes las cartas necesarias para aceptar este intercambio.'
            ], 409);
        }

        $db = \Model\ActiveRecord::getDatabaseConnection();
        $db->begin_transaction();

        try {
            $tradeId = Trade::create($collectorId, $receiverId);

            foreach ($offerItems as $item) {
                TradeCard::add($tradeId, $item['id_card'], $item['quantity'], 'sender');
            }

            foreach ($requestItems as $item) {
                TradeCard::add($tradeId, $item['id_card'], $item['quantity'], 'receiver');
            }

            $db->commit();

            static::jsonResponse([
                'ok' => true,
                'message' => 'Intercambio creado exitosamente.',
                'data' => [
                    'id_trade' => $tradeId,
                ]
            ], 201);
        } catch (\Throwable $e) {
            $db->rollback();

            static::jsonResponse([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public static function tradeFeed(Router $router): void
    {
        $collectorId = SessionContext::userId();
        $filter = strtolower((string)($_GET['filter'] ?? 'all'));
        if (isset($_GET['mine']) && (int)$_GET['mine'] === 1) {
            $filter = 'mine';
        }

        if (!in_array($filter, ['all', 'mine', 'received', 'completed'], true)) {
            $filter = 'all';
        }

        $feed = Trade::feedByFilter($collectorId, $filter);

        $tradeIds = [];
        foreach ($feed as $trade) {
            $tradeIds[] = (int)($trade['id_trade'] ?? 0);
        }

        $tradeCards = TradeCard::detailsByTradeIds($tradeIds);

        foreach ($feed as &$trade) {
            $tradeId = (int)($trade['id_trade'] ?? 0);
            $trade['sender_avatar_url'] = static::binaryToDataUrl($trade['sender_avatar'] ?? null);
            unset($trade['sender_avatar']);
            $trade['cards'] = $tradeCards[$tradeId] ?? [
                'offer' => [],
                'request' => [],
            ];

            foreach ($trade['cards']['offer'] as &$item) {
                $item['image_url'] = static::cardImageApiUrl((int)($item['id_card'] ?? 0));
            }
            unset($item);

            foreach ($trade['cards']['request'] as &$item) {
                $item['image_url'] = static::cardImageApiUrl((int)($item['id_card'] ?? 0));
            }
            unset($item);
        }
        unset($trade);

        static::jsonResponse([
            'ok' => true,
            'data' => $feed,
        ]);
    }

    public static function acceptTrade(Router $router): void
    {
        $collectorId = SessionContext::userId();
        $body = static::parseJsonBody();
        $tradeId = (int)($body['trade_id'] ?? 0);

        if ($tradeId < 1) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'trade_id is required.'
            ], 422);
        }

        $trade = Trade::findById($tradeId);
        if (!$trade) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'Trade not found.'
            ], 404);
        }

        if ((string)$trade['status'] !== 'pending') {
            static::jsonResponse([
                'ok' => false,
                'error' => 'Trade is not pending.'
            ], 409);
        }

        $senderId = (int)$trade['id_sender'];
        $receiverId = isset($trade['id_receiver']) ? (int)$trade['id_receiver'] : 0;
        if ($receiverId > 0 && $receiverId !== $collectorId) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'This trade is not assigned to you.'
            ], 403);
        }

        $sides = TradeCard::splitBySide($tradeId);
        $offerItems = $sides['offer'];
        $requestItems = $sides['request'];

        // Backward-compatible safeguard: force requested side to one unit per card.
        foreach ($requestItems as &$requestItem) {
            $requestItem['quantity'] = 1;
        }
        unset($requestItem);

        if (!Inventory::hasEnoughCards($senderId, $offerItems)) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'Sender no longer has enough offered cards.'
            ], 409);
        }

        if (!Inventory::hasEnoughCards($collectorId, $requestItems)) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'You do not have enough requested cards.'
            ], 409);
        }

        $db = \Model\ActiveRecord::getDatabaseConnection();
        $db->begin_transaction();

        try {
            Trade::reserveReceiverIfOpen($tradeId, $collectorId);

            foreach ($offerItems as $item) {
                Inventory::removeCard($senderId, $item['id_card'], $item['quantity']);
                Inventory::addCard($collectorId, $item['id_card'], $item['quantity']);
            }

            foreach ($requestItems as $item) {
                Inventory::removeCard($collectorId, $item['id_card'], $item['quantity']);
                Inventory::addCard($senderId, $item['id_card'], $item['quantity']);
            }

            Trade::updateStatus($tradeId, 'accepted');

            $db->commit();

            static::jsonResponse([
                'ok' => true,
                'message' => 'Trade accepted successfully.',
            ]);
        } catch (\Throwable $e) {
            $db->rollback();

            static::jsonResponse([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public static function rejectTrade(Router $router): void
    {
        $collectorId = SessionContext::userId();
        $body = static::parseJsonBody();
        $tradeId = (int)($body['trade_id'] ?? 0);

        if ($tradeId < 1) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'trade_id is required.'
            ], 422);
        }

        $trade = Trade::findById($tradeId);
        if (!$trade) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'Trade not found.'
            ], 404);
        }

        $receiverId = isset($trade['id_receiver']) ? (int)$trade['id_receiver'] : 0;
        if ($receiverId > 0 && $receiverId !== $collectorId) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'This trade is not assigned to you.'
            ], 403);
        }

        if ((string)$trade['status'] !== 'pending') {
            static::jsonResponse([
                'ok' => false,
                'error' => 'Trade is not pending.'
            ], 409);
        }

        Trade::reserveReceiverIfOpen($tradeId, $collectorId);
        Trade::updateStatus($tradeId, 'rejected');

        static::jsonResponse([
            'ok' => true,
            'message' => 'Trade rejected successfully.',
        ]);
    }

    public static function cancelTrade(Router $router): void
    {
        $collectorId = SessionContext::userId();
        $body = static::parseJsonBody();
        $tradeId = (int)($body['trade_id'] ?? 0);

        if ($tradeId < 1) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'trade_id is required.'
            ], 422);
        }

        $trade = Trade::findById($tradeId);
        if (!$trade) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'Trade not found.'
            ], 404);
        }

        if ((int)$trade['id_sender'] !== $collectorId) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'Only sender can cancel this trade.'
            ], 403);
        }

        if ((string)$trade['status'] !== 'pending') {
            static::jsonResponse([
                'ok' => false,
                'error' => 'Trade is not pending.'
            ], 409);
        }

        Trade::updateStatus($tradeId, 'cancelled');

        static::jsonResponse([
            'ok' => true,
            'message' => 'Trade cancelled successfully.',
        ]);
    }

    public static function panelTeamPlayers(Router $router): void
    {
        SessionContext::userId();
        $teamId = (int)($_GET['id_team'] ?? 0);

        if ($teamId < 1) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'id_team is required.'
            ], 422);
        }

        $players = \Model\Player::allByTeam($teamId);
        
        static::jsonResponse([
            'ok' => true,
            'data' => $players
        ]);
    }

    public static function panelPlayerDetail(Router $router): void
    {
        SessionContext::userId();
        $playerId = (int)($_GET['id_player'] ?? 0);

        if ($playerId < 1) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'id_player is required.'
            ], 422);
        }

        $player = \Model\Player::findById($playerId);
        if (!$player) {
            static::jsonResponse([
                'ok' => false,
                'error' => 'Player not found.'
            ], 404);
        }

        $card = \Model\Card::findByPlayerId($playerId);
        $player['rarity'] = $card['rarity'] ?? 'Common';

        if (isset($player['photo']) && is_string($player['photo']) && $player['photo'] !== '') {
            $photoMime = 'image/jpeg';
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo !== false) {
                    $detectedMime = finfo_buffer($finfo, $player['photo']);
                    unset($finfo);

                    if (is_string($detectedMime) && str_starts_with($detectedMime, 'image/')) {
                        $photoMime = $detectedMime;
                    }
                }
            }
            $player['photo_url'] = 'data:' . $photoMime . ';base64,' . base64_encode($player['photo']);
        } else {
            $player['photo_url'] = null;
        }

        unset($player['photo']);

        static::jsonResponse([
            'ok' => true,
            'data' => $player
        ]);
    }

    public static function scoreboard(Router $router): void
    {
        SessionContext::userId();
        
        $service = new \App\Services\FootballDataService();
        
        // Get live matches first
        $liveMatches = $service->getLiveMatches();
        
        // If no live matches, get scheduled ones
        if (empty($liveMatches)) {
            $liveMatches = $service->getScheduledMatches();
        }
        
        // Get standings for context
        $standings = $service->getStandings();
        
        static::jsonResponse([
            'ok' => true,
            'data' => [
                'matches' => $liveMatches,
                'standings' => $standings,
            ]
        ]);
    }

    public static function upcomingMatches(Router $router): void
    {
        SessionContext::userId();
        
        $service = new \App\Services\FootballDataService();
        
        // Get all matches (includes LIVE, SCHEDULED, TIMED, FINISHED, etc)
        $allMatches = $service->getAllMatches();
        
        // Filter for upcoming matches - include any status except FINISHED
        // TIMED = partidos con fecha/hora confirmada pero sin jugar
        // LIVE = partidos en vivo
        // SCHEDULED = partidos programados
        $upcomingMatches = array_filter($allMatches, function($match) {
            $status = $match['status'] ?? '';
            // Exclude only finished matches and suspended
            return $status !== 'FINISHED' && $status !== 'SUSPENDED' && $status !== 'POSTPONED' && $status !== 'CANCELLED';
        });
        
        // Limit to 5 matches
        $upcomingMatches = array_slice($upcomingMatches, 0, 5);
        
        static::jsonResponse([
            'ok' => true,
            'data' => [
                'matches' => array_values($upcomingMatches),
            ]
        ]);
    }

    public static function debugMatches(Router $router): void
    {
        SessionContext::userId();
        
        $service = new \App\Services\FootballDataService();
        
        // Get raw response from API
        $rawResponse = $service->request('/competitions/WC/matches');
        
        // Also try getting all matches
        $allMatches = $service->getAllMatches();
        
        static::jsonResponse([
            'ok' => true,
            'data' => [
                'raw_response' => $rawResponse,
                'all_matches_extracted' => count($allMatches),
                'first_raw_match' => isset($rawResponse['matches'][0]) ? $rawResponse['matches'][0] : 'No matches in raw response',
            ]
        ]);
    }
}
