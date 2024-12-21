<?php

namespace App\Entity;

use App\Repository\DetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetteRepository::class)]
class Dette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?float $montant = null;

    // #[ORM\Column]
    // private ?float $montantVerset = null;

    #[ORM\Column]
    private ?float $montantVerset = 0.0; // Définit une valeur par défaut de 0


    #[ORM\Column]
    private ?float $montantRestant = null;

    #[ORM\Column(length: 50)]
    private ?string $etat = 'En Cours'; // Nouvel état par défaut

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null; // Date de création automatique

    
    #[ORM\ManyToOne(inversedBy: 'dettes')]
    private ?Client $client = null;

    /**
     * @var Collection<int, Paiement>
     */
    #[ORM\OneToMany(targetEntity: Paiement::class, mappedBy: 'dette', cascade: ['persist', 'remove'])]
    private Collection $paiements;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\ManyToMany(targetEntity: Article::class, inversedBy: 'dettes')]
    private Collection $dette_articles;

    public function __construct()
    {
        $this->paiements = new ArrayCollection();
        $this->dette_articles = new ArrayCollection();
        $this->createdAt = new \DateTime(); // Initialisation de la date de création
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        if ($montant < 0) {
            throw new \InvalidArgumentException('Le montant total doit être positif.');
        }
        $this->montant = $montant;

        return $this;
    }

    public function getMontantVerset(): ?float
    {
        $montantVerse = 0;

    foreach ($this->paiements as $paiement) {
        $montantVerse += $paiement->getMontant();
    }

    return $montantVerse;
}

// Supposons que vous avez une propriété "montantVerse"
    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $montantVerse;
    

    public function getMontantVerse(): ?float
    {
        return $this->montantVerse;
    }

    

    public function setMontantVerse(?float $montantVerse): self
    {
        $this->montantVerse = $montantVerse;
        return $this;
    }

    public function setMontantVerset(float $montantVerset): static
    {
        if ($montantVerset < 0) {
            throw new \InvalidArgumentException('Le montant versé doit être positif.');
        }
        $this->montantVerset = $montantVerset;
        $this->calculateMontantRestant(); // Mettre à jour automatiquement

        return $this;
    }

    public function getMontantRestant(): ?float
    {
        return $this->montantRestant;
    }

    public function calculateMontantRestant(): void
    {
        if ($this->montant !== null && $this->montantVerset !== null) {
            $this->montantRestant = max($this->montant - $this->montantVerset, 0);
        } else {
            $this->montantRestant = $this->montant;
        }
    }



        public function setMontantRestant(float $montantRestant): static
        {
            $this->montantRestant = $montantRestant;

            return $this;
        }
                    

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, Paiement>
     */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiement $paiement): static
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements->add($paiement);
            $paiement->setDette($this);
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): static
    {
        if ($this->paiements->removeElement($paiement)) {
            if ($paiement->getDette() === $this) {
                $paiement->setDette(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getDetteArticles(): Collection
    {
        return $this->dette_articles;
    }

    public function addDetteArticle(Article $detteArticle): static
    {
        if (!$this->dette_articles->contains($detteArticle)) {
            $this->dette_articles->add($detteArticle);
        }

        return $this;
    }

    public function removeDetteArticle(Article $detteArticle): static
    {
        $this->dette_articles->removeElement($detteArticle);

        return $this;
    }


    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }
}
