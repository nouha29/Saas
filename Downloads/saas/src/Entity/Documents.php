<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DocumentsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: DocumentsRepository::class)]
#[ORM\Table(name: 'documents')]
#[ORM\HasLifecycleCallbacks]
class Documents
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 15, unique: true)]
    private ?string $reference = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $docDate = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'emetteur_id', referencedColumnName: 'id')]
    private ?Users $emetteur = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'destinataire_id', referencedColumnName: 'id')]
    private ?Users $destinataire = null;

    #[ORM\Column(
        type: 'string',
        length: 50
    )]
    private ?string $type = null;

    #[ORM\Column(
        type: 'string',
        length: 15,
    )]
    private ?string $status = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $montantHt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $tauxTva = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $montantTva = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $timbre = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $retenu = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'create_by', referencedColumnName: 'id')]
    private ?Users $createBy = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createAt = null;
    public function getId(): ?int
    {
        return $this->id;
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

    public function getDocDate(): ?\DateTimeInterface
    {
        return $this->docDate;
    }

    public function setDocDate(\DateTimeInterface $docDate): self
    {
        $this->docDate = $docDate;
        return $this;
    }

    public function getEmetteur(): ?Users
    {
        return $this->emetteur;
    }

    public function setEmetteur(?Users $emetteur): self
    {
        $this->emetteur = $emetteur;
        return $this;
    }

    public function getDestinataire(): ?Users
    {
        return $this->destinataire;
    }

    public function setDestinataire(?Users $destinataire): self
    {
        $this->destinataire = $destinataire;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getMontantHt(): ?float
    {
        return $this->montantHt;
    }

    public function setMontantHt(?float $montantHt): self
    {
        $this->montantHt = $montantHt;
        return $this;
    }

    public function getTauxTva(): ?int
    {
        return $this->tauxTva;
    }

    public function setTauxTva(?int $tauxTva): self
    {
        $this->tauxTva = $tauxTva;
        return $this;
    }

    public function getMontantTva(): ?float
    {
        return $this->montantTva;
    }
    public function setMontantTva(?int $montantTva): self
    {
        $this->montantTva = $montantTva;
        $this->calculateMontantTva();
        return $this;
    }
    public function getTimbre(): ?float
    {
        return $this->timbre;
    }

    public function setTimbre(?float $timbre): self
    {
        $this->timbre = $timbre;
        return $this;
    }

    public function getRetenu(): ?float
    {
        return $this->retenu;
    }

    public function setRetenu(?float $retenu): self
    {
        $this->retenu = $retenu;
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
    public function updateMontantTva(): void
    {
        $this->calculateMontantTva();
    }
    #[ORM\Column(nullable: true)]
    private ?float $montantAPayer = null;

    public function getMontantAPayer(): ?float
    {
        return $this->montantAPayer;
    }

    public function setMontantAPayer(float $montantAPayer): static
    {
        $this->montantAPayer = $montantAPayer;
        return $this;
    }
    #[ORM\OneToMany(targetEntity: Documentslignes::class, mappedBy: "document", cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $lignes;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
    }

    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(Documentslignes $ligne): self
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes[] = $ligne;
            $ligne->setDocument($this);
        }
        return $this;
    }

    public function removeLigne(Documentslignes $ligne): self
    {
        if ($this->lignes->removeElement($ligne)) {
            if ($ligne->getDocument() === $this) {
                $ligne->setDocument(null);
            }
        }
        return $this;
    }
    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->createAt = new \DateTime();
        if ($this->reference === null) {
            $this->reference = $this->generateReference();
        }
        $this->updateTotals();
    }

    public function generateReference(): string
    {
        $prefixMap = [
            'Devis achat' => 'DA',
            'Commande achat' => 'CA',
            'Facture achat' => 'FA',
            'Facture achat avoire' => 'FAA',
            'Bon d\'entré' => 'BE',
            'Bon de transfert' => 'BT',
            'Bon de retour' => 'BR',
            'Devis vente' => 'DV',
            'Commande vente' => 'CV',
            'Facture vente' => 'FV',
            'Facture vente avoire' => 'FVA',
            'Bon de sortie' => 'BS',
            'Bon de livraison' => 'BL',
            'Inventaire' => 'INV'
        ];

        $prefix = $prefixMap[$this->type] ?? 'DOC';
        $year = date('y');

        return $prefix . $year . '000001';
    }
    public function calculateTotals(): void
    {
        $this->montantHt = 0;

        foreach ($this->lignes as $ligne) {
            $ligne->calculatePrixTotalHt();
            $this->montantHt += $ligne->getPrixTotalHt();
        }

        $this->calculateMontantTva();

        $ttc = $this->montantTva ?? $this->montantHt;
        $this->montantAPayer = $ttc + ($this->timbre ?? 0) - ($this->retenu ?? 0);
    }

    public function calculateMontantTva(): self
    {
        if ($this->montantHt !== null && $this->tauxTva !== null) {
            $this->montantTva = $this->montantHt * (1 + ($this->tauxTva / 100));
        }
        return $this;
    }
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTotals(): void
    {
        $this->calculateTotals();
    }
}
