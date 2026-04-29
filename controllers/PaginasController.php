<?php

namespace Controllers;

use Model\ActiveRecord;
use Model\Admin;
use Model\Card;
use Model\Collector;
use Model\Inventory;
use Model\Player;
use Model\Team;
use Model\Trade;
use Model\User;
use MVC\Router;
use Middleware\SessionContext;

class PaginasController
{
    private static function blobToDataUrl(?string $blob, string $fallback = '/img/default.png'): string
    {
        if (!is_string($blob) || $blob === '') {
            return $fallback;
        }

        $mimeType = 'image/png';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $detectedType = finfo_buffer($finfo, $blob);
                unset($finfo);

                if (is_string($detectedType) && strpos($detectedType, 'image/') === 0) {
                    $mimeType = $detectedType;
                }
            }
        }

        return 'data:' . $mimeType . ';base64,' . base64_encode($blob);
    }

    private static function passwordPolicyErrors(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'La contraseña debe incluir al menos una letra mayuscula.';
        }

        if (!preg_match('/\d/', $password)) {
            $errors[] = 'La contraseña debe incluir al menos un numero.';
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'La contraseña debe incluir al menos un caracter especial.';
        }

        return $errors;
    }

    public static function index(Router $router)
    {
        $topCollectors = Collector::topCollectors(10);

        foreach ($topCollectors as $index => &$collector) {
            $collector['rank'] = $index + 1;
            $collector['avatar_url'] = static::blobToDataUrl($collector['avatar'] ?? null, '/img/image3.png');
            $collector['username'] = $collector['fullname'] ?? 'Usuario';
            $collector['puntaje'] = (int)($collector['total_cards'] ?? 0);
        }
        unset($collector);

        $router->render('paginas/index', [
            'topCollectors' => $topCollectors,
        ]);
    }

    public static function register(Router $router)
    {
        $errores = [];
        $exito = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';

            if ($name === '') {
                $errores[] = 'El nombre es obligatorio.';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El correo electronico no es valido.';
            }

            $errores = array_merge($errores, static::passwordPolicyErrors($password));

            if ($password !== $confirmPassword) {
                $errores[] = 'Las contraseñas no coinciden.';
            }

            if (!$errores && User::findByEmail($email)) {
                $errores[] = 'Este correo ya esta registrado.';
            }

            if (!$errores) {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                if (User::create($name, $email, $passwordHash)) {
                    $exito = 'Usuario registrado correctamente. Ya puedes iniciar sesion.';
                } else {
                    $errores[] = 'No se pudo registrar el usuario. Intentalo de nuevo.';
                }
            }
        }

        $router->render('paginas/register', [
            'errores' => $errores,
            'exito' => $exito
        ], 'layout-auth');
    }

    public static function login(Router $router)
    {
        $errores = [];
        $exito = '';
        SessionContext::start();
        $redirectUrl = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El correo electronico no es valido.';
            }

            if ($password === '') {
                $errores[] = 'La contraseña es obligatoria.';
            }

            if (!$errores) {
                $user = User::findByEmail($email);

                if ($user && password_verify($password, $user['password'])) {
                    SessionContext::start();

                    $_SESSION['user_id'] = $user['id_collector'];
                    $_SESSION['fullname'] = $user['fullname'];
                    $_SESSION['avatar_url'] = static::blobToDataUrl($user['avatar'] ?? null, '/img/image3.png');
                    $_SESSION['rol'] = 'collector';

                    $exito = 'Inicio de sesion correcto.';
                    $redirectUrl = '/home';
                } else {
                    $admin = Admin::findByEmail($email);

                    if (!$admin || !password_verify($password, $admin['password'])) {
                        $errores[] = 'Credenciales invalidas.';
                    } elseif (($admin['rol'] ?? '') !== 'Admin') {
                        $errores[] = 'Este administrador no tiene un rol valido.';
                    } else {
                        SessionContext::start();

                        $_SESSION['user_id'] = $admin['id_admin'];
                        $_SESSION['fullname'] = $admin['fullname'];
                        $_SESSION['rol'] = 'admin';

                        $exito = 'Inicio de sesion correcto.';
                        $redirectUrl = '/panel';
                    }
                }
            }
        }

        $router->render('paginas/login', [
            'errores' => $errores,
            'exito' => $exito,
            'redirectUrl' => $redirectUrl
        ], 'layout-auth');
    }

    public static function forgotPassword(Router $router)
    {
        $errores = [];
        $exito = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El correo electronico no es valido.';
            }

            $errores = array_merge($errores, static::passwordPolicyErrors($password));

            if ($password !== $confirmPassword) {
                $errores[] = 'Las contraseñas no coinciden.';
            }

            if (!$errores) {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $collectorUpdated = User::updatePasswordByEmail($email, $passwordHash);
                $adminUpdated = Admin::updatePasswordByEmail($email, $passwordHash);

                if ($collectorUpdated || $adminUpdated) {
                    $exito = 'Contraseña actualizada correctamente. Ya puedes iniciar sesion.';
                } else {
                    $errores[] = 'No se encontro una cuenta asociada a ese correo.';
                }
            }
        }

        $router->render('paginas/forgotPassword', [
            'errores' => $errores,
            'exito' => $exito
        ], 'layout-auth');
    }

    public static function logout(Router $router)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        header('Location: /');
        exit;
    }

    public static function album(Router $router)
{
    $collectorId = SessionContext::userId();
    $teams = $collectorId > 0 ? Team::allWithCollectorProgress($collectorId) : Team::all();

    foreach ($teams as &$team) {
        $teamId = (int)($team['id_team'] ?? 0);
        $team['flag_url'] = '/api/teams/flag?id_team=' . $teamId;
        $team['team_url'] = '/team?team=' . $teamId;
        $team['owned_cards'] = (int)($team['owned_cards'] ?? 0);
        $team['total_cards'] = (int)($team['players_amount'] ?? 0);
    }
    unset($team);

    $router->render('paginas/album', [
        'teams' => $teams,
    ], 'layout-collector');
}

    public static function altas(Router $router)
    {
        $erroresAltas = [];
        $exitoEquipo = '';
        SessionContext::start();
        $exitoJugador = '';
        $tabActiva = 'jugador';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formType = $_POST['form_type'] ?? '';

            if ($formType === 'player') {
                $tabActiva = 'jugador';
                $playerName = trim($_POST['player_name'] ?? '');
                $shirtNumber = (int)($_POST['shirt_number'] ?? 0);
                $position = trim($_POST['position'] ?? '');
                $rarity = trim($_POST['rarity'] ?? '');
                $teamId = (int)($_POST['id_team'] ?? 0);
                $registeredBy = SessionContext::userId();

                if ($playerName === '') {
                    $erroresAltas[] = 'El nombre del jugador es obligatorio.';
                }

                if ($shirtNumber < 1) {
                    $erroresAltas[] = 'El numero de camiseta debe ser mayor a 0.';
                }

                if ($position === '') {
                    $erroresAltas[] = 'La posicion del jugador es obligatoria.';
                }

                if (!in_array($rarity, ['Common', 'Rare', 'Epic', 'Legendary'], true)) {
                    $erroresAltas[] = 'Debes seleccionar una rareza valida para la carta.';
                }

                if ($teamId < 1) {
                    $erroresAltas[] = 'Debes seleccionar un equipo registrado.';
                }

                if ($registeredBy <= 0) {
                    $erroresAltas[] = 'No se pudo identificar al administrador que registra.';
                }

                $photoBinary = null;
                if (!isset($_FILES['photo']) || ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    $erroresAltas[] = 'Debes subir una fotografia del jugador.';
                } elseif (($_FILES['photo']['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                    $erroresAltas[] = 'Hubo un error al subir la fotografia.';
                } else {
                    $photoBinary = file_get_contents($_FILES['photo']['tmp_name']);
                    if ($photoBinary === false) {
                        $erroresAltas[] = 'No se pudo leer la imagen del jugador.';
                    }
                }

                if (!$erroresAltas) {
                    $db = ActiveRecord::getDatabaseConnection();
                    $db->begin_transaction();

                    try {
                        $playerId = Player::create([
                            'name' => $playerName,
                            'shirtnumber' => $shirtNumber,
                            'position' => $position,
                            'id_team' => $teamId,
                            'photo' => $photoBinary,
                            'registered_by' => $registeredBy,
                        ]);

                        if ($playerId === null) {
                            throw new \RuntimeException('No se pudo registrar el jugador.');
                        }

                        if (!Card::createForPlayer($playerId, $rarity, $photoBinary)) {
                            throw new \RuntimeException('No se pudo registrar la carta del jugador.');
                        }

                        $db->commit();
                        $exitoJugador = 'Jugador registrado correctamente.';
                    } catch (\Throwable $e) {
                        $db->rollback();
                        $erroresAltas[] = $e->getMessage();
                    }
                }
            } elseif ($formType === 'team') {
                $tabActiva = 'equipo';
                $country = trim($_POST['country'] ?? '');
                $playersAmount = (int)($_POST['players_amount'] ?? 0);
                $group = trim($_POST['group'] ?? '');
                $teamFact = trim($_POST['team_fact'] ?? '');
                $registeredBy = SessionContext::userId();

                if ($country === '') {
                    $erroresAltas[] = 'Debes seleccionar un pais.';
                }

                if ($playersAmount < 1) {
                    $erroresAltas[] = 'La cantidad de jugadores debe ser mayor a 0.';
                }

                if (!in_array($group, ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'], true)) {
                    $erroresAltas[] = 'Debes seleccionar un grupo valido.';
                }

                if ($teamFact === '') {
                    $erroresAltas[] = 'El dato del equipo es obligatorio.';
                }

                if ($registeredBy <= 0) {
                    $erroresAltas[] = 'No se pudo identificar al administrador que registra.';
                }

                $flagBinary = null;
                if (!isset($_FILES['flag']) || ($_FILES['flag']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    $erroresAltas[] = 'Debes subir un escudo para el equipo.';
                } elseif (($_FILES['flag']['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                    $erroresAltas[] = 'Hubo un error al subir el escudo.';
                } else {
                    $flagBinary = file_get_contents($_FILES['flag']['tmp_name']);
                    if ($flagBinary === false) {
                        $erroresAltas[] = 'No se pudo leer la imagen del escudo.';
                    }
                }

                if (!$erroresAltas) {
                    $ok = Team::create([
                        'country' => $country,
                        'players_amount' => $playersAmount,
                        'group' => $group,
                        'team_fact' => $teamFact,
                        'flag' => $flagBinary,
                        'registered_by' => $registeredBy,
                    ]);

                    if ($ok) {
                        $exitoEquipo = 'Equipo registrado correctamente.';
                    } else {
                        $erroresAltas[] = 'No se pudo registrar el equipo. Intentalo de nuevo.';
                    }
                }
            }
        }

        $teams = Team::all();

        $router->render('paginas/altas', [
            'erroresAltas' => $erroresAltas,
            'exitoEquipo' => $exitoEquipo,
            'exitoJugador' => $exitoJugador,
            'tabActiva' => $tabActiva,
            'teams' => $teams,
        ], 'layout-admin');
    }

    public static function feedOfertas(Router $router)
    {
        $router->render('paginas/feedOfertas', [], 'layout-collector');
    }

    public static function home(Router $router)
    {
        $collectorId = SessionContext::userId();
        $totalCardsGoal = 180;
        $ownedUniqueCards = 0;
        $tradeNotifications = [];

        if ($collectorId > 0) {
            $ownedUniqueCards = Inventory::uniqueOwnedCardsCount($collectorId);
            $tradeNotifications = Trade::acceptedNotificationsForCollector($collectorId, 5);
        }

        $progressPercentage = 0;
        if ($totalCardsGoal > 0) {
            $progressPercentage = (int)round(($ownedUniqueCards / $totalCardsGoal) * 100);
        }
        $progressPercentage = max(0, min(100, $progressPercentage));

        $router->render('paginas/home', [
            'ownedUniqueCards' => $ownedUniqueCards,
            'totalCardsGoal' => $totalCardsGoal,
            'progressPercentage' => $progressPercentage,
            'tradeNotifications' => $tradeNotifications,
        ], 'layout-collector');
    }

    public static function market(Router $router)
    {
        $router->render('paginas/market', [], 'layout-collector');
    }

    public static function ofertas(Router $router)
    {
        $router->render('paginas/ofertas', [], 'layout-collector');
    }

    public static function panel(Router $router)
    {
        $erroresPanel = [];
        $exitoPanel = '';

        $selectedTeamId = (int)($_GET['team'] ?? $_POST['team_filter'] ?? 0);
        $selectedPlayerId = (int)($_GET['player'] ?? $_POST['player_id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'delete_player') {
            $playerId = (int)($_POST['player_id'] ?? 0);

            if ($playerId < 1) {
                $erroresPanel[] = 'Debes seleccionar un jugador para eliminar.';
            }

            if (!$erroresPanel) {
                $db = ActiveRecord::getDatabaseConnection();
                $db->begin_transaction();

                try {
                    $player = Player::findById($playerId);
                    if (!$player) {
                        throw new \RuntimeException('El jugador seleccionado no existe.');
                    }

                    $card = Card::findByPlayerId($playerId);
                    if ($card) {
                        Inventory::deleteByCardId((int)$card['id_card']);
                        Card::deleteByPlayerId($playerId);
                    }

                    if (!Player::deleteById($playerId)) {
                        throw new \RuntimeException('No se pudo eliminar el jugador.');
                    }

                    $db->commit();
                    $exitoPanel = 'Jugador eliminado correctamente.';
                    $selectedPlayerId = 0;
                } catch (\Throwable $e) {
                    $db->rollback();
                    $erroresPanel[] = $e->getMessage();
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'update_player') {
            $playerId = (int)($_POST['player_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $shirtNumber = (int)($_POST['shirtnumber'] ?? 0);
            $position = trim($_POST['position'] ?? '');
            $teamId = (int)($_POST['id_team'] ?? 0);
            $rarity = trim($_POST['rarity'] ?? '');
            $registeredBy = SessionContext::userId();

            if ($playerId < 1) {
                $erroresPanel[] = 'Debes seleccionar un jugador para actualizar.';
            }

            if ($name === '') {
                $erroresPanel[] = 'El nombre del jugador es obligatorio.';
            }

            if ($shirtNumber < 1) {
                $erroresPanel[] = 'El numero de camiseta debe ser mayor a 0.';
            }

            if ($position === '') {
                $erroresPanel[] = 'La posicion del jugador es obligatoria.';
            }

            if ($teamId < 1) {
                $erroresPanel[] = 'Debes seleccionar un equipo valido.';
            }

            if (!in_array($rarity, ['Common', 'Rare', 'Epic', 'Legendary'], true)) {
                $erroresPanel[] = 'Debes seleccionar una rareza valida para la carta.';
            }

            if ($registeredBy < 1) {
                $erroresPanel[] = 'No se pudo identificar al administrador.';
            }

            $photoBinary = null;
            if (isset($_FILES['photo']) && ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                if (($_FILES['photo']['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                    $erroresPanel[] = 'Hubo un error al subir la fotografia.';
                } else {
                    $photoBinary = file_get_contents($_FILES['photo']['tmp_name']);
                    if ($photoBinary === false) {
                        $erroresPanel[] = 'No se pudo leer la fotografia.';
                    }
                }
            }

            if (!$erroresPanel) {
                $ok = Player::updateById($playerId, [
                    'name' => $name,
                    'shirtnumber' => $shirtNumber,
                    'position' => $position,
                    'id_team' => $teamId,
                    'photo' => $photoBinary,
                    'registered_by' => $registeredBy,
                ]);

                if ($ok && !Card::updateRarityByPlayerId($playerId, $rarity)) {
                    $erroresPanel[] = 'No se pudo actualizar la rareza de la carta.';
                    $ok = false;
                }

                if ($ok) {
                    $exitoPanel = 'Jugador actualizado correctamente.';
                    $selectedTeamId = $teamId;
                    $selectedPlayerId = $playerId;
                } else {
                    $erroresPanel[] = 'No se pudo actualizar el jugador. Intentalo de nuevo.';
                }
            }
        }

        $teams = Team::all();
        if ($selectedTeamId < 1 && !empty($teams)) {
            $selectedTeamId = (int)$teams[0]['id_team'];
        }

        $players = $selectedTeamId > 0 ? Player::allByTeam($selectedTeamId) : [];

        if ($selectedPlayerId < 1 && !empty($players)) {
            $selectedPlayerId = (int)$players[0]['id_player'];
        }

        $selectedPlayer = $selectedPlayerId > 0 ? Player::findById($selectedPlayerId) : null;
        if ($selectedPlayer) {
            $selectedCard = Card::findByPlayerId($selectedPlayerId);
            $selectedPlayer['rarity'] = $selectedCard['rarity'] ?? 'Common';
        }

        $router->render('paginas/panel', [
            'teams' => $teams,
            'players' => $players,
            'selectedTeamId' => $selectedTeamId,
            'selectedPlayerId' => $selectedPlayerId,
            'selectedPlayer' => $selectedPlayer,
            'erroresPanel' => $erroresPanel,
            'exitoPanel' => $exitoPanel,
        ], 'layout-admin');
    }

    public static function profile(Router $router)
    {
        $collectorId = SessionContext::userId();
        $erroresProfile = [];
        $erroresAvatar = [];
        $erroresPassword = [];
        $exitoAvatar = '';
        $exitoPassword = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $profileAction = $_POST['profile_action'] ?? '';

            if ($profileAction === 'avatar') {
                if (!isset($_FILES['avatar']) || ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    $erroresAvatar[] = 'Debes seleccionar una fotografia para actualizar tu perfil.';
                } elseif (($_FILES['avatar']['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                    $erroresAvatar[] = 'Hubo un error al subir la fotografia.';
                } else {
                    $avatarBinary = file_get_contents($_FILES['avatar']['tmp_name']);
                    if ($avatarBinary === false) {
                        $erroresAvatar[] = 'No se pudo leer la fotografia.';
                    } else {
                        $detectedMime = null;
                        if (function_exists('finfo_open')) {
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            if ($finfo !== false) {
                                $detectedMime = finfo_buffer($finfo, $avatarBinary);
                               unset($finfo);
                            }
                        }

                        if (!is_string($detectedMime) || strpos($detectedMime, 'image/') !== 0) {
                            $erroresAvatar[] = 'La fotografia debe ser una imagen valida.';
                        } elseif (Collector::updateAvatar($collectorId, $avatarBinary)) {
                            $exitoAvatar = 'Tu fotografia de perfil se actualizo correctamente.';
                        } else {
                            $erroresAvatar[] = 'No se pudo actualizar la fotografia de perfil.';
                        }
                    }
                }
            }

            if ($profileAction === 'password') {
                $password = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirmPassword'] ?? '';

                $erroresPassword = array_merge($erroresPassword, static::passwordPolicyErrors($password));

                if ($password !== $confirmPassword) {
                    $erroresPassword[] = 'Las contrasenas no coinciden.';
                }

                if (!$erroresPassword) {
                    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                    if (Collector::updatePassword($collectorId, $passwordHash)) {
                        $exitoPassword = 'Tu contraseña se actualizo correctamente.';
                    } else {
                        $erroresPassword[] = 'No se pudo actualizar la contraseña.';
                    }
                }
            }
        }

        $collector = Collector::findById($collectorId);
        if (!$collector) {
            $erroresProfile[] = 'No se encontro tu perfil.';
            $collector = [
                'id_collector' => $collectorId,
                'fullname' => $_SESSION['fullname'] ?? 'Perfil',
                'avatar_url' => '/img/image3.png',
            ];
        } else {
            SessionContext::start();
            $collector['avatar_url'] = static::blobToDataUrl($collector['avatar'] ?? null, '/img/image3.png');
            $_SESSION['fullname'] = $collector['fullname'] ?? ($_SESSION['fullname'] ?? 'Perfil');
            $_SESSION['avatar_url'] = $collector['avatar_url'];
        }

        $stats = Collector::leaderboardStats($collectorId);
        $duplicateCards = Inventory::duplicateCardsByCollector($collectorId);
        $tradeNotifications = Trade::acceptedNotificationsForCollector($collectorId, 5);

        foreach ($duplicateCards as &$duplicateCard) {
            $duplicateCard['card_url'] = '/api/cards/image?id_card=' . (int)($duplicateCard['id_card'] ?? 0);
        }
        unset($duplicateCard);

        $router->render('paginas/profile', [
            'collector' => $collector,
            'stats' => $stats,
            'duplicateCards' => $duplicateCards,
            'tradeNotifications' => $tradeNotifications,
            'erroresProfile' => $erroresProfile,
            'erroresAvatar' => $erroresAvatar,
            'erroresPassword' => $erroresPassword,
            'exitoAvatar' => $exitoAvatar,
            'exitoPassword' => $exitoPassword,
        ], 'layout-collector');
    }

    public static function team(Router $router)
    {
        $collectorId = SessionContext::userId();
        $teamId = (int)($_GET['team'] ?? 0);
        $fallbackTeam = null;
        $team = $teamId > 0 ? Team::findById($teamId) : null;

        if (!$team) {
            $teams = Team::all();
            $fallbackTeam = $teams[0] ?? null;
            if ($fallbackTeam) {
                $team = Team::findById((int)$fallbackTeam['id_team']);
                $teamId = (int)$fallbackTeam['id_team'];
            }
        }

        $players = $team && $collectorId > 0 ? Team::rosterWithInventory($teamId, $collectorId) : [];

        if ($team) {
            $team['flag_url'] = '/api/teams/flag?id_team=' . $teamId;
        }

        foreach ($players as &$player) {
            $player['card_url'] = ((int)($player['inventory_quantity'] ?? 0) > 0)
                ? '/api/cards/image?id_card=' . (int)($player['id_card'] ?? 0)
                : '/img/default.png';
        }
        unset($player);

        $totalSlots = 12;
        if ($team && (int)($team['players_amount'] ?? 0) > 0) {
            $totalSlots = (int)$team['players_amount'];
        }

        $playerSlots = [];
        foreach ($players as $player) {
            if (count($playerSlots) >= $totalSlots) {
                break;
            }

            $playerSlots[] = [
                'player_name' => $player['player_name'] ?? 'Faltante',
                'card_url' => $player['card_url'] ?? '/img/default.png',
                'has_card' => ((int)($player['inventory_quantity'] ?? 0) > 0),
            ];
        }

        while (count($playerSlots) < $totalSlots) {
            $playerSlots[] = [
                'player_name' => 'Faltante',
                'card_url' => '/img/default.png',
                'has_card' => false,
            ];
        }

        $router->render('paginas/team', [
            'team' => $team,
            'players' => $players,
            'playerSlots' => $playerSlots,
        ], 'layout-collector');
    }

    public static function scoreboard(Router $router)
    {
        SessionContext::userId();
        $router->render('paginas/scoreboard', [], 'layout-collector');
    }
}
