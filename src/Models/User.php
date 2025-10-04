<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User extends Database
{
    /**
     * Encuentra un usuario por su nombre de usuario.
     * @param string $username
     * @return object|false
     */
    public function findByUsername(string $username)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM public.users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            error_log('Error en findByUsername: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo usuario con una contraseña hasheada.
     * @param string $username
     * @param string $password
     * @return bool True si tuvo éxito, false si el usuario ya existe.
     */
    public function create(string $username, string $password): bool
    {
        // Verificar si el usuario ya existe
        if ($this->findByUsername($username)) {
            return false;
        }

        try {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $sql = "INSERT INTO public.users (username, password_hash) VALUES (:username, :password_hash)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':username' => $username, ':password_hash' => $passwordHash]);
        } catch (\PDOException $e) {
            error_log('Error al crear usuario: ' . $e->getMessage());
            return false;
        }
    }


    public function findInvitationToken(string $token): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT 1 FROM public.invitations WHERE token = :token");
            $stmt->execute([':token' => $token]);
            return $stmt->fetchColumn() !== false;
        } catch (\PDOException $e) {
            error_log('Error en findInvitationToken: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteInvitationToken(string $token): bool
    {
         try {
            $stmt = $this->db->prepare("DELETE FROM public.invitations WHERE token = :token");
            return $stmt->execute([':token' => $token]);
        } catch (\PDOException $e) {
            error_log('Error en deleteInvitationToken: ' . $e->getMessage());
            return false;
        }
    }
}

