<?php

// Fichero de configuración para la conexión a la base de datos.
// AHORA LEE LAS CREDENCIALES DESDE LAS VARIABLES DE ENTORNO DEL SERVIDOR.
// Esto es más seguro y es la práctica estándar en plataformas como Render.

define('DB_HOST', getenv('DB_HOST'));
define('DB_PORT', getenv('DB_PORT') ?: '5432'); // Puerto por defecto si no se define
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));

