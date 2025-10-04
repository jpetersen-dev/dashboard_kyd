<?php

namespace App\Controllers;

use App\Models\User;

class AuthController
{
    /**
     * Muestra el formulario de login.
     */
    public function showLoginForm()
    {
        // Si se redirige desde un registro exitoso, mostrar mensaje.
        if (isset($_GET['success'])) {
            $_SESSION['success'] = '¡Cuenta creada con éxito! Ahora puedes iniciar sesión.';
        }
        require_once __DIR__ . '/../Views/login.php';
    }

    /**
     * Procesa el intento de login.
     */
    public function login()
    {
        $userModel = new User();
        
        $user = $userModel->findByUsername($_POST['username']);

        if ($user && password_verify($_POST['password'], $user->password_hash)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            header('Location: /');
            exit();
        } else {
            $_SESSION['error'] = 'Usuario o contraseña incorrectos.';
            header('Location: /login');
            exit();
        }
    }

    /**
     * Procesa la creación de una nueva cuenta (ahora como API).
     */
    public function register()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        $userModel = new User();

        // Validaciones
        if (empty($data['token']) || empty($data['username']) || empty($data['password']) || $data['password'] !== $data['password_confirm']) {
            http_response_code(400);
            echo json_encode(['message' => 'Todos los campos son obligatorios y las contraseñas deben coincidir.']);
            return;
        }

        if (!$userModel->findInvitationToken($data['token'])) {
            http_response_code(403);
            echo json_encode(['message' => 'El código de invitación no es válido o ya ha sido utilizado.']);
            return;
        }
        
        $result = $userModel->create($data['username'], $data['password']);

        if ($result === true) {
            $userModel->deleteInvitationToken($data['token']);
            echo json_encode(['message' => 'Cuenta creada con éxito.']);
        } else {
            http_response_code(409); // Conflict
            echo json_encode(['message' => 'El nombre de usuario ya está en uso. Por favor, elige otro.']);
        }
    }

    /**
     * NUEVO: Verifica un token de invitación vía API.
     */
    public function verifyToken()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['token'])) {
            http_response_code(400);
            echo json_encode(['message' => 'El token es obligatorio.']);
            return;
        }

        $userModel = new User();
        if ($userModel->findInvitationToken($data['token'])) {
            echo json_encode(['message' => 'Token válido.']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'El código de invitación no es válido o ya ha sido utilizado.']);
        }
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout()
    {
        session_unset();
        session_destroy();
        header('Location: /login');
        exit();
    }
}

