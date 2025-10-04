<?php

namespace App\Controllers;

use App\Models\Contact;

class ContactController
{
    public function __construct()
    {
        // Protege todo el controlador.
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
    }
    /**
     * Muestra la página de perfil detallado de un contacto.
     * @param string $id El id_contacto del usuario a mostrar.
     */
    public function show(string $id)
    {
        $contactModel = new Contact();

        $contactDetails = $contactModel->findById($id);

        // Si el contacto no existe, mostrar un error 404.
        if (!$contactDetails) {
            http_response_code(404);
            echo "Error 404: Contacto no encontrado.";
            return;
        }

        $interactionHistory = $contactModel->getInteractionHistory($id);
        $contactNotes = $contactModel->getNotes($id);

        // Cargar la vista del perfil con todos los datos obtenidos.
        require_once __DIR__ . '/../Views/contact_profile.php';
    }

    /**
     * Maneja la adición de una nueva nota a un contacto vía AJAX.
     */
    public function addNote()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        // Validaciones básicas
        if (empty($data['id_contacto']) || empty($data['note_content'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Faltan datos para añadir la nota.']);
            return;
        }

        $contactId = $data['id_contacto'];
        $noteContent = trim(strip_tags($data['note_content']));

        $contactModel = new Contact();
        $result = $contactModel->addNote($contactId, $noteContent);

        if ($result) {
            http_response_code(201); // Created
            echo json_encode(['success' => true, 'message' => 'Nota añadida con éxito.']);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'message' => 'Error al guardar la nota.']);
        }
    }
}

