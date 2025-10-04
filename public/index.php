<?php
session_start();
date_default_timezone_set('America/Santiago');
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
$router = new Router();

// --- Rutas Públicas ---
$router->add('GET', '/login', 'AuthController@showLoginForm');
$router->add('POST', '/login', 'AuthController@login');
$router->add('GET', '/register/{token}', 'AuthController@showRegisterForm');
$router->add('POST', '/register', 'AuthController@register');

// --- Rutas Protegidas ---
$router->add('GET', '/', 'DashboardController@index');
$router->add('POST', '/logout', 'AuthController@logout');
$router->add('POST', '/campaigns/create', 'DashboardController@createCampaign');
$router->add('GET', '/contact/{id}', 'ContactController@show');
$router->add('POST', '/contact/note/add', 'ContactController@addNote');
$router->add('GET', '/api/interactions/{campaign_id}/{period}', 'DashboardController@getInteractionsData');

// --- Despachador ---
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$router->dispatch($uri, $method);

