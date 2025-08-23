<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Form\DocumentsType;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DocumentsRepository;
use App\Entity\Documents;
use App\Repository\UsersRepository;


#[Route('/documents')]
class DocumentsController extends AbstractController
{
    private LoggerInterface $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    #[Route('/', name: 'app_documents_index', methods: ['GET'])]
    public function index(Request $request, DocumentsRepository $documentsRepository): Response
    {
        $filter = $request->query->get('Doc');
        $documents = match ($filter) {
            'DA' => $documentsRepository->findByType('Devis achat'),
            'CA' => $documentsRepository->findByType('Commande achat'),
            'FA' => $documentsRepository->findByType('Facture achat'),
            'FAA' => $documentsRepository->findByType('Facture achat avoire'),
            'BE' => $documentsRepository->findByType('Bon d\'entrée'),
            'BT' => $documentsRepository->findByType('Bon de transfert'),
            'BR' => $documentsRepository->findByType('Bon de retour'),
            // Ventes
            'DV' => $documentsRepository->findByType('Devis vente'),
            'CV' => $documentsRepository->findByType('Commande vente'),
            'FV' => $documentsRepository->findByType('Facture vente'),
            'FVA' => $documentsRepository->findByType('Facture vente avoire'),
            'BS' => $documentsRepository->findByType('Bon de sortie'),
            'BL' => $documentsRepository->findByType('Bon de livraison'),
            // Commun
            'Inv' => $documentsRepository->findByType('Inventaire'),
            // Production 
            'creation_of' => $documentsRepository->findByType('Création OF'),
            'demande_besoins' => $documentsRepository->findByType('Demande besoins'),
            default => $documentsRepository->findAll()
        };
        return $this->render('documents/index.html.twig', [
            'documents' => $documents,
            'current_type' => $filter
        ]);
    }
    #[Route('/new', name: 'app_documents_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UsersRepository $usersRepository): Response
    {
        $document = new Documents();
        $typeFromRoute = $request->query->get('Doc');
        $this->logger->info('Type from route: ' . $typeFromRoute);

        if ($typeFromRoute) {
            $document->setType($typeFromRoute);
        }

        $form = $this->createForm(DocumentsType::class, $document, [
            'hide_type' => (bool) $typeFromRoute
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $rootUser = $usersRepository->find(49);
            if (!$rootUser) {
                throw $this->createNotFoundException('Utilisateur root (id=49) non trouvé');
            }
            $document->setCreateBy($rootUser);
            $reference = $entityManager->getRepository(Documents::class)
                ->getNextReference($document->getType());
            $document->setReference($reference);
            foreach ($document->getLignes() as $ligne) {
                $ligne->setDocument($document);
                $ligne->calculatePrixTotalHt();
            }
            $document->updateTotals();

            $entityManager->persist($document);
            $entityManager->flush();

            $this->addFlash('success', 'Document créé avec succès!');
            return $this->redirectToRoute('app_documents_index');
        }

        return $this->render('documents/new&edit.html.twig', [
            'form' => $form->createView(),
            'document' => $document,
            'current_type' => $typeFromRoute
        ]);
    }

    #[Route('/{id}/edit', name: 'app_documents_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Documents $document, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DocumentsType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $document->updateTotals();
            $entityManager->flush();

            $this->addFlash('success', 'Document mis à jour avec succès!');
            return $this->redirectToRoute('app_documents_index');
        }

        return $this->render('documents/new&edit.html.twig', [
            'form' => $form->createView(),
            'document' => $document,
            'current_type' => $document->getType()
        ]);
    }
    #[Route('/{id}', name: 'app_documents_delete', methods: ['POST'])]
    public function delete(Request $request, Documents $document, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $document->getId(), $request->request->get('_token'))) {
            foreach ($document->getLignes() as $ligne) {
                $entityManager->remove($ligne);
            }
            $entityManager->remove($document);
            $entityManager->flush();
            $this->addFlash('success', 'Document supprimé avec succès');
        }
        return $this->redirectToRoute('app_documents_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}', name: 'app_documents_show', methods: ['GET'])]
    public function show(
        Documents $document,
        DocumentsRepository $documentsRepository
    ): Response {
        $document = $documentsRepository->find($document->getId());
        return $this->render('documents/show.html.twig', [
            'document' => $document,
        ]);
    }
}
