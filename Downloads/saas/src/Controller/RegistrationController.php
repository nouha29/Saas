<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Service\StatsKpiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly StatsKpiService $stats
    ) {}


    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home'); // on renvoie vers la home (dashboard)
        }

        $lastUsername = $authenticationUtils->getLastUsername();
        if ($error) {
            dump($error->getMessageData());
        }

        return $this->render('auth/auth-signin-basic.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route('/signup', name: 'app_signup', methods: ['GET', 'POST'])]
    public function signup(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new Users();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->logger->info('Form validation signup failed', [
                    'errors' => $form->getErrors(true, true)
                ]);
            } else {
                $this->logger->info('I am in signup sub');

                $user->setPassword(
                    $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
                );
                $user->setStatus('Activé');

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('auth/auth-signup-basic.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * Page d'accueil = Dashboard (index.html.twig)
     * On passe TOUTES les variables attendues par la vue.
     */
   #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('index.html.twig', [
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

    #[Route('/landing', name: 'app_landing')]
    public function landing(): Response
    {
        return $this->home();
    }

    // ✅ On conserve l’API pour le graphe 12 mois ici
    #[Route('/api/stats/ca-12-mois', name: 'api_stats_ca_12_mois')]
    public function ca12Mois(): Response
    {
        return $this->json($this->stats->serieCa12Mois());
    }
}