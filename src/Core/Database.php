<?php

namespace App\Core;

use PDO;
use PDOException;

// Incluir el archivo de configuración de forma más robusta
require_once __DIR__ . '/../../config/database.php';

class Database {
    protected $db;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $dsn = 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->db = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('Connection Error: ' . $e->getMessage());
            die('Error de conexión a la base de datos. Por favor, contacte al administrador.');
        }
    }
}

