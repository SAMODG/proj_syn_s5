<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Dette;
use App\Entity\Article;
use App\Form\DetteType;
use App\Form\ClientType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Déclaration de la classe contrôleur pour gérer les clients
#[Route('/client')]
class ClientController extends AbstractController
{


     

    #[Route('/client/dashboard', name: 'client_dashboard')]
    public function clientDashboard(): Response
    {
        return $this->render('client/dashboard.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }



        #[Route('/client/dettes', name: 'client_dettes')]
        public function dettes(): Response
        {
            return $this->render('client/dettes.html.twig');
        }
        
        #[Route('/client/paiements', name: 'client_paiements')]
        public function paiements(): Response
        {
            return $this->render('client/paiements.html.twig');
        }
        
        #[Route('/client/contact', name: 'client_contact')]
        public function contact(): Response
        {
            return $this->render('client/contact.html.twig');
        }
        
    
    // Action pour afficher la liste des clients
    #[Route('/', name: 'client_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les clients depuis la base de données
        $clients = $entityManager->getRepository(Client::class)->findAll();

        // Retourner une vue Twig pour afficher les clients
        return $this->render('client/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    // Action pour créer un nouveau client
    #[Route('/new', name: 'client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $client = new Client(); // Création d'une nouvelle instance de Client
        $form = $this->createForm(ClientType::class, $client); // Création du formulaire lié à l'entité Client
        $form->handleRequest($request); // Gestion de la soumission du formulaire

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($client); // Préparation de l'entité à être sauvegardée
            $entityManager->flush(); // Sauvegarde en base de données

            // Redirection vers la liste des clients après la création
            return $this->redirectToRoute('client_index');
        }

        // Afficher le formulaire dans une vue Twig
        return $this->render('client/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Action pour afficher les détails d'un client spécifique
    #[Route('/{id}', name: 'client_show', methods: ['GET'])]
    public function show(Client $client): Response
    {
        // Retourne la vue avec les détails du client
        return $this->render('client/show.html.twig', [
            'client' => $client,
        ]);
    }

    // Action pour modifier un client existant
    #[Route('/{id}/edit', name: 'client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClientType::class, $client); // Formulaire pré-rempli avec les données du client
        $form->handleRequest($request); // Gestion de la soumission du formulaire

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Mise à jour des données en base

            // Redirection vers la liste des clients après modification
            return $this->redirectToRoute('client_index');
        }

        // Afficher le formulaire dans une vue Twig
        return $this->render('client/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Action pour supprimer un client
    #[Route('/{id}', name: 'client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        // Vérification CSRF pour sécuriser la suppression
        if ($this->isCsrfTokenValid('delete' . $client->getId(), $request->request->get('_token'))) {
            $entityManager->remove($client); // Préparation de la suppression
            $entityManager->flush(); // Suppression en base de données
        }

        // Redirection vers la liste des clients après suppression
        return $this->redirectToRoute('client_index');
    }


     #Faire une demande de dette (nouvelle méthode)


     #[Route('/{id}/dette/new', name: 'client_dette_new', methods: ['GET', 'POST'])]
    public function demandeDette(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        $dette = new Dette();
        $dette->setClient($client);
        $dette->setDate(new \DateTime());
        $dette->setMontantVerset(0); // Initialiser à 0

        $form = $this->createFormBuilder($dette)
            ->add('detteArticles', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Sélectionnez des articles'
            ])
            ->add('montant', NumberType::class, [
                'label' => 'Montant total',
                'required' => true
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier que des articles sont sélectionnés
            if ($dette->getDetteArticles()->count() > 0) {
                // Calculer le montant restant
                $dette->setMontantRestant($dette->getMontant());

                // Persister la dette
                $entityManager->persist($dette);
                $entityManager->flush();

                // Message de confirmation
                $this->addFlash('success', 'La dette a été créée avec succès.');
                return $this->redirectToRoute('client_dettes', ['id' => $client->getId()]);
            } else {
                $this->addFlash('error', 'Veuillez sélectionner au moins un article.');
            }
        }

        return $this->render('client/dette_new.html.twig', [
            'form' => $form->createView(),
            'client' => $client,
        ]);
    }



    
    #Relancer une demande de dette annulée
        #[Route('/dette/{id}/relance', name: 'client_dette_relance', methods: ['POST'])]
    public function relancerDette(Dette $dette, EntityManagerInterface $entityManager): Response
    {
        if ($dette->getEtat() === 'Annulée') {
            $dette->setEtat('En Cours');
            $entityManager->flush();
            $this->addFlash('success', 'La demande a été relancée.');
        } else {
            $this->addFlash('error', 'Seules les demandes annulées peuvent être relancées.');
        }

        return $this->redirectToRoute('client_dettes', ['id' => $dette->getClient()->getId()]);
    }

        

}
