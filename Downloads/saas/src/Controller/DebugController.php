<?php
namespace App\Controller;

use App\Service\StatsKpiService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DebugController extends AbstractController
{
    public function __construct(private readonly StatsKpiService $stats, private readonly Connection $db) {}

    #[Route('/debug/kpi', name: 'debug_kpi')]
    public function debugKpi(): JsonResponse
    {
        // Récupère la fenêtre calculée par StatsKpiService
        $ref = new \ReflectionClass($this->stats);
        $m   = $ref->getMethod('monthBounds');
        $m->setAccessible(true);
        [$start,$end] = $m->invoke($this->stats);

        // Quelques checks SQL directs
        $fv = $this->db->fetchAssociative("
            SELECT COUNT(*) n, COALESCE(SUM(montant_ht),0) ca
            FROM documents
            WHERE type='Facture vente' AND TRIM(LOWER(status))='confirmé'
              AND doc_date >= :s AND doc_date < :e
        ", ['s'=>$start->format('Y-m-d H:i:s'),'e'=>$end->format('Y-m-d H:i:s')]);

        $fa = $this->db->fetchAssociative("
            SELECT COUNT(*) n, COALESCE(SUM(montant_ht),0) tot
            FROM documents
            WHERE type='Facture achat' AND TRIM(LOWER(status))='confirmé'
              AND doc_date >= :s AND doc_date < :e
        ", ['s'=>$start->format('Y-m-d H:i:s'),'e'=>$end->format('Y-m-d H:i:s')]);

        $parType = $this->db->fetchAllAssociative("
            SELECT type, COUNT(*) nb
            FROM documents
            WHERE doc_date >= :s AND doc_date < :e
            GROUP BY type
            ORDER BY nb DESC
        ", ['s'=>$start->format('Y-m-d H:i:s'),'e'=>$end->format('Y-m-d H:i:s')]);

        return $this->json([
            'currentMonth_from_service' => $this->stats->getCurrentMonth(),
            'start' => $start->format('Y-m-d H:i:s'),
            'end'   => $end->format('Y-m-d H:i:s'),
            'FV_confirmes' => $fv,
            'FA_confirmes' => $fa,
            'documents_par_type' => $parType,
        ]);
    }
}
