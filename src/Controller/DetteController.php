<?php

namespace App\Controller;

use App\Entity\Dette;
use App\Entity\Client;
use App\Entity\Article;
use App\Form\DetteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Déclaration de la classe contrôleur pour gérer les dettes
#[Route('/dette')]
class DetteController extends AbstractController
{
    // Action pour afficher la liste des dettes
    #[Route('/', name: 'dette_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer toutes les dettes depuis la base de données
        $dettes = $entityManager->getRepository(Dette::class)->findAll();

        // Retourner une vue Twig pour afficher les dettes
        return $this->render('dette/index.html.twig', [
            'dettes' => $dettes,
        ]);
    }

    // Action pour créer une nouvelle dette
    #[Route('/new', name: 'dette_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $dette = new Dette(); // Création d'une nouvelle instance de Dette
    $form = $this->createForm(DetteType::class, $dette); // Formulaire lié à l'entité Dette
    $form->handleRequest($request); // Gestion de la soumission du formulaire

    // Si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        // Vérification que le client est associé
        if (null === $dette->getClient()) {
            $this->addFlash('error', 'Un client doit être sélectionné pour cette dette.');
            return $this->redirectToRoute('dette_new');
        }

        // Récupérer les articles associés (si la relation est ManyToMany)
        $articles = $dette->getDetteArticles();
        if ($articles->isEmpty()) {
            $this->addFlash('error', 'Au moins un article doit être sélectionné pour cette dette.');
            return $this->redirectToRoute('dette_new');
        }

        // Calculer automatiquement le montant restant
        $dette->calculateMontantRestant();

        // Persister l'entité Dette avec les relations
        $entityManager->persist($dette);
        $entityManager->flush();

        // Ajouter un message flash de confirmation
        $this->addFlash('success', 'La dette a été créée avec succès.');

        // Redirection vers la liste des dettes après création
        return $this->redirectToRoute('dette_index');
    }

    // Afficher le formulaire dans une vue Twig
    return $this->render('dette/new.html.twig', [
        'dette' => $dette,
        'form' => $form->createView(),
    ]);
}

    // Action pour afficher les détails d'une dette spécifique
    #[Route('/{id}', name: 'dette_show', methods: ['GET'])]
    public function show(Dette $dette): Response
    {
        // Retourne une vue Twig avec les détails de la dette
        return $this->render('dette/show.html.twig', [
            'dette' => $dette,
        ]);
    }

    // Action pour modifier une dette existante
    #[Route('/{id}/edit', name: 'dette_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Dette $dette, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DetteType::class, $dette); // Formulaire pré-rempli avec les données de la dette
        $form->handleRequest($request); // Gestion de la soumission du formulaire

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Mise à jour des données en base

            // Redirection vers la liste des dettes après modification
            return $this->redirectToRoute('dette_index');
        }

        // Afficher le formulaire dans une vue Twig
        return $this->render('dette/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Action pour supprimer une dette
    #[Route('/{id}', name: 'dette_delete', methods: ['POST'])]
    public function delete(Request $request, Dette $dette, EntityManagerInterface $entityManager): Response
    {
        // Vérification CSRF pour sécuriser la suppression
        if ($this->isCsrfTokenValid('delete' . $dette->getId(), $request->request->get('_token'))) {
            $entityManager->remove($dette); // Préparation de la suppression
            $entityManager->flush(); // Suppression en base de données
        }

        // Redirection vers la liste des dettes après suppression
        return $this->redirectToRoute('dette_index');
    }
}
