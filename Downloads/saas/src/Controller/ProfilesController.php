<?php
namespace App\Controller;

use App\Entity\Profiles;
use App\Form\ProfilesType;
use App\Repository\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profiles')]
class ProfilesController extends AbstractController
{
    #[Route('/', name: 'app_profiles_index', methods: ['GET'])]
    public function index(ProfileRepository $profileRepository): Response
    {
        return $this->render('profiles/index.html.twig', [
            'profiles' => $profileRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_profiles_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $profile = new Profiles();
        $form = $this->createForm(ProfilesType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($profile);
            $entityManager->flush();

            $this->addFlash('success', 'Profile créé avec succès');
            return $this->redirectToRoute('app_profiles_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('profiles/new&edit.html.twig', [
            'profile' => $profile,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}/edit', name: 'app_profiles_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Profiles $profile, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProfilesType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Profile mis à jour avec succès');
            return $this->redirectToRoute('app_profiles_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('profiles/new&edit.html.twig', [
            'profile' => $profile,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_profiles_delete', methods: ['POST'])]
    public function delete(Request $request, Profiles $profile, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$profile->getId(), $request->request->get('_token'))) {
            $entityManager->remove($profile);
            $entityManager->flush();
            $this->addFlash('success', 'Profile supprimé avec succès');
        }

        return $this->redirectToRoute('app_profiles_index', [], Response::HTTP_SEE_OTHER);
    }
}