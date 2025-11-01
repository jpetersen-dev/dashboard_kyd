<?php
session_start();
date_default_timezone_set('America/Santiago');
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
$router = new Router();

// --- Rutas Públicas (no requieren login) ---
$router->add('GET', '/login', 'AuthController@showLoginForm');
$router->add('POST', '/login', 'AuthController@login');
$router->add('POST', '/register', 'AuthController@register'); // Ahora es una API
$router->add('POST', '/api/verify-token', 'AuthController@verifyToken');

// --- Rutas Protegidas (requieren login) ---
$router->add('GET', '/', 'DashboardController@index');
$router->add('POST', '/logout', 'AuthController@logout');
$router->add('POST', '/campaigns/create', 'DashboardController@createCampaign');
$router->add('GET', '/contact/{id}', 'ContactController@show');
$router->add('POST', '/contact/note/add', 'ContactController@addNote');
$router->add('GET', '/api/interactions/{campaign_id}/{period}', 'DashboardController@getInteractionsData');
$router->add('POST', '/invitations/create', 'InvitationController@create');
$router->add('POST', '/invitations/delete/{token}', 'InvitationController@delete');

// --- Despachador ---

// **INICIO DE LA CORRECCIÓN**
// 1. Obtener la URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 2. Limpiar la URI: eliminar la barra final, excepto si es la ruta raíz.
$uri = rtrim($uri, '/');
if (empty($uri)) {
    $uri = '/'; // Asegurarse de que la ruta raíz siga siendo '/'
}
// **FIN DE LA CORRECCIÓN**

$method = $_SERVER['REQUEST_METHOD'];
$router->dispatch($uri, $method);

