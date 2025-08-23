<?php

namespace App\Controller;

use App\Entity\Functions;
use App\Form\FunctionsType;
use App\Repository\FunctionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/functions')]
class FunctionsController extends AbstractController
{
    #[Route('/', name: 'app_functions_index', methods: ['GET'])]
    public function index(FunctionsRepository $functionsRepository): Response
    {
        return $this->render('functions/index.html.twig', [
            'functions' => $functionsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_functions_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $function = new Functions();
        $form = $this->createForm(FunctionsType::class, $function);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($function);
            $entityManager->flush();

            return $this->redirectToRoute('app_functions_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('functions/new&edit.html.twig', [
            'function' => $function,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_functions_show', methods: ['GET'])]
    public function show(Functions $function): Response
    {
        return $this->render('functions/show.html.twig', [
            'function' => $function,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_functions_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Functions $function, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FunctionsType::class, $function);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_functions_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('functions/new&edit.html.twig', [
            'function' => $function,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_functions_delete', methods: ['POST'])]
    public function delete(Request $request, Functions $function, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$function->getId(), $request->request->get('_token'))) {
            $entityManager->remove($function);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_functions_index', [], Response::HTTP_SEE_OTHER);
    }
}