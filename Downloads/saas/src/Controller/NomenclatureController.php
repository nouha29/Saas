<?php

namespace App\Controller;

use App\Entity\Nomenclature;
use App\Entity\Articles;
use App\Form\NomenclatureType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Composition;

use Psr\Log\LoggerInterface;

#[Route('/nomenclature')]
class NomenclatureController extends AbstractController
{
    private LoggerInterface $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    #[Route('/', name: 'nomenclature_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $query = $entityManager->createQuery(
            'SELECT DISTINCT p 
         FROM App\Entity\Articles p 
         WHERE EXISTS (
             SELECT 1 
             FROM App\Entity\Nomenclature n 
             WHERE n.produit = p.id
         )'
        );

        $produits = $query->getResult();
        return $this->render('nomenclature/index.html.twig', [
            'produits' => $produits
        ]);
    }

    #[Route('/new', name: 'nomenclature_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $nomenclature = new Nomenclature();
        $form = $this->createForm(NomenclatureType::class, $nomenclature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all();
            $this->logger->info('JSON log entry', ['json_data' => json_encode($data)]);

            $produit = $data['nomenclature']['produit'];
            $produit = $entityManager->getRepository(Articles::class)->find($produit);
            $compositions = $data['nomenclature']['compositions'];

            $existingNomenclature = $entityManager->getRepository(Nomenclature::class)
                ->findOneBy(['produit' => $produit]);

            if ($existingNomenclature) {
                $this->addFlash('error', 'Ce produit possède déjà une nomenclature.');
                return $this->redirectToRoute('nomenclature_new');
            }

            foreach ($compositions as $composition) {
                $matiere = $composition['matiere'];
                $matiere = $entityManager->getRepository(Articles::class)->find($matiere);
                $consommation = $composition['consommation'];

                $nomenclatureItem = new Nomenclature();
                $nomenclatureItem->setProduit($produit);
                $nomenclatureItem->setMatiere($matiere);
                $nomenclatureItem->setConsommation($consommation);

                $entityManager->persist($nomenclatureItem);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Nomenclature créée avec succès!');
            return $this->redirectToRoute('nomenclature_index');
        }

        return $this->render('nomenclature/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/{produit_id}/show', name: 'nomenclature_show', methods: ['GET'])]
    public function show(int $produit_id, EntityManagerInterface $entityManager): Response
    {
        $produit = $entityManager->getRepository(Articles::class)->find($produit_id);

        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé');
        }
        $nomenclatures = $entityManager->getRepository(Nomenclature::class)->findBy(
            ['produit' => $produit_id],
            ['id' => 'ASC']
        );
        return $this->render('nomenclature/show.html.twig', [
            'produit' => $produit,
            'nomenclatures' => $nomenclatures,
        ]);
    }

    #[Route('/{id}/edit', name: 'nomenclature_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, Articles $produit): Response
    {
        $nomenclatureLignes = $entityManager->getRepository(Nomenclature::class)->findBy(['produit' => $produit]);
        $nomenclature = new Nomenclature();
        $nomenclature->setProduit($produit);
        foreach ($nomenclatureLignes as $ligne) {
            $composition = new Composition();
            $composition->setMatiere($ligne->getMatiere());
            $composition->setConsommation($ligne->getConsommation());
            $nomenclature->addComposition($composition);
        }

        $form = $this->createForm(NomenclatureType::class, $nomenclature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($nomenclatureLignes as $ligne) {
                $entityManager->remove($ligne);
            }
            $compositions = $nomenclature->getCompositions();
            foreach ($compositions as $composition) {
                $newLigne = new Nomenclature();
                $newLigne->setProduit($produit);
                $newLigne->setMatiere($composition->getMatiere());
                $newLigne->setConsommation($composition->getConsommation());
                $entityManager->persist($newLigne);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Nomenclature mise à jour avec succès!');
            return $this->redirectToRoute('nomenclature_index');
        }

        return $this->render('nomenclature/edit.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}', name: 'nomenclature_delete', methods: ['POST'])]
    public function delete(Request $request, Nomenclature $nomenclature, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $nomenclature->getId(), $request->request->get('_token'))) {
            $entityManager->remove($nomenclature);
            $entityManager->flush();
        }

        return $this->redirectToRoute('nomenclature_index', [], Response::HTTP_SEE_OTHER);
    }
}
