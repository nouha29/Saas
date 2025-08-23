<?php

namespace App\Controller;

use App\Service\StatsKpiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public function __construct(private readonly StatsKpiService $stats) {}

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
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

    // ❌ ENLEVER ce bloc (doublon de route)
    // #[Route('/api/stats/ca-12-mois', name: 'api_stats_ca_12_mois')]
    // public function ca12Mois(): JsonResponse
    // {
    //     return $this->json($this->stats->serieCa12Mois());
    // }
}
