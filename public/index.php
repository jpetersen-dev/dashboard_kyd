<?php

date_default_timezone_set('America/Santiago');
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;

$router = new Router();

// --- Definición de Rutas ---

// Ruta principal del dashboard
$router->add('GET', '/', 'DashboardController@index');

// Rutas de Campañas (API)
$router->add('POST', '/campaigns/create', 'DashboardController@createCampaign');

// Rutas de Contactos
$router->add('GET', '/contact/{id}', 'ContactController@show');
$router->add('POST', '/contact/note/add', 'ContactController@addNote');


// --- Despachador de Rutas ---
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$router->dispatch($uri, $method);

