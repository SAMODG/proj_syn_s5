<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Déclaration de la classe contrôleur pour gérer les articles
#[Route('/article')]
class ArticleController extends AbstractController
{
    // Action pour afficher la liste des articles
    #[Route('/', name: 'article_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les articles depuis la base de données
        $articles = $entityManager->getRepository(Article::class)->findAll();

        // Retourner une vue Twig pour afficher les articles
        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    // Action pour créer un nouvel article
    #[Route('/new', name: 'article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article(); // Création d'une nouvelle instance d'Article
        $form = $this->createForm(ArticleType::class, $article); // Création du formulaire lié à l'entité Article
        $form->handleRequest($request); // Gestion de la soumission du formulaire

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article); // Préparation de l'entité à être sauvegardée
            $entityManager->flush(); // Sauvegarde en base de données

            // Redirection vers la liste des articles après la création
            return $this->redirectToRoute('article_index');
        }

        // Afficher le formulaire dans une vue Twig
        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Action pour afficher les détails d'un article spécifique
    #[Route('/{id}', name: 'article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        // Retourne la vue avec les détails de l'article
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    // Action pour modifier un article existant
    #[Route('/{id}/edit', name: 'article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article); // Formulaire pré-rempli avec les données de l'article
        $form->handleRequest($request); // Gestion de la soumission du formulaire

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Mise à jour des données en base

            // Redirection vers la liste des articles après modification
            return $this->redirectToRoute('article_index');
        }

        // Afficher le formulaire dans une vue Twig
        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Action pour supprimer un article
    #[Route('/{id}', name: 'article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        // Vérification CSRF pour sécuriser la suppression
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article); // Préparation de la suppression
            $entityManager->flush(); // Suppression en base de données
        }

        // Redirection vers la liste des articles après suppression
        return $this->redirectToRoute('article_index');
    }
}
