<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $login = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Client $client = null;
        #[ORM\Column(type: 'json')]
    private array $roles = [];


    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true; // Colonne pour le statut actif/inactif


    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // Assurez-vous que chaque utilisateur a au moins ROLE_USER
        return array_unique($roles);
    }


    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // Cette méthode est vide car nous n'utilisons pas de données temporaires sensibles.
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }
    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }
}
