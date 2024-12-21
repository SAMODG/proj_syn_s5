<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $surname = null;

    #[ORM\Column(length: 255)]
    private ?string $telephone = null;

    #[ORM\OneToOne(inversedBy: 'client', cascade: ['persist', 'remove'])]
    private ?Adresse $adresse = null;

    /**
     * @var Collection<int, Dette>
     */
    #[ORM\OneToMany(targetEntity: Dette::class, mappedBy: 'client')]
    private Collection $dettes;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'client', cascade: ['persist', 'remove'])]
     #[ORM\JoinColumn(nullable: true)] // Optionnel : un client peut ne pas avoir de compte utilisateur
        private ?User $user = null;

        public function getUser(): ?User
        {
            return $this->user;
        }

        public function setUser(?User $user): static
        {
            $this->user = $user;

            return $this;
        }

        #[ORM\Column(type: 'datetime', nullable: false)]
        private \DateTime $createdAt;

        public function getCreatedAt(): \DateTime
        {
            return $this->createdAt;
        }

        public function setCreatedAt(\DateTime $createdAt): static
        {
            $this->createdAt = $createdAt;

            return $this;
        }



    public function __construct()
    {
        $this->dettes = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): static
    {
        $this->surname = $surname;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getAdresse(): ?Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(?Adresse $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * @return Collection<int, Dette>
     */
    public function getDettes(): Collection
    {
        return $this->dettes;
    }

    public function addDette(Dette $dette): static
    {
        if (!$this->dettes->contains($dette)) {
            $this->dettes->add($dette);
            $dette->setClient($this);
        }

        return $this;
    }

    public function removeDette(Dette $dette): static
    {
        if ($this->dettes->removeElement($dette)) {
            // set the owning side to null (unless already changed)
            if ($dette->getClient() === $this) {
                $dette->setClient(null);
            }
        }

        return $this;
    }
/**
     * Retourne le montant total des dettes du client.
     */
    public function getTotalMontant(): float
    {
        return array_sum(array_map(fn($dette) => $dette->getMontant(), $this->getDettes()->toArray()));
    }

    /**
     * Retourne le montant total versé pour toutes les dettes du client.
     */
    public function getTotalVerse(): float
    {
        return array_sum(array_map(fn($dette) => $dette->getMontantVerse(), $this->getDettes()->toArray()));
    }

    /**
     * Retourne le montant restant pour toutes les dettes du client.
     */
    public function getTotalRestant(): float
    {
        return array_sum(array_map(fn($dette) => $dette->getMontantRestant(), $this->getDettes()->toArray()));
    }


    public function getTotalDettes(): float
    {
        $total = 0;
        foreach ($this->getDettes() as $dette) {
            $total += $dette->getMontantRestant();
        }
        return $total;
    }
    
}
