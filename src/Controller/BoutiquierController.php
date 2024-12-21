<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Client;
use App\Entity\Dette;
use App\Entity\Paiement;

use App\Entity\Article;
use App\Form\DetteType;
use App\Form\ClientType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;



class BoutiquierController extends AbstractController
{
    #[Route('/boutiquier', name: 'app_boutiquier')]
    // public function index(): Response
    // {
    //     return $this->render('boutiquier/index.html.twig', [
    //         'controller_name' => 'Espace Boutiquier',
    //     ]);
    // }
    

    #[Route('/boutiquier', name: 'app_boutiquier')]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupérer les statistiques
        $totalDettes = $em->getRepository(Dette::class)
                          ->createQueryBuilder('d')
                          ->select('SUM(d.montantRestant)')
                          ->getQuery()
                          ->getSingleScalarResult();

        $nombreClients = $em->getRepository(Client::class)->count([]);

        $articlesEnStock = $em->getRepository(Article::class)->count([]);

        $demandesEnCours = $em->getRepository(Dette::class)
                              ->count(['etat' => 'En Cours']);

        // Récupérer la liste des clients récents
        $clients = $em->getRepository(Client::class)
                      ->findBy([], ['id' => 'DESC'], 5);

        // Récupérer les articles en rupture de stock
        $articlesRupture = $em->getRepository(Article::class)
                              ->findBy(['qteStock' => 0]);

        // Passer les données au template
        return $this->render('boutiquier/index.html.twig', [
            'totalDettes' => $totalDettes ?: 0,
            'nombreClients' => $nombreClients,
            'articlesEnStock' => $articlesEnStock,
            'demandesEnCours' => $demandesEnCours,
            'clients' => $clients,
            'articlesRupture' => $articlesRupture,
        ]);
    }

    

    #1. Créer un Client
    #[Route('/boutiquier/client/new', name: 'boutiquier_client_new', methods: ['GET', 'POST'])]
            public function newClient(Request $request, EntityManagerInterface $em): Response
            {
                $client = new Client();
                $form = $this->createForm(ClientType::class, $client);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $em->persist($client);
                    $em->flush();

                    $this->addFlash('success', 'Client ajouté avec succès.');
                    return $this->redirectToRoute('boutiquier_clients');
                }

                return $this->render('boutiquier/client_new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }


    #2. Lister et Filtrer les Clients
    #[Route('/boutiquier/clients', name: 'boutiquier_clients', methods: ['GET'])]
        
        public function listClients(Request $request, EntityManagerInterface $em): Response
        {
            $telephone = $request->query->get('telephone');
        
            // Filtrage des clients
            if ($telephone) {
                $clients = $em->getRepository(Client::class)->findBy(['telephone' => $telephone]);
                if (empty($clients)) {
                    $this->addFlash('info', "Aucun client trouvé avec le numéro : $telephone.");
                }
            } else {
                $clients = $em->getRepository(Client::class)->findAll();
            }
        
            // Calculer les montants dus pour chaque client
            foreach ($clients as $client) {
                $totalDue = 0;
                foreach ($client->getDettes() as $dette) {
                    $totalDue += $dette->getMontantRestant(); // Assurez-vous que getMontantRestant() existe dans l'entité Dette
                }
                $client->totalDue = $totalDue; // Ajouter une propriété dynamique
            }
        
            return $this->render('boutiquier/clients.html.twig', [
                'clients' => $clients,
                'searchTerm' => $telephone,
            ]);
        }
        


        #3. Rechercher un Client par Téléphone

        #[Route('/boutiquier/client/search', name: 'boutiquier_client_search', methods: ['GET', 'POST'])]
        public function searchClient(Request $request, EntityManagerInterface $em): Response
        {
            $searchTerm = $request->query->get('telephone');
            $clients = [];

            if ($searchTerm) {
                $clients = $em->getRepository(Client::class)->findBy(['telephone' => $searchTerm]);
            }

            return $this->render('boutiquier/client_search.html.twig', [
                'clients' => $clients,
                'searchTerm' => $searchTerm,
            ]);
        }


        #4. Créer une Dette
               
        #[Route('/boutiquier/client/{id}/dette/new', name: 'boutiquier_dette_new', methods: ['GET', 'POST'])]
        public function newDette(Client $client, Request $request, EntityManagerInterface $em): Response
        {
    
            
            $dette = new Dette();
            $dette->setClient($client);
            $dette->setDate(new \DateTime());
    
            foreach ($dette->getDetteArticles() as $article) {
                if ($article->getQteStock() <= 0) {
                    $this->addFlash('error', "L'article {$article->getNom()} n'a plus de stock.");
                    return $this->redirectToRoute('boutiquier_clients');
                }
            }
            
            $form = $this->createForm(DetteType::class, $dette);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $dette->calculateMontantRestant();
                $em->persist($dette);
                $em->flush();
    
                $this->addFlash('success', 'Dette créée avec succès.');
                return $this->redirectToRoute('boutiquier_clients');
            }
    
            return $this->render('boutiquier/dette_new.html.twig', [
                'form' => $form->createView(),
                'client' => $client,
            ]);
        }
    


    // #5. Enregistrer un Paiement

  


    #5. Enregistrer un Paiement
#[Route('/boutiquier/dette/{id}/paiement/new', name: 'boutiquier_paiement_new', methods: ['GET', 'POST'])]
public function newPaiement(Dette $dette, Request $request, EntityManagerInterface $em): Response
{
    $paiement = new Paiement();
    $paiement->setDate(new \DateTime());
    $paiement->setDette($dette);

    $form = $this->createFormBuilder($paiement)
        ->add('montant', NumberType::class, [
            'label' => 'Montant du paiement',
            'attr' => ['class' => 'form-control']
        ])
        ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        if ($paiement->getMontant() > $dette->getMontantRestant()) {
            $this->addFlash('error', 'Le montant du paiement ne peut pas dépasser le montant restant de la dette.');
            return $this->redirectToRoute('boutiquier_paiement_new', ['id' => $dette->getId()]);
        }

        $montantRestant = $dette->getMontantRestant() - $paiement->getMontant();
        $dette->setMontantRestant($montantRestant);

        $em->persist($paiement);
        $em->flush();

        $this->addFlash('success', 'Paiement enregistré avec succès.');
        return $this->redirectToRoute('boutiquier_client_dettes', ['id' => $dette->getClient()->getId()]);
    }

    return $this->render('boutiquier/paiement_new.html.twig', [
        'form' => $form->createView(),
        'dette' => $dette,
    ]);
}


    #2.1 Lister les dettes non soldées d’un client
    // #[Route('/boutiquier/client/{id}/dettes', name: 'boutiquier_client_dettes', methods: ['GET'])]
    // public function listDettesNonSoldees(Client $client, EntityManagerInterface $em): Response
    // {
    //     $dettes = $em->getRepository(Dette::class)->findBy([
    //         'client' => $client,
    //         'montantRestant' => ['>', 0]
    //     ]);

    //     return $this->render('boutiquier/client_dettes.html.twig', [
    //         'client' => $client,
    //         'dettes' => $dettes,
    //     ]);
    // }


        #[Route('/boutiquier/client/{id}/dettes', name: 'boutiquier_client_dettes', methods: ['GET'])]
        
        public function listDettesNonSoldees(Client $client, EntityManagerInterface $em): Response
        {
            // Requête pour récupérer les dettes non soldées
            $query = $em->createQuery(
                'SELECT d
                FROM App\Entity\Dette d
                WHERE d.client = :client AND d.montantRestant > 0'
            )->setParameter('client', $client);
        
            $dettes = $query->getResult();
        
            // Initialisation des totaux
            $totalMontant = 0;
            $totalVerse = 0;
            $totalRestant = 0;
        
            // Calcul des totaux
            foreach ($dettes as $dette) {
                $totalMontant += $dette->getMontant();
                if (method_exists($dette, 'getMontantVerse')) {
                    $totalVerse += $dette->getMontantVerse(); // Vérification si la méthode existe
                } else {
                    $totalVerse += 0; // Si la méthode n'existe pas, on ajoute 0
                }
                $totalRestant += $dette->getMontantRestant();
            }
        
            // Passer les données au template
            return $this->render('boutiquier/client_dettes.html.twig', [
                'client' => $client,
                'dettes' => $dettes,
                'totalMontant' => $totalMontant,
                'totalVerse' => $totalVerse,
                'totalRestant' => $totalRestant,
            ]);
        }
        

    #2.2 Gérer les demandes de dette (valider/refuser)
    #[Route('/boutiquier/dette/{id}/validate', name: 'boutiquier_dette_validate', methods: ['POST'])]
public function validateDette(Dette $dette, EntityManagerInterface $em): Response
{
    if ($dette->getEtat() === 'En Cours') {
        $dette->setEtat('Validée');
        $em->flush();
        $this->addFlash('success', 'Dette validée avec succès.');
    } else {
        $this->addFlash('error', 'Seules les dettes en cours peuvent être validées.');
    }

    return $this->redirectToRoute('boutiquier_clients');
    }

    #[Route('/boutiquier/dette/{id}/refuse', name: 'boutiquier_dette_refuse', methods: ['POST'])]
    public function refuseDette(Dette $dette, EntityManagerInterface $em): Response
    {
        if ($dette->getEtat() === 'En Cours') {
            $dette->setEtat('Refusée');
            $em->flush();
            $this->addFlash('success', 'Dette refusée avec succès.');
        } else {
            $this->addFlash('error', 'Seules les dettes en cours peuvent être refusées.');
        }

        return $this->redirectToRoute('boutiquier_clients');
    }



}
