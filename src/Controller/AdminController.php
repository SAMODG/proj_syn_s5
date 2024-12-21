<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Article;
use App\Entity\Dette;
use App\Form\UserType;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{



    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function adminDashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }


    
    // #[Route('/admin', name: 'app_admin')]
    // public function index(): Response
    // {
    //     return $this->render('admin/index.html.twig', [
    //         'controller_name' => 'AdminController',
    //     ]);
    // }

        #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }


    ##// 1. Lister les utilisateurs avec filtres (rôle et statut)
   


            #[Route('/users', name: 'admin_users_list', methods: ['GET'])]
        public function listUsers(EntityManagerInterface $em, Request $request): Response
        {
            $role = $request->query->get('role');
            $status = $request->query->get('status');

            $query = $em->getRepository(User::class)->createQueryBuilder('u');

            if ($role) {
                $query->andWhere('u.roles LIKE :role')
                    ->setParameter('role', '%' . $role . '%');
            }

            if ($status !== null) {
                $query->andWhere('u.isActive = :status')
                    ->setParameter('status', $status);
            }

            $users = $query->getQuery()->getResult();

            // Récupérer les clients sans compte utilisateur
            $clients = $em->getRepository(Client::class)->createQueryBuilder('c')
                        ->leftJoin('c.user', 'u')
                        ->where('u IS NULL')
                        ->getQuery()
                        ->getResult();

            return $this->render('admin/users_list.html.twig', [
                'users' => $users,
                'clients' => $clients,
            ]);
        }


   ## // 2. Créer un compte utilisateur pour un client existant

   


    // #[Route('/user/create/client/{id}', name: 'admin_user_create_client', methods: ['GET', 'POST'])]
    //     public function createUserForClient(Client $client, Request $request, EntityManagerInterface $em): Response
    //     {
    //         $user = new User();
    //         $user->setClient($client);
    //         $form = $this->createForm(UserType::class, $user);

    //         $form->handleRequest($request);

    //         if ($form->isSubmitted() && $form->isValid()) {
    //             $user->setRoles(['ROLE_CLIENT']);
    //             $user->setIsActive(true);
    //             $em->persist($user);
    //             $em->flush();

    //             $this->addFlash('success', 'Compte utilisateur créé avec succès pour le client.');
    //             return $this->redirectToRoute('admin_users_list');
    //         }

    //         return $this->render('admin/user_create.html.twig', [
    //             'form' => $form->createView(),
    //         ]);
    //     }


    #[Route('/user/create/client/{id}', name: 'admin_user_create_client', methods: ['GET', 'POST'])]
        


        // public function createUserForClient(Client $client, Request $request, EntityManagerInterface $em): Response
        // {
        //     $user = new User();
        //     $user->setClient($client);

        //     // Préremplir l'email si nécessaire
        //     $user->setEmail(strtolower($client->getSurname()) . '@example.com');

        //     $form = $this->createForm(UserType::class, $user);
        //     $form->handleRequest($request);

        //     if ($form->isSubmitted() && $form->isValid()) {
        //         $user->setRoles(['ROLE_CLIENT']);
        //         $user->setIsActive(true);
        //         $em->persist($user);
        //         $em->flush();

        //         $this->addFlash('success', 'Compte utilisateur créé avec succès pour le client : ' . $client->getSurname());
        //         return $this->redirectToRoute('admin_users_list');
        //     }

        //     return $this->render('admin/user_create.html.twig', [
        //         'form' => $form->createView(),
        //         'client' => $client,
        //     ]);
        // }


        public function createUserForClient(Client $client, Request $request, EntityManagerInterface $em): Response
        {
            $user = new User();
            $user->setClient($client); // Associer le client
            // Assigner un login par défaut basé sur le nom ou le téléphone
            $user->setLogin($client->getSurname() . '_client'); 

            $user->setEmail($client->getSurname() . '@example.com'); // Pré-remplir avec une adresse email par défaut
            $user->setIsActive(true); // Activer par défaut
            
            $form = $this->createForm(UserType::class, $user);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Vérifier si l'email existe déjà
                $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
                if ($existingUser) {
                    $this->addFlash('error', 'Cet email est déjà utilisé.');
                    return $this->redirectToRoute('admin_users_list');
                }

                $user->setRoles(['ROLE_CLIENT']);
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Compte utilisateur créé avec succès pour le client.');
                return $this->redirectToRoute('admin_users_list');
            }

            return $this->render('admin/user_create.html.twig', [
                'form' => $form->createView(),
                'client' => $client,
            ]);
        }
    // 3. Créer un nouveau compte Admin ou Boutiquier
    #[Route('/user/create', name: 'admin_user_create', methods: ['GET', 'POST'])]
    public function createUser(Request $request, EntityManagerInterface $em): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setIsActive(true);
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('admin/user_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // 4. Activer/Désactiver un utilisateur
    #[Route('/user/{id}/toggle', name: 'admin_user_toggle', methods: ['POST'])]
    public function toggleUserStatus(User $user, EntityManagerInterface $em): Response
    {
        $user->setIsActive(!$user->isActive());
        $em->flush();

        $this->addFlash('success', 'Statut de l’utilisateur mis à jour.');
        return $this->redirectToRoute('admin_users_list');
    }

    // 5. Créer un article
    #[Route('/article/new', name: 'admin_article_new', methods: ['GET', 'POST'])]
    public function newArticle(Request $request, EntityManagerInterface $em): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($article);
            $em->flush();

            $this->addFlash('success', 'Article ajouté avec succès.');
            return $this->redirectToRoute('admin_articles_list');
        }

        return $this->render('admin/article_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // 6. Lister les articles avec un filtre de disponibilité
    #[Route('/articles', name: 'admin_articles_list', methods: ['GET'])]
    public function listArticles(EntityManagerInterface $em, Request $request): Response
    {
        $disponible = $request->query->get('disponible');

        $query = $em->getRepository(Article::class)->createQueryBuilder('a');

        if ($disponible === '1') {
            $query->andWhere('a.qteStock > 0');
        }

        $articles = $query->getQuery()->getResult();

        return $this->render('admin/articles_list.html.twig', [
            'articles' => $articles,
        ]);
    }

    // 2. Modifier un article
    #[Route('/article/{id}/edit', name: 'admin_article_edit', methods: ['GET', 'POST'])]
    public function editArticle(Article $article, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Article modifié avec succès.');
            return $this->redirectToRoute('admin_articles_list');
        }

        return $this->render('admin/article_edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    // 3. Supprimer un article
        #[Route('/article/{id}/delete', name: 'admin_article_delete', methods: ['POST'])]
        public function deleteArticle(Article $article, EntityManagerInterface $em): Response
        {
            $em->remove($article);
            $em->flush();

            $this->addFlash('success', 'Article supprimé avec succès.');
            return $this->redirectToRoute('admin_articles_list');
        }

    // 7. Archiver les dettes soldées
    #[Route('/dettes/archive', name: 'admin_dettes_archive', methods: ['POST'])]
    public function archiveDettes(EntityManagerInterface $em): Response
    {
        $dettes = $em->getRepository(Dette::class)->findBy(['montantRestant' => 0]);

        foreach ($dettes as $dette) {
            $em->remove($dette); // Suppression ou archivage
        }

        $em->flush();

        $this->addFlash('success', 'Dettes soldées archivées avec succès.');
        return $this->redirectToRoute('admin_users_list');
    }



}
