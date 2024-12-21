<?php

namespace App\DataFixtures;

use App\Entity\Adresse;
use App\Entity\Client;
use App\Entity\Article;
use App\Entity\Dette;
use App\Entity\Paiement;
use App\Entity\User; // Ajout des utilisateurs
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Création des Adresses
        $adresses = [];
        for ($i = 1; $i <= 10; $i++) {
            $adresse = new Adresse();
            $adresse->setRue('Rue ' . $i);
            $adresse->setVille('Ville ' . $i);
            $adresse->setCodePostal('1234' . $i);
            $adresse->setPays('Pays ' . $i);

            $manager->persist($adresse);
            $adresses[] = $adresse;
        }

        // 2. Création des Clients
        $clients = [];
        for ($i = 1; $i <= 10; $i++) {
            $client = new Client();
            $client->setSurname('Client ' . $i);
            $client->setTelephone('12345678' . $i);
            $client->setAdresse($adresses[$i - 1]);

            $manager->persist($client);
            $clients[] = $client;
        }

        // 3. Création des Articles
        $articles = [];
        $articleData = [
            ['nom' => 'Article A', 'qteStock' => 100, 'prix' => 50],
            ['nom' => 'Article B', 'qteStock' => 50, 'prix' => 30],
            ['nom' => 'Article C', 'qteStock' => 75, 'prix' => 40],
        ];

        foreach ($articleData as $data) {
            $article = new Article();
            $article->setNom($data['nom']);
            $article->setQteStock($data['qteStock']);
            $article->setPrix($data['prix']);

            $manager->persist($article);
            $articles[] = $article;
        }

        // 4. Création des Dettes
        $dettes = [];
        foreach ($clients as $client) {
            $dette = new Dette();
            $dette->setClient($client);
            $dette->setDate(new \DateTime());
            $dette->setMontant(1000);
            $dette->setMontantVerset(500);
            $dette->setEtat('En Cours');
            $dette->calculateMontantRestant();

            // Associer deux articles
            $dette->addDetteArticle($articles[array_rand($articles)]);
            $dette->addDetteArticle($articles[array_rand($articles)]);

            $manager->persist($dette);
            $dettes[] = $dette;
        }

        // 5. Création des Paiements
        foreach ($dettes as $dette) {
            $paiement = new Paiement();
            $paiement->setDate(new \DateTime());
            $paiement->setMontant($dette->getMontantVerset() / 2);
            $paiement->setDette($dette);

            $manager->persist($paiement);
        }

        // 6. Création des Utilisateurs (Admin, Boutiquier, Client)
        $roles = [
            'admin' => 'ROLE_ADMIN',
            'boutiquier' => 'ROLE_BOUTIQUIER',
            'client' => 'ROLE_CLIENT',
        ];

        foreach ($roles as $name => $role) {
            $user = new User();
            $user->setEmail("{$name}@example.com");
            $user->setLogin("{$name}_login"); // Ajout du login
            $user->setRoles([$role]);
            $user->setIsActive(true);
            $user->setPassword('plain_password');

            $manager->persist($user);
        }

        // 7. Flush pour enregistrer en base
        $manager->flush();
    }
}
