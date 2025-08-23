<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DocumentsligneRepository;

#[ORM\Entity(repositoryClass: DocumentsligneRepository::class)]
#[ORM\Table(name: 'documentslignes')]
class Documentslignes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Documents::class, inversedBy: 'lignes')]
    #[ORM\JoinColumn(name: 'id_document', referencedColumnName: 'id', nullable: false)]
    private ?Documents $document = null;
    public function getDocument(): ?Documents
    {
        return $this->document;
    }
    public function setDocument(?Documents $document): self
    {
        $this->document = $document;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Articles::class)]
    #[ORM\JoinColumn(name: 'id_article', referencedColumnName: 'id', nullable: false)]
    private ?Articles $article = null;
    public function getArticle(): ?Articles
    {
        return $this->article;
    }
    public function setArticle(?Articles $article): self
    {
        $this->article = $article;
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: false)]
    private ?float $qte = null;

    public function getQte(): ?float
    {
        return $this->qte;
    }

    public function setQte(float $qte): self
    {
        $this->qte = $qte;
        $this->calculatePrixTotalHt();
        $this->updateDocument();
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: false)]
    private ?float $prixUnitaireHt = null;

    public function getPrixUnitaireHt(): ?float
    {
        return $this->prixUnitaireHt;
    }

    public function setPrixUnitaireHt(float $prixUnitaireHt): self
    {
        $this->prixUnitaireHt = $prixUnitaireHt;
        $this->calculatePrixTotalHt();
        $this->updateDocument();
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: false)]
    private ?float $prixTotalHt = null;

    public function getPrixTotalHt(): ?float
    {
        return $this->prixTotalHt;
    }

    public function setPrixTotalHt(float $prixTotalHt): self
    {
        $this->prixTotalHt = $prixTotalHt;
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: false)]
    private ?float $remise = null;

    public function getRemise(): ?float
    {
        return $this->remise;
    }

    public function setRemise(float $remise): self
    {
        $this->remise = $remise;
        $this->calculatePrixTotalHt();
        $this->updateDocument();
        return $this;
    }
    public function calculatePrixTotalHt(): void
    {
        $total = $this->qte * $this->prixUnitaireHt;
        $this->prixTotalHt = $total - ($total * ($this->remise / 100));
    }
    public function updateDocument(): void
    {
        if ($this->document) {
            $this->document->calculateTotals();
        }
    }
}
