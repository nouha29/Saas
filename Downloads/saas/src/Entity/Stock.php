<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\StockRepository;

#[ORM\Entity(repositoryClass: StockRepository::class)]
#[ORM\Table(name: 'stock')]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Articles::class)]
    #[ORM\JoinColumn(name: 'id_article', referencedColumnName: 'id', nullable: false)]
    private ?Articles $article = null;

    #[ORM\ManyToOne(targetEntity: Depots::class)]
    #[ORM\JoinColumn(name: 'id_depot', referencedColumnName: 'id', nullable: false)]
    private ?Depots $depot = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateEntree = null;

    #[ORM\Column(type: 'float')]
    private float $qteStockPrincipal  = 0.0;

    #[ORM\Column(type: 'float')]
    private float $qteStockDispo  = 0.0;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getArticle(): ?Articles
    {
        return $this->article;
    }

    public function setArticle(?Articles $article): self
    {
        $this->article = $article;
        return $this;
    }

    public function getDepot(): ?Depots
    {
        return $this->depot;
    }

    public function setDepot(?Depots $depot): self
    {
        $this->depot = $depot;
        return $this;
    }

    public function getDateEntree(): ?\DateTimeInterface
    {
        return $this->dateEntree;
    }

    public function setDateEntree(\DateTimeInterface $dateEntree): self
    {
        $this->dateEntree = $dateEntree;
        return $this;
    }

    public function getQteStockPrincipal(): float
    {
        return $this->qteStockPrincipal;
    }

    public function setQteStockPrincipal(float $qteStockPrincipal): self
    {
        $this->qteStockPrincipal = $qteStockPrincipal;
        return $this;
    }

    public function getQteStockDispo(): float
    {
        return $this->qteStockDispo;
    }

    public function setQteStockDispo(float $qteStockDispo): self
    {
        $this->qteStockDispo = $qteStockDispo;
        return $this;
    }

}