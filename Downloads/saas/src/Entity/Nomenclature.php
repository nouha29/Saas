<?php

namespace App\Entity;

use App\Repository\NomenclatureRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(
    fields: ['produit'],
    message: 'Ce produit possède déjà une nomenclature.',
    errorPath: 'produit'
)]
#[ORM\Entity(repositoryClass: NomenclatureRepository::class)]
#[ORM\Table(name: 'nomenclature')]
class Nomenclature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Articles::class)]
    #[ORM\JoinColumn(name: 'produit_id', referencedColumnName: 'id', nullable: false)]
    private ?Articles $produit = null;

    #[ORM\ManyToOne(targetEntity: Articles::class)]
    #[ORM\JoinColumn(name: 'matiere_id', referencedColumnName: 'id', nullable: false)]
    private ?Articles $matiere = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $consommation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduit(): ?Articles
    {
        return $this->produit;
    }
    public function setProduit(?Articles $produit): self
    {
        $this->produit = $produit;
        return $this;
    }
    public function getMatiere(): ?Articles
    {
        return $this->matiere;
    }
    public function setMatiere(?Articles $matiere): self
    {
        $this->matiere = $matiere;
        return $this;
    }
    public function getConsommation(): ?int
    {
        return $this->consommation;
    }
    public function setConsommation(?int $consommation): self
    {
        $this->consommation = $consommation;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'produit')]
    private Collection $compositions;

    public function __construct()
    {
        $this->compositions = new ArrayCollection();
    }

    public function getCompositions(): Collection
    {
        return $this->compositions;
    }

    public function addComposition(Composition $composition): self
    {
        if (!$this->compositions->contains($composition)) {
            $this->compositions[] = $composition;
            $composition->setNomenclature($this);
        }
        return $this;
    }

    public function removeComposition(Composition $composition): self
    {
        if ($this->compositions->removeElement($composition)) {
            if ($composition->getNomenclature() === $this) {
                $composition->setNomenclature(null);
            }
        }
        return $this;
    }
}
