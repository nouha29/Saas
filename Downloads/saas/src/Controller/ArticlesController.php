<?php
namespace App\Controller;

use App\Entity\Articles;
use App\Form\ArticlesType;
use App\Repository\ArticlesRepository;
use App\Repository\UsersRepository;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/articles')]
class ArticlesController extends AbstractController
{
    #[Route('/', name: 'app_articles_index', methods: ['GET'])]
    public function index(ArticlesRepository $articleRepository): Response
    {
        return $this->render('articles/index.html.twig', [
            'articles' => $articleRepository->findAllWithFournisseur(),
        ]);
    }

#[Route('/new', name: 'app_articles_new', methods: ['GET', 'POST'])]
public function new(
    Request $request, 
    ArticlesRepository $articleRepository,
    UsersRepository $userRepository
): Response
{
    $article = new Articles();
    $form = $this->createForm(ArticlesType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $type = $form->get('type')->getData();
        $prefix = $this->getTypePrefix($type);
        $yearSuffix = date('y'); 
        $nextNumber = $articleRepository->getNextCounterValue($prefix, $yearSuffix);
        
        $reference = sprintf(
            '%s%s%06d',
            $prefix,
            $yearSuffix,
            $nextNumber
        );
        
        $article->setReference($reference);
        
        $rootUser = $userRepository->find(1);
        if (!$rootUser) {
            throw $this->createNotFoundException('Utilisateur root (id=1) non trouvé');
        }
        
        $article->setCreateBy($rootUser);
        $article->setCreateAt(new \DateTime());

        $articleRepository->save($article, true);

        $this->addFlash('success', 'Article créé avec succès.');
        return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
    }

    $fournisseurs = $userRepository->findBy(['profile' => 6]);

    return $this->render('articles/new.html.twig', [
        'article' => $article,
        'form' => $form->createView(), 
        'fournisseurs' => $fournisseurs,
    ]);
}

private function getTypePrefix(string $type): string
{
    $mapping = [
        'Produit Fini' => 'PF',
        'Matière Première' => 'MP',
    ];
    
    return $mapping[$type] ?? strtoupper(substr($type, 0, 2));
}

    #[Route('/{id}', name: 'app_articles_show', methods: ['GET'])]
    public function show(Articles $article): Response
    {
        return $this->render('articles/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_articles_edit', methods: ['GET', 'POST'])]
public function edit(
    Request $request, 
    Articles $article, 
    ArticlesRepository $articleRepository,
    UsersRepository $userRepository
): Response
{
    $oldReference = $article->getReference();
    
    $form = $this->createForm(ArticlesType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        if ($article->getType() !== $this->getOriginalTypeFromReference($oldReference)) {
            $type = $form->get('type')->getData();
            $prefix = $this->getTypePrefix($type);
            $yearSuffix = date('y');
            $nextNumber = $articleRepository->getNextCounterValue($prefix, $yearSuffix);
            
            $reference = sprintf('%s%s%06d', $prefix, $yearSuffix, $nextNumber);
            $article->setReference($reference);
        }

        $articleRepository->save($article, true);

        $this->addFlash('success', 'Article modifié avec succès.');
        return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
    }

    $fournisseurs = $userRepository->findBy(['profile' => 6]);

    return $this->render('articles/edit.html.twig', [
        'article' => $article,
        'form' => $form->createView(), 
        'fournisseurs' => $fournisseurs,
    ]);
}

private function getOriginalTypeFromReference(string $reference): ?string
{
    $prefix = substr($reference, 0, 2);
    $mapping = [
        'PF' => 'Produit Fini',
        'MP' => 'Matière Première',
    ];
    
    return $mapping[$prefix] ?? null;
}

    #[Route('/{id}', name: 'app_articles_delete', methods: ['POST'])]
    public function delete(
        Request $request, 
        Articles $article, 
        ArticlesRepository $articleRepository
    ): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $articleRepository->remove($article, true);
            $this->addFlash('success', 'Article supprimé avec succès.');
        }

        return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
    }
}