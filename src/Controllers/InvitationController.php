<?php

namespace App\Controllers;

use App\Models\User;

class InvitationController
{
    public function __construct()
    {
        // Proteger todo el controlador para que solo usuarios logueados puedan gestionar invitaciones.
        if (!isset($_SESSION['user_id'])) {
            // Si no está logueado, se devuelve un error de no autorizado.
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
            exit();
        }
    }

    /**
     * Crea un nuevo token de invitación y lo devuelve como JSON.
     */
    public function create()
    {
        header('Content-Type: application/json');
        $userModel = new User();
        $token = $userModel->generateInvitationToken();

        if ($token) {
            http_response_code(201); // Created
            echo json_encode(['success' => true, 'token' => $token]);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'message' => 'No se pudo generar el token.']);
        }
    }

    /**
     * Elimina un token de invitación.
     * @param string $token
     */
    public function delete(string $token)
    {
        header('Content-Type: application/json');
        $userModel = new User();
        
        if ($userModel->deleteInvitationToken($token)) {
            echo json_encode(['success' => true, 'message' => 'Token eliminado con éxito.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el token.']);
        }
    }
}
