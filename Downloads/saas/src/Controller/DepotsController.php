<?php

namespace App\Controller;

use App\Entity\Depots;
use App\Form\DepotsType;
use App\Repository\DepotsRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/depots')]
class DepotsController extends AbstractController
{
    #[Route('/', name: 'app_depots_index', methods: ['GET'])]
    public function index(DepotsRepository $depotsRepository): Response
    {
        return $this->render('depots/index.html.twig', [
            'depots' => $depotsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_depots_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        DepotsRepository $depotsRepository,
        UsersRepository $usersRepository
    ): Response {
        $depot = new Depots();
        $form = $this->createForm(DepotsType::class, $depot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rootUser = $usersRepository->find(49);
            if (!$rootUser) {
                throw $this->createNotFoundException('Utilisateur root (id=49) non trouvé');
            }

            $depot->setCreateBy($rootUser);
            $depot->setCreateAt(new \DateTime());

            $depotsRepository->save($depot, true);

            $this->addFlash('success', 'Dépôt créé avec succès.');
            return $this->redirectToRoute('app_depots_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('depots/new&edit.html.twig', [
            'depot' => $depot,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_depots_show', methods: ['GET'])]
    public function show(Depots $depot): Response
    {
        return $this->render('depots/show.html.twig', [
            'depot' => $depot,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_depots_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Depots $depot,
        DepotsRepository $depotsRepository
    ): Response {
        $form = $this->createForm(DepotsType::class, $depot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $depotsRepository->save($depot, true);

            $this->addFlash('success', 'Dépôt modifié avec succès.');
            return $this->redirectToRoute('app_depots_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('depots/new&edit.html.twig', [
            'depot' => $depot,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_depots_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Depots $depot,
        DepotsRepository $depotsRepository
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $depot->getId(), $request->request->get('_token'))) {
            $depotsRepository->remove($depot, true);
            $this->addFlash('success', 'Dépôt supprimé avec succès.');
        }

        return $this->redirectToRoute('app_depots_index', [], Response::HTTP_SEE_OTHER);
    }
}
