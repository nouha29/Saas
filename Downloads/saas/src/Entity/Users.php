<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\Table(name: 'users')]
class Users implements PasswordAuthenticatedUserInterface, UserInterface
{
    public function eraseCredentials(): void {}

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];
        if ($this->profile) {
            $roles[] = 'ROLE_' . strtoupper($this->profile->getIntitule());
        }
        return array_unique($roles);
    }
    #[ORM\Column(type: 'string', length: 255)]

    private ?string $password = null;

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $firstName = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $lastName = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private ?string $mail = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $status = 'Activé';

    #[ORM\ManyToOne(targetEntity: Profiles::class)]
    #[ORM\JoinColumn(name: 'id_profile', referencedColumnName: 'id')]
    private ?Profiles $profile = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
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
    public function getUserIdentifier(): string
    {
        return $this->mail;
    }
}
