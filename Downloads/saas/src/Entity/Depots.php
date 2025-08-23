<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\DepotsRepository;

#[ORM\Entity(repositoryClass: DepotsRepository::class)]
#[ORM\Table(name: 'depots')]
class Depots
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

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $intitule = null;

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule(string $intitule): self
    {
        $this->intitule = $intitule;
        return $this;
    }
    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'create_by', referencedColumnName: 'id')]
    private ?Users $createBy = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createAt = null;
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
