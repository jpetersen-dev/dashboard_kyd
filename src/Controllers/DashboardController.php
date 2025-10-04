<?php

namespace App\Controllers;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\User; // <-- Se añade el modelo de User

class DashboardController
{
    public function __construct()
    {
        // Este "constructor" se ejecuta antes que cualquier otro método.
        // Protege todo el controlador.
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
    }

    public function index()
    {
        // ... (El método index no cambia, sigue cargando todos los datos la primera vez) ...
        $campaignModel = new Campaign();
        $contactModel = new Contact();
        $userModel = new User(); // <-- Se instancia el modelo de User

        $allCampaigns = $campaignModel->getAllCampaigns();
        
        if (empty($allCampaigns)) {
            $selectedCampaign = null; $kpis = null; $topLeads = []; $latestInteractions = []; $latestUnsubscribes = []; $chartJSData = [];
            require_once __DIR__ . '/../Views/dashboard.php';
            return;
        }

        $selectedCampaign = $allCampaigns[0];
        $selectedCampaignId = $allCampaigns[0]->campaign_id;

        if (isset($_GET['campaign_id'])) {
            $urlCampaignId = $_GET['campaign_id'];
            foreach ($allCampaigns as $campaign) {
                if ($campaign->campaign_id === $urlCampaignId) {
                    $selectedCampaign = $campaign; $selectedCampaignId = $campaign->campaign_id;
                    break;
                }
            }
        }
        
        $selectedPeriod = $_GET['period'] ?? '30';
        $interactionsOverTime = [];
        if ($selectedPeriod === 'today') {
            $interactionsOverTime = $campaignModel->getTodaysInteractionsByHour($selectedCampaignId);
        } else {
            $periodDays = filter_var($selectedPeriod, FILTER_VALIDATE_INT, ['options' => ['default' => 30]]);
            $interactionsOverTime = $campaignModel->getInteractionsOverTime($selectedCampaignId, $periodDays);
        }

        $kpis = $campaignModel->getKpis($selectedCampaignId);
        $topLeads = $campaignModel->getTopLeads($selectedCampaignId);
        $latestInteractions = $campaignModel->getLatestInteractions($selectedCampaignId);
        $latestUnsubscribes = $campaignModel->getLatestUnsubscribes($selectedCampaignId);
        $contactStatus = $contactModel->getStatusDistribution();
        $interestByIndustry = $campaignModel->getInterestByIndustry($selectedCampaignId);
        $interestByRegion = $campaignModel->getInterestByRegion($selectedCampaignId);
        $interactionHeatmap = $campaignModel->getInteractionHeatmap($selectedCampaignId);
        
        $chartJSData = [
            'contactStatus' => $contactStatus,
            'interactionsOverTime' => $interactionsOverTime,
            'interactionHeatmap' => $interactionHeatmap,
            'interestByIndustry' => $interestByIndustry,
            'interestByRegion' => $interestByRegion,
            'selectedPeriod' => $selectedPeriod,
            'selectedCampaignId' => $selectedCampaignId,
        ];

        require_once __DIR__ . '/../Views/dashboard.php';
    }

    /**
     * NUEVO MÉTODO API: Devuelve solo los datos para el gráfico de interacciones.
     * Es mucho más rápido porque solo ejecuta una consulta.
     */
    public function getInteractionsData(string $campaignId, string $period)
    {
        header('Content-Type: application/json');
        $campaignModel = new Campaign();
        $data = [];

        if ($period === 'today') {
            $data = $campaignModel->getTodaysInteractionsByHour($campaignId);
        } else {
            $periodDays = filter_var($period, FILTER_VALIDATE_INT, ['options' => ['default' => 30]]);
            $data = $campaignModel->getInteractionsOverTime($campaignId, $periodDays);
        }

        echo json_encode($data);
    }

    public function createCampaign()
    {
        // ... (Este método no cambia) ...
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['nombre_campana'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El nombre de la campaña es obligatorio.']);
            return;
        }
        $campaignName = trim(strip_tags($data['nombre_campana']));
        $campaignModel = new Campaign();
        $result = $campaignModel->create($campaignName);
        if ($result) {
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Campaña creada con éxito.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error interno al guardar la campaña.']);
        }
    }
}

