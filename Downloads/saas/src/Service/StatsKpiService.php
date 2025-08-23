<?php
namespace App\Service;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

class StatsKpiService
{
    private ?string $currentMonth = null;
public function getCurrentMonth(): ?string { return $this->currentMonth; }

    public function __construct(
        private readonly Connection $db,
        private readonly RequestStack $requestStack
    ) {}

    /** Fenêtre [start, end[ du mois demandé, sinon dernier mois non vide */
   // dans StatsKpiService


private function monthBounds(): array
{
    $request = $this->requestStack->getCurrentRequest();
    $param = $request?->query->get('month'); // "YYYY-MM"

    // 1) Si l’utilisateur force un mois → on respecte
    if ($param && preg_match('/^\d{4}-\d{2}$/', $param)) {
        $start = new \DateTimeImmutable($param . '-01 00:00:00');
        $end   = $start->modify('first day of next month');
        $this->currentMonth = $start->format('Y-m');
        return [$start, $end];
    }

    // 2) Chercher le DERNIER mois où il y a des FV/FA CONFIRMÉES
    $row = $this->db->fetchAssociative("
        SELECT DATE_FORMAT(MAX(doc_date), '%Y-%m-01 00:00:00') AS start
        FROM documents
        WHERE (type IN ('Facture vente','Facture achat'))
          AND TRIM(LOWER(status)) = 'confirmé'
    ");

    if (!empty($row['start'])) {
        $start = new \DateTimeImmutable($row['start']);
    } else {
        // 3) À défaut, tomber sur le DERNIER mois où il y a au moins un document (create_at)
        $row2 = $this->db->fetchAssociative("
            SELECT DATE_FORMAT(MAX(create_at), '%Y-%m-01 00:00:00') AS start
            FROM documents
        ");
        if (!empty($row2['start'])) {
            $start = new \DateTimeImmutable($row2['start']);
        } else {
            // 4) Base complètement vide → mois courant
            $start = new \DateTimeImmutable('first day of this month 00:00:00');
        }
    }

    $end = $start->modify('first day of next month');
    $this->currentMonth = $start->format('Y-m');
    return [$start, $end];
}



    // 1) CA mensuel HT = SUM(montant_ht) des FV Confirmé du mois (col. type='Facture vente', status='Confirmé')
    // 1) CA mensuel (HT) - Facture vente du mois (sans filtrer status pour valider le flux)
public function caMensuelHT(): float {
    [$start,$end] = $this->monthBounds();
    $sql = "SELECT COALESCE(SUM(montant_ht),0)
            FROM documents
            WHERE type='Facture vente' AND status='Confirmé'
              AND doc_date >= :start AND doc_date < :end";
    return (float)$this->db->fetchOne($sql, ['start'=>$start->format('Y-m-d H:i:s'), 'end'=>$end->format('Y-m-d H:i:s')]);
}

public function depensesMensuellesHT(): float {
    [$start,$end] = $this->monthBounds();
    $sql = "SELECT COALESCE(SUM(montant_ht),0)
            FROM documents
            WHERE type='Facture achat' AND status='Confirmé'
              AND doc_date >= :start AND doc_date < :end";
    return (float)$this->db->fetchOne($sql, ['start'=>$start->format('Y-m-d H:i:s'), 'end'=>$end->format('Y-m-d H:i:s')]);
}

public function topClientsByCA(int $limit=5): array {
    [$start,$end] = $this->monthBounds();
    $sql = "SELECT u.username AS customer_name, d.destinataire_id AS customer_id,
                   COALESCE(SUM(d.montant_ht),0) AS ca
            FROM documents d
            LEFT JOIN users u ON u.id = d.destinataire_id
            WHERE d.type='Facture vente' AND d.status='Confirmé'
              AND d.doc_date >= :start AND d.doc_date < :end
            GROUP BY d.destinataire_id, u.username
            ORDER BY ca DESC
            LIMIT {$limit}";
    return $this->db->fetchAllAssociative($sql, ['start'=>$start->format('Y-m-d H:i:s'),'end'=>$end->format('Y-m-d H:i:s')]);
}

public function topFournisseurs(int $limit=5): array {
    [$start,$end] = $this->monthBounds();
    $sql = "SELECT u.username AS supplier_name, d.emetteur_id AS supplier_id,
                   COALESCE(SUM(d.montant_ht),0) AS total
            FROM documents d
            LEFT JOIN users u ON u.id = d.emetteur_id
            WHERE d.type='Facture achat' AND d.status='Confirmé'
              AND d.doc_date >= :start AND d.doc_date < :end
            GROUP BY d.emetteur_id, u.username
            ORDER BY total DESC
            LIMIT {$limit}";
    return $this->db->fetchAllAssociative($sql, ['start'=>$start->format('Y-m-d H:i:s'),'end'=>$end->format('Y-m-d H:i:s')]);
}


// 5) Valeur stock (fallback sans window functions)
public function valeurStock(): float {
    // Dernier prix d’achat par article via sous-requête
    $sql = "
      SELECT COALESCE(SUM(s.qte_stock_dispo * COALESCE(lp.prix_unitaire_ht,0)),0) AS stock_value
      FROM stock s
      LEFT JOIN (
        SELECT dl1.id_article, dl1.prix_unitaire_ht
        FROM documentslignes dl1
        JOIN documents d1 ON d1.id = dl1.id_document
        WHERE d1.type='Facture achat'
          AND d1.doc_date = (
            SELECT MAX(d2.doc_date)
            FROM documents d2
            JOIN documentslignes dl2 ON dl2.id_document = d2.id
            WHERE d2.type='Facture achat' AND dl2.id_article = dl1.id_article
          )
      ) lp ON lp.id_article = s.id_article";
    return (float)$this->db->fetchOne($sql);
}

// 6) Ruptures (seuil 20 par défaut)
public function articlesEnRupture(int $seuil=20): array {
    $sql = "SELECT a.id, a.reference AS name, COALESCE(SUM(s.qte_stock_dispo),0) AS qty
            FROM articles a
            LEFT JOIN stock s ON s.id_article = a.id
            GROUP BY a.id, a.reference
            HAVING qty <= :seuil
            ORDER BY qty ASC";
    $rows = $this->db->fetchAllAssociative($sql, ['seuil'=>$seuil]);
    foreach ($rows as &$r) { $r['reorder_threshold'] = $seuil; }
    return $rows;
}

// 7) # docs créés (mois) - colonne create_at existe dans ton SQL
public function nbDocsCreesMensuel(): int {
    [$start,$end] = $this->monthBounds();
    $sql = "SELECT COUNT(*) FROM documents
            WHERE create_at >= :start AND create_at < :end";
    return (int)$this->db->fetchOne($sql, ['start'=>$start->format('Y-m-d H:i:s'),'end'=>$end->format('Y-m-d H:i:s')]);
}


    // 8) Top users (créateurs) du mois
    public function topUsersActifs(int $limit=5): array
    {
        [$start, $end] = $this->monthBounds();
        $sql = "SELECT create_by AS user_id, COUNT(*) AS nb
                FROM documents
                WHERE create_at >= :start AND create_at < :end
                GROUP BY create_by
                ORDER BY nb DESC
                LIMIT $limit";
        return $this->db->fetchAllAssociative($sql, ['start'=>$start->format('Y-m-d H:i:s'), 'end'=>$end->format('Y-m-d H:i:s')]);
    }

    // Série CA des 12 derniers mois (pour le graph)
    public function serieCa12Mois(): array
    {
        $points = [];
        $now = new \DateTimeImmutable('first day of this month');
        for ($i=11; $i>=0; $i--) {
            $start = $now->modify("-{$i} months");
            $end   = $start->modify('first day of next month');
            $sql = "SELECT COALESCE(SUM(montant_ht),0)
                    FROM documents
                    WHERE type='Facture vente'
                      AND status='Confirmé'
                      AND doc_date >= :start AND doc_date < :end";
            $val = (float)$this->db->fetchOne($sql, [
                'start'=>$start->format('Y-m-d H:i:s'),
                'end'  =>$end->format('Y-m-d H:i:s'),
            ]);
            $points[] = ['label'=>$start->format('Y-m'), 'value'=>$val];
        }
        return $points;
    }

    /** CA par jour (mois courant) – ventes */
public function caParJourMoisCourant(): array
{
    [$start, $end] = $this->monthBounds();
    $sql = "
      SELECT DATE(doc_date) as d, COALESCE(SUM(montant_ht),0) as ca
      FROM documents
      WHERE type='Facture vente' AND status='Confirmé'
        AND doc_date >= :start AND doc_date < :end
      GROUP BY DATE(doc_date)
      ORDER BY d ASC";
    $rows = $this->db->fetchAllAssociative($sql, [
        'start'=>$start->format('Y-m-d H:i:s'),
        'end'=>$end->format('Y-m-d H:i:s'),
    ]);
    return array_map(fn($r)=>['label'=>$r['d'],'value'=>(float)$r['ca']], $rows);
}

/** Dépenses par jour (mois courant) – achats */
public function depensesParJourMoisCourant(): array
{
    [$start, $end] = $this->monthBounds();
    $sql = "
      SELECT DATE(doc_date) as d, COALESCE(SUM(montant_ht),0) as dep
      FROM documents
      WHERE type='Facture achat' AND status='Confirmé'
        AND doc_date >= :start AND doc_date < :end
      GROUP BY DATE(doc_date)
      ORDER BY d ASC";
    $rows = $this->db->fetchAllAssociative($sql, [
        'start'=>$start->format('Y-m-d H:i:s'),
        'end'=>$end->format('Y-m-d H:i:s'),
    ]);
    return array_map(fn($r)=>['label'=>$r['d'],'value'=>(float)$r['dep']], $rows);
}

/** Valeur de stock par dépôt */
public function valeurStockParDepot(): array
{
    // dernier prix d’achat unitaire par article (voir valeurStock())
    $sql = <<<SQL
    WITH last_purchase AS (
      SELECT dl.id_article,
             dl.prix_unitaire_ht,
             d.doc_date,
             ROW_NUMBER() OVER (PARTITION BY dl.id_article ORDER BY d.doc_date DESC, d.id DESC) AS rn
      FROM documentslignes dl
      JOIN documents d ON d.id = dl.id_document
      WHERE d.type='Facture achat'
    )
    SELECT dep.intitule AS depot, 
           COALESCE(SUM(s.qte_stock_dispo * COALESCE(lp.prix_unitaire_ht,0)),0) AS val
    FROM stock s
    JOIN depots dep ON dep.id = s.id_depot
    LEFT JOIN last_purchase lp ON lp.id_article = s.id_article AND lp.rn = 1
    GROUP BY dep.intitule
    ORDER BY val DESC
    SQL;
    $rows = $this->db->fetchAllAssociative($sql);
    return array_map(fn($r)=>['label'=>$r['depot'],'value'=>(float)$r['val']], $rows);
}

/** Répartition des documents par type (mois courant) */
public function docsParTypeMoisCourant(): array
{
    [$start, $end] = $this->monthBounds();
    $sql = "
      SELECT type, COUNT(*) as nb
      FROM documents
      WHERE doc_date >= :start AND doc_date < :end
      GROUP BY type
      ORDER BY nb DESC";
    $rows = $this->db->fetchAllAssociative($sql, [
        'start'=>$start->format('Y-m-d H:i:s'),
        'end'=>$end->format('Y-m-d H:i:s'),
    ]);
    return array_map(fn($r)=>['label'=>$r['type'],'value'=>(int)$r['nb']], $rows);
}

}

