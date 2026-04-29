<?php

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

require_once __DIR__ . '/../Router.php';
require_once __DIR__ . '/../controllers/PaginasController.php';
require_once __DIR__ . '/../controllers/ApiController.php';
require_once __DIR__ . '/../middleware/Middleware.php';
require_once __DIR__ . '/../middleware/SessionContext.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ActiveRecord.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../models/Team.php';
require_once __DIR__ . '/../models/Player.php';
require_once __DIR__ . '/../models/Collector.php';
require_once __DIR__ . '/../models/Card.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/Pack.php';
require_once __DIR__ . '/../models/PackOpening.php';
require_once __DIR__ . '/../models/PackCard.php';
require_once __DIR__ . '/../models/Trade.php';
require_once __DIR__ . '/../models/TradeCard.php';
require_once __DIR__ . '/../src/services/EnvLoader.php';
require_once __DIR__ . '/../src/services/FootballDataService.php';

use MVC\Router;
use Controllers\ApiController;
use Controllers\PaginasController;
use Middleware\Middleware;
use Model\ActiveRecord;

$db = Database::getInstance()->getConnection();
ActiveRecord::setDB($db);

$router = new Router();

$router->get('/', [PaginasController::class, 'index']);
$router->get('/register', [PaginasController::class, 'register'], Middleware::forGuest());
$router->post('/register', [PaginasController::class, 'register'], Middleware::forGuest());
$router->get('/login', [PaginasController::class, 'login'], Middleware::forGuest());
$router->post('/login', [PaginasController::class, 'login'], Middleware::forGuest());
$router->get('/forgot-password', [PaginasController::class, 'forgotPassword']);
$router->post('/forgot-password', [PaginasController::class, 'forgotPassword']);
$router->get('/logout', [PaginasController::class, 'logout'], Middleware::forAuth());
$router->get('/album', [PaginasController::class, 'album'], Middleware::forCollector());
$router->get('/altas', [PaginasController::class, 'altas'], Middleware::forAdmin());
$router->post('/altas', [PaginasController::class, 'altas'], Middleware::forAdmin());
$router->get('/feedOfertas', [PaginasController::class, 'feedOfertas'], Middleware::forCollector());
$router->get('/home', [PaginasController::class, 'home'], Middleware::forCollector());
$router->get('/market', [PaginasController::class, 'market'], Middleware::forCollector());
$router->get('/ofertas', [PaginasController::class, 'ofertas'], Middleware::forCollector());
$router->get('/panel', [PaginasController::class, 'panel'], Middleware::forAdmin());
$router->post('/panel', [PaginasController::class, 'panel'], Middleware::forAdmin());
$router->get('/profile', [PaginasController::class, 'profile'], Middleware::forCollector());
$router->post('/profile', [PaginasController::class, 'profile'], Middleware::forCollector());
$router->get('/team', [PaginasController::class, 'team'], Middleware::forCollector());
$router->get('/scoreboard', [PaginasController::class, 'scoreboard'], Middleware::forCollector());

$router->get('/api/packs/balance', [ApiController::class, 'packsBalance'], Middleware::forCollector());
$router->post('/api/packs/claim-daily', [ApiController::class, 'claimDailyPack'], Middleware::forCollector());
$router->post('/api/packs/open', [ApiController::class, 'openPack'], Middleware::forCollector());
$router->post('/api/packs/open-all', [ApiController::class, 'openAllPacks'], Middleware::forCollector());
$router->get('/api/inventory', [ApiController::class, 'inventory'], Middleware::forCollector());
$router->get('/api/cards/catalog', [ApiController::class, 'cardsCatalog'], Middleware::forCollector());
$router->get('/api/cards/image', [ApiController::class, 'cardImage'], Middleware::forCollector());
$router->get('/api/teams/flag', [ApiController::class, 'teamFlag'], Middleware::forCollector());
$router->post('/api/trades', [ApiController::class, 'createTrade'], Middleware::forCollector());
$router->get('/api/trades/feed', [ApiController::class, 'tradeFeed'], Middleware::forCollector());
$router->post('/api/trades/accept', [ApiController::class, 'acceptTrade'], Middleware::forCollector());
$router->post('/api/trades/reject', [ApiController::class, 'rejectTrade'], Middleware::forCollector());
$router->post('/api/trades/cancel', [ApiController::class, 'cancelTrade'], Middleware::forCollector());
$router->get('/api/admin/team-players', [ApiController::class, 'panelTeamPlayers'], Middleware::forAdmin());
$router->get('/api/admin/player-detail', [ApiController::class, 'panelPlayerDetail'], Middleware::forAdmin());
$router->get('/api/external/scoreboard', [ApiController::class, 'scoreboard'], Middleware::forCollector());
$router->get('/api/external/upcoming-matches', [ApiController::class, 'upcomingMatches'], Middleware::forCollector());
$router->get('/api/debug/matches', [ApiController::class, 'debugMatches'], Middleware::forCollector());

$router->comprobarRutas();