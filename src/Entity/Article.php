<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $qteStock = null;

    /**
     * @var Collection<int, Dette>
     */
    #[ORM\ManyToMany(targetEntity: Dette::class, mappedBy: 'dette_articles')]
    private Collection $dettes;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;


    #[ORM\Column(type: 'float')]
    private ?float $prix = null;

    public function getPrix(): ?float
    {
        return $this->prix;
    }

public function setPrix(float $prix): static
{
    $this->prix = $prix;
    return $this;
}

    public function __construct()
    {
        $this->dettes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQteStock(): ?int
    {
        return $this->qteStock;
    }

    public function setQteStock(int $qteStock): static
    {
        $this->qteStock = $qteStock;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

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
            $dette->addDetteArticle($this);
        }

        return $this;
    }

    public function removeDette(Dette $dette): static
    {
        if ($this->dettes->removeElement($dette)) {
            $dette->removeDetteArticle($this);
        }

        return $this;
    }

    
}
