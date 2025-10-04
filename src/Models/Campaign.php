<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Campaign extends Database
{
    // ... create, getAllCampaigns, getKpis, getTopLeads, etc. se mantienen igual ...
    // NOTE: All previous methods are unchanged and remain here. This file is complete.

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

    public function getKpis(string $campaignId)
    {
        try {
            $stmt = $this->db->prepare("SELECT total_enviados, total_aperturas, total_clics FROM public.campaigns WHERE campaign_id = :campaign_id");
            $stmt->execute([':campaign_id' => $campaignId]);
            $data = $stmt->fetch();

            if (!$data) return false;

            $kpis = [
                'total_enviados' => (int)$data['total_enviados'],
                'total_aperturas' => (int)$data['total_aperturas'],
                'total_clics' => (int)$data['total_clics'],
                'tasa_apertura' => ($data['total_enviados'] > 0) ? ($data['total_aperturas'] / $data['total_enviados']) * 100 : 0,
                'tasa_clics_ctr' => ($data['total_enviados'] > 0) ? ($data['total_clics'] / $data['total_enviados']) * 100 : 0,
                'tasa_clics_ctor' => ($data['total_aperturas'] > 0) ? ($data['total_clics'] / $data['total_aperturas']) * 100 : 0,
            ];

            $stmt_bajas = $this->db->prepare("SELECT COUNT(DISTINCT sl.id_contacto) FROM public.sends_log sl JOIN public.contacts_operational co ON sl.id_contacto = co.id_contacto WHERE sl.campaign_id = :campaign_id AND co.estado_suscripcion = 'baja'");
            $stmt_bajas->execute([':campaign_id' => $campaignId]);
            $bajas = $stmt_bajas->fetchColumn();

            $kpis['tasa_bajas'] = ($data['total_enviados'] > 0) ? ($bajas / $data['total_enviados']) * 100 : 0;
            
            return $kpis;

        } catch (\PDOException $e) {
            error_log('Error al obtener KPIs: ' . $e->getMessage());
            return false;
        }
    }
    
    public function getTopLeads(string $campaignId)
    {
        try {
            $sql = "
                SELECT 
                    op.id_contacto,
                    COALESCE(NULLIF(op.nombre_empresa, ''), op.nombre_contacto) AS contacto_empresa,
                    op.rubro,
                    SUM(i.puntuacion_lead) AS puntuacion_total
                FROM public.interactions_log i
                JOIN public.contacts_operational op ON i.id_contacto = op.id_contacto
                WHERE i.campaign_id = :campaign_id
                GROUP BY op.id_contacto, contacto_empresa, op.rubro
                ORDER BY puntuacion_total DESC
                LIMIT 15;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':campaign_id' => $campaignId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('Error al obtener Top Leads: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * CORREGIDO: Ahora genera una serie de fechas completa para el período
     * y une los datos de interacciones, rellenando los días vacíos con 0.
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
                    ON DATE(i.timestamp AT TIME ZONE 'UTC' AT TIME ZONE 'America/Santiago') = ds.fecha 
                    AND i.campaign_id = :campaign_id
                GROUP BY ds.fecha
                ORDER BY ds.fecha ASC;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':campaign_id' => $campaignId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('Error en getInteractionsOverTime: ' . $e->getMessage());
            return [];
        }
    }

    public function getTodaysInteractionsByHour(string $campaignId): array
    {
        try {
            $sql = "
                SELECT
                    EXTRACT(HOUR FROM timestamp AT TIME ZONE 'UTC' AT TIME ZONE 'America/Santiago') as hora,
                    COUNT(CASE WHEN tipo_interaccion = 'apertura' THEN 1 END) AS aperturas,
                    COUNT(CASE WHEN tipo_interaccion = 'clic' THEN 1 END) AS clics
                FROM public.interactions_log
                WHERE
                    campaign_id = :campaign_id AND
                    DATE(timestamp AT TIME ZONE 'UTC' AT TIME ZONE 'America/Santiago') = CURRENT_DATE
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

    public function getInteractionHeatmap(string $campaignId): array
    {
        try {
            $sql = "
                SELECT
                    EXTRACT(DOW FROM timestamp AT TIME ZONE 'UTC' AT TIME ZONE 'America/Santiago') AS dia_semana,
                    EXTRACT(HOUR FROM timestamp AT TIME ZONE 'UTC' AT TIME ZONE 'America/Santiago') AS hora_dia,
                    COUNT(*) AS interacciones
                FROM public.interactions_log
                WHERE campaign_id = :campaign_id
                GROUP BY dia_semana, hora_dia
                ORDER BY dia_semana, hora_dia;
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':campaign_id' => $campaignId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('Error en getInteractionHeatmap: ' . $e->getMessage());
            return [];
        }
    }

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
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('Error en getInterestByIndustry: ' . $e->getMessage());
            return [];
        }
    }

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
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('Error en getInterestByRegion: ' . $e->getMessage());
            return [];
        }
    }
    
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
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('Error en getLatestInteractions: ' . $e->getMessage());
            return [];
        }
    }
    
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
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('Error en getLatestUnsubscribes: ' . $e->getMessage());
            return [];
        }
    }
}

