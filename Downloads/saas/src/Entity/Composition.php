<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Composition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Nomenclature::class, inversedBy: 'compositions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Nomenclature $nomenclature = null;

    #[ORM\ManyToOne(targetEntity: Articles::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Articles $matiere = null;

    #[ORM\Column(type: 'float')]
    private ?float $consommation = null;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomenclature(): ?Nomenclature
    {
        return $this->nomenclature;
    }

    public function setNomenclature(?Nomenclature $nomenclature): static
    {
        $this->nomenclature = $nomenclature;
        return $this;
    }

    public function getMatiere(): ?Articles
    {
        return $this->matiere;
    }
    public function setMatiere(?Articles $matiere): static
    {
        $this->matiere = $matiere;
        return $this;
    }
    public function getConsommation(): ?float
    {
        return $this->consommation;
    }

    public function setConsommation(float $consommation): static
    {
        $this->consommation = $consommation;
        return $this;
    }
}
