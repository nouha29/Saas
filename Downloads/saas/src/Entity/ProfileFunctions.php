<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ProfileFunctionsRepository;

#[ORM\Entity(repositoryClass: ProfileFunctionsRepository::class)]
#[ORM\Table(name: 'profile_functions')]
class ProfileFunctions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Profiles::class, inversedBy: 'profileFunctions')]
    #[ORM\JoinColumn(name: 'id_profile', referencedColumnName: 'id', nullable: false)]
    private ?Profiles $profile = null;

    #[ORM\ManyToOne(targetEntity: Functions::class, inversedBy: 'profileFunctions')]
    #[ORM\JoinColumn(name: 'id_function', referencedColumnName: 'id', nullable: false)]
    private ?Functions $function = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getProfile(): ?Profiles
    {
        return $this->profile;
    }

    public function setProfile(?Profiles $profile): self
    {
        $this->profile = $profile;
        return $this;
    }

    public function getFunction(): ?Functions
    {
        return $this->function;
    }

    public function setFunction(?Functions $function): self
    {
        $this->function = $function;
        return $this;
    }
}
