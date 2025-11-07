<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Campaign extends Database
{
    /**
     * Inserta una nueva campaña en la tabla 'campaigns'.
     * @param string $name El nombre de la nueva campaña.
     * @return bool True si la creación fue exitosa, false si falló.
     */
    public function create(string $name): bool
    {
        try {
            $campaignId = 'camp_' . bin2hex(random_bytes(12));
            $sql = "INSERT INTO public.campaigns (campaign_id, nombre_campana) VALUES (:campaign_id, :nombre_campana)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':campaign_id' => $campaignId, ':nombre_campana' => $name]);
        } catch (\PDOException $e) {
            error_log('Error al crear campaña: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene una lista de todas las campañas para el selector como un array de objetos.
     * @return array|false
     */
    public function getAllCampaigns()
    {
        try {
            $stmt = $this->db->query("SELECT campaign_id, nombre_campana FROM public.campaigns ORDER BY fecha_lanzamiento DESC");
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            error_log('Error al obtener todas las campañas: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * CORREGIDO: La función ahora es más eficiente.
     * Lee todos los contadores pre-calculados de la tabla 'campaigns' en una sola consulta.
     */
    public function getKpis(string $campaignId)
    {
        try {
            // Se leen todos los contadores de una sola vez
            $stmt = $this->db->prepare("
                SELECT 
                    total_enviados, 
                    total_aperturas, 
                    total_clics, 
                    total_bajas, 
                    total_rebotes 
                FROM public.campaigns 
                WHERE campaign_id = :campaign_id
            ");
            $stmt->execute([':campaign_id' => $campaignId]);
            $data = $stmt->fetch();

            if (!$data) return false;

            // Se obtienen las aperturas únicas para las tasas (CTOR)
            $stmt_opens_unique = $this->db->prepare("
                SELECT COUNT(DISTINCT id_contacto) 
                FROM public.interactions_log 
                WHERE campaign_id = :campaign_id AND tipo_interaccion = 'apertura'
            ");
            $stmt_opens_unique->execute([':campaign_id' => $campaignId]);
            $total_aperturas_unicas = $stmt_opens_unique->fetchColumn();

            $enviados = (int)$data['total_enviados'];
            $aperturas_unicas = (int)$total_aperturas_unicas;
            
            $kpis = [
                'total_enviados'     => $enviados,
                'total_aperturas'    => (int)$data['total_aperturas'], // Eventos totales de apertura
                'total_clics'        => (int)$data['total_clics'],
                'total_bajas'        => (int)$data['total_bajas'],
                'total_rebotes'      => (int)$data['total_rebotes'],
                
                // --- TASAS ---
                'tasa_apertura'   => ($enviados > 0) ? ($aperturas_unicas / $enviados) * 100 : 0, // Basada en usuarios únicos
                'tasa_clics_ctr'  => ($enviados > 0) ? ((int)$data['total_clics'] / $enviados) * 100 : 0,
                'tasa_clics_ctor' => ($aperturas_unicas > 0) ? ((int)$data['total_clics'] / $aperturas_unicas) * 100 : 0,
                'tasa_bajas'      => ($enviados > 0) ? ((int)$data['total_bajas'] / $enviados) * 100 : 0,
                'tasa_rebotes'    => ($enviados > 0) ? ((int)$data['total_rebotes'] / $enviados) * 100 : 0,
            ];
            
            return $kpis;

        } catch (\PDOException $e) {
            error_log('Error al obtener KPIs: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene el ranking de leads (empresas o contactos) con mayor puntuación.
     * @param string $campaignId
     * @return array|false
     */
    public function getTopLeads(string $campaignId)
    {
        try {
            $sql = "
                SELECT 
                    op.id_contacto,
                    COALESCE(NULLIF(op.nombre_empresa, ''), op.nombre_contacto) AS contacto_empresa,
                    op.rubro,
                    
                    -- --- INICIO DE LA MODIFICACIÓN ---
                    op.estado_suscripcion,
                    -- --- FIN DE LA MODIFICACIÓN ---

                    SUM(i.puntuacion_lead) AS puntuacion_total
                FROM public.interactions_log i
                JOIN public.contacts_operational op ON i.id_contacto = op.id_contacto
                WHERE i.campaign_id = :campaign_id

                -- --- INICIO DE LA MODIFICACIÓN ---
                GROUP BY op.id_contacto, contacto_empresa, op.rubro, op.estado_suscripcion
                -- --- FIN DE LA MODIFICACIÓN ---
                
                ORDER BY puntuacion_total DESC
                LIMIT 15;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':campaign_id' => $campaignId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error al obtener Top Leads: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene el recuento de interacciones (aperturas y clics) por día para un período determinado.
     * Genera una serie de fechas completa para rellenar los días vacíos con 0.
     * @param string $campaignId
     * @param int $periodDays
     * @return array
     */
    public function getInteractionsOverTime(string $campaignId, int $periodDays = 30): array
    {
        try {
            // Ajuste para que '7 días' incluya los últimos 7 días completos
            $interval = $periodDays - 1;

            $sql = "
                WITH date_series AS (
                    SELECT generate_series(
                        (NOW() - INTERVAL '{$interval} days')::date,
                        NOW()::date,
                        '1 day'::interval
                    )::date AS fecha
                )
                SELECT
                    ds.fecha,
                    COALESCE(SUM(CASE WHEN i.tipo_interaccion = 'apertura' THEN 1 ELSE 0 END), 0) AS aperturas,
                    COALESCE(SUM(CASE WHEN i.tipo_interaccion = 'clic' THEN 1 ELSE 0 END), 0) AS clics
                FROM date_series ds
                LEFT JOIN public.interactions_log i 
                    ON DATE(i.timestamp AT TIME ZONE 'America/Santiago') = ds.fecha 
                    AND i.campaign_id = :campaign_id
                GROUP BY ds.fecha
                ORDER BY ds.fecha ASC;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':campaign_id' => $campaignId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error en getInteractionsOverTime: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las interacciones del día actual, agrupadas por hora.
     * @param string $campaignId
     * @return array
     */
    public function getTodaysInteractionsByHour(string $campaignId): array
    {
        try {
            $sql = "
                SELECT
                    EXTRACT(HOUR FROM timestamp AT TIME ZONE 'America/Santiago') as hora,
                    COUNT(CASE WHEN tipo_interaccion = 'apertura' THEN 1 END) AS aperturas,
                    COUNT(CASE WHEN tipo_interaccion = 'clic' THEN 1 END) AS clics
                FROM public.interactions_log
                WHERE
                    campaign_id = :campaign_id AND
                    DATE(timestamp AT TIME ZONE 'America/Santiago') = CURRENT_DATE
                GROUP BY hora
                ORDER BY hora ASC;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':campaign_id' => $campaignId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error en getTodaysInteractionsByHour: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los datos para el mapa de calor de interacciones.
     * @param string $campaignId
     * @return array
     */
    public function getInteractionHeatmap(string $campaignId): array
    {
        try {
            $sql = "
                SELECT
                    EXTRACT(DOW FROM timestamp AT TIME ZONE 'America/Santiago') AS dia_semana,
                    EXTRACT(HOUR FROM timestamp AT TIME ZONE 'America/Santiago') AS hora_dia,
                    COUNT(*) AS interacciones
                FROM public.interactions_log
                WHERE campaign_id = :campaign_id
                GROUP BY dia_semana, hora_dia
                ORDER BY dia_semana, hora_dia;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':campaign_id' => $campaignId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error en getInteractionHeatmap: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los datos de interés por rubro.
     * @param string $campaignId
     * @return array
     */
    public function getInterestByIndustry(string $campaignId): array
    {
        try {
            $sql = "
                SELECT op.rubro, COUNT(i.id) as total
                FROM public.interactions_log i
                JOIN public.contacts_operational op ON i.id_contacto = op.id_contacto
                WHERE i.campaign_id = :campaign_id AND op.rubro IS NOT NULL AND op.rubro <> ''
                GROUP BY op.rubro
                ORDER BY total DESC
                LIMIT 7;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':campaign_id' => $campaignId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error en getInterestByIndustry: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los datos de interés por región.
     * @param string $campaignId
     * @return array
     */
    public function getInterestByRegion(string $campaignId): array
    {
        try {
            $sql = "
                SELECT cm.region, COUNT(i.id) as total
                FROM public.interactions_log i
                JOIN public.contacts_master cm ON i.id_contacto = cm.id_contacto
                WHERE i.campaign_id = :campaign_id AND cm.region IS NOT NULL AND cm.region <> ''
                GROUP BY cm.region
                ORDER BY total DESC
                LIMIT 7;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':campaign_id' => $campaignId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error en getInterestByRegion: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * NUEVA FUNCIÓN: Obtiene los datos de interés por comuna.
     * @param string $campaignId
     * @return array
     */
    public function getInterestByCommune(string $campaignId): array
    {
        try {
            $sql = "
                SELECT cm.comuna, COUNT(i.id) as total
                FROM public.interactions_log i
                JOIN public.contacts_master cm ON i.id_contacto = cm.id_contacto
                WHERE i.campaign_id = :campaign_id AND cm.comuna IS NOT NULL AND cm.comuna <> ''
                GROUP BY cm.comuna
                ORDER BY total DESC
                LIMIT 7;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':campaign_id' => $campaignId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error en getInterestByCommune: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene las últimas interacciones (aperturas/clics) sin límite.
     * @param string $campaignId
     * @return array
     */
    public function getLatestInteractions(string $campaignId): array
    {
        try {
            $sql = "
                SELECT
                    op.id_contacto,
                    i.tipo_interaccion,
                    i.timestamp,
                    COALESCE(NULLIF(op.nombre_empresa, ''), op.nombre_contacto) AS interactor_name
                FROM public.interactions_log i
                JOIN public.contacts_operational op ON i.id_contacto = op.id_contacto
                WHERE i.campaign_id = :campaign_id
                ORDER BY i.timestamp DESC;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':campaign_id' => $campaignId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error en getLatestInteractions: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene los últimos contactos que se dieron de baja durante esta campaña, sin límite.
     * @param string $campaignId
     * @return array
     */
    public function getLatestUnsubscribes(string $campaignId): array
    {
        try {
             $sql = "
                SELECT
                    op.id_contacto,
                    sl.timestamp,
                    COALESCE(NULLIF(op.nombre_empresa, ''), op.nombre_contacto) AS interactor_name
                FROM public.sends_log sl
                JOIN public.contacts_operational op ON sl.id_contacto = op.id_contacto
                WHERE sl.campaign_id = :campaign_id
                AND op.estado_suscripcion = 'baja'
                ORDER BY sl.timestamp DESC;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':campaign_id' => $campaignId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error en getLatestUnsubscribes: ' . $e->getMessage());
            return [];
        }
    }
}