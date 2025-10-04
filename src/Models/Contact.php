<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Contact extends Database
{
    /**
     * Encuentra un contacto por su ID y une información de ambas tablas.
     * @param string $id El id_contacto a buscar.
     * @return array|false Los detalles del contacto o false si no se encuentra.
     */
    public function findById(string $id)
    {
        try {
            $sql = "
                SELECT 
                    cm.id_contacto, cm.nombre_contacto, cm.telefono_1, cm.cargo, cm.email,
                    cm.direccion, cm.comuna, cm.region, cm.empresa_razon_social,
                    op.rubro
                FROM public.contacts_master cm
                LEFT JOIN public.contacts_operational op ON cm.id_contacto = op.id_contacto
                WHERE cm.id_contacto = :id_contacto
                LIMIT 1;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_contacto' => $id]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            error_log('Error en findById: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el historial completo de interacciones de un contacto.
     * @param string $id El id_contacto.
     * @return array La lista de interacciones.
     */
    public function getInteractionHistory(string $id): array
    {
        try {
            $sql = "
                SELECT 
                    i.tipo_interaccion,
                    i.timestamp,
                    c.nombre_campana
                FROM public.interactions_log i
                JOIN public.campaigns c ON i.campaign_id = c.campaign_id
                WHERE i.id_contacto = :id_contacto
                ORDER BY i.timestamp DESC;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_contacto' => $id]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('Error en getInteractionHistory: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todas las notas asociadas a un contacto.
     * @param string $id El id_contacto.
     * @return array La lista de notas.
     */
    public function getNotes(string $id): array
    {
        try {
            $sql = "SELECT note_content, created_at FROM public.contact_notes WHERE id_contacto = :id_contacto ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_contacto' => $id]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('Error en getNotes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Añade una nueva nota a un contacto.
     * @param string $contactId
     * @param string $noteContent
     * @return bool True si tuvo éxito, false si falló.
     */
    public function addNote(string $contactId, string $noteContent): bool
    {
        try {
            $sql = "INSERT INTO public.contact_notes (id_contacto, note_content) VALUES (:id_contacto, :note_content)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id_contacto' => $contactId, ':note_content' => $noteContent]);
        } catch (\PDOException $e) {
            error_log('Error en addNote: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene la distribución de estados de todos los contactos.
     * @return array|false
     */
    public function getStatusDistribution()
    {
        try {
            $stmt = $this->db->query("
                SELECT estado_suscripcion, COUNT(*) as total
                FROM public.contacts_operational
                GROUP BY estado_suscripcion
            ");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('Error en getStatusDistribution: ' . $e->getMessage());
            return false;
        }
    }
}

