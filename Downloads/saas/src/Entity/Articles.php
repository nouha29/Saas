<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\ArticlesRepository;

#[ORM\Entity(repositoryClass: ArticlesRepository::class)]
#[ORM\Table(name: 'articles')]
class Articles
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true, nullable: false)]
    private ?string $reference = null;

    #[ORM\Column(type: 'string', length: 20, nullable: false)]
    private ?string $type = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'fournisseur_id', referencedColumnName: 'id', nullable: false)]
    private ?Users $fournisseur = null;

    #[ORM\Column(type: 'string', length: 10, nullable: false)]
    private ?string $unite = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'create_by', referencedColumnName: 'id', nullable: false)]
    private ?Users $createBy = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getFournisseur(): ?Users
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?Users $fournisseur): self
    {
        $this->fournisseur = $fournisseur;
        return $this;
    }

    public function getUnite(): ?string
    {
        return $this->unite;
    }

    public function setUnite(string $unite): self
    {
        $this->unite = $unite;
        return $this;
    }

    public function getCreateBy(): ?Users
    {
        return $this->createBy;
    }

    public function setCreateBy(?Users $createBy): self
    {
        $this->createBy = $createBy;
        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeInterface $createAt): self
    {
        $this->createAt = $createAt;
        return $this;
    }
}