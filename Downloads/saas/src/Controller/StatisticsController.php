<?php

namespace App\Controller;

use App\Service\StatsKpiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/stats')]
class StatisticsController extends AbstractController
{
    public function __construct(private readonly StatsKpiService $stats) {}

    #[Route('', name: 'stats_general')]
    public function general(): Response
    {
        return $this->render('stats/general.html.twig', [
            'caMensuel'       => $this->stats->caMensuelHT(),
            'depMensuelles'   => $this->stats->depensesMensuellesHT(),
            'valStock'        => $this->stats->valeurStock(),
            'nbDocsMensuel'   => $this->stats->nbDocsCreesMensuel(),
            'topClients'      => $this->stats->topClientsByCA(),
            'topFournisseurs' => $this->stats->topFournisseurs(),
            'ruptures'        => $this->stats->articlesEnRupture(),
            'topUsers'        => $this->stats->topUsersActifs(),
            'currentMonth'    => $this->stats->getCurrentMonth(),
        ]);
    }

    #[Route('/ventes', name: 'stats_ventes')]
    public function ventes(): Response
    {
        return $this->render('stats/ventes.html.twig', [
            'topClients'   => $this->stats->topClientsByCA(10),
            'currentMonth' => $this->stats->getCurrentMonth(),
        ]);
    }

    #[Route('/achats', name: 'stats_achats')]
    public function achats(): Response
    {
        return $this->render('stats/achats.html.twig', [
            'topFournisseurs' => $this->stats->topFournisseurs(10),
            'currentMonth'    => $this->stats->getCurrentMonth(),
        ]);
    }

    #[Route('/productions', name: 'stats_productions')]
    public function productions(): Response
    {
        return $this->render('stats/productions.html.twig', [
            'ruptures'     => $this->stats->articlesEnRupture(20),
            'currentMonth' => $this->stats->getCurrentMonth(),
        ]);
    }

    /* ===================== API JSON ===================== */

    #[Route('/api/kpi/ca-mois', name: 'api_kpi_ca_mois')]
    public function apiKpiCaMois(): JsonResponse {
        return $this->json(['value' => $this->stats->caMensuelHT()]);
    }

    #[Route('/api/kpi/dep-mois', name: 'api_kpi_dep_mois')]
    public function apiKpiDepMois(): JsonResponse {
        return $this->json(['value' => $this->stats->depensesMensuellesHT()]);
    }

    #[Route('/api/kpi/ruptures-count', name: 'api_kpi_ruptures_count')]
    public function apiKpiRupturesCount(): JsonResponse {
        return $this->json(['value' => count($this->stats->articlesEnRupture())]);
    }

    #[Route('/api/ventes/ca-par-jour', name: 'api_stats_ventes_ca_jour')]
    public function apiVentesCaJour(): JsonResponse {
        return $this->json($this->stats->caParJourMoisCourant());
    }

    #[Route('/api/achats/depenses-par-jour', name: 'api_stats_achats_dep_jour')]
    public function apiAchatsDepJour(): JsonResponse {
        return $this->json($this->stats->depensesParJourMoisCourant());
    }

    #[Route('/api/stock/valeur-par-depot', name: 'api_stats_stock_val_depot')]
    public function apiStockParDepot(): JsonResponse {
        return $this->json($this->stats->valeurStockParDepot());
    }

    #[Route('/api/docs/repartition-type', name: 'api_stats_docs_repartition')]
    public function apiDocsRepartition(): JsonResponse {
        return $this->json($this->stats->docsParTypeMoisCourant());
    }
}
