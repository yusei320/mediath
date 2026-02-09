<?php

namespace App\Entity;

use App\Repository\DemandeEmpruntRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DemandeEmpruntRepository::class)]
class DemandeEmprunt
{
    // Constantes de statut
    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_ACCEPTEE = 'acceptee';
    public const STATUT_REFUSEE = 'refusee';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Adherent $adherent = null;

    #[ORM\ManyToOne(targetEntity: Document::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Document $document = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Veuillez sélectionner un bibliothécaire')]
    private ?User $bibliothecaire = null; // L'utilisateur ROLE_BIBLIOTHECAIRE assigné

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDemande = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateEmpruntSouhaitee = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\Positive]
    private ?int $dureeSouhaiteeJours = 14; // Par défaut 14 jours

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 500)]
    private ?string $messageAdherent = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: [self::STATUT_EN_ATTENTE, self::STATUT_ACCEPTEE, self::STATUT_REFUSEE])]
    private ?string $statut = self::STATUT_EN_ATTENTE;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000)]
    private ?string $motifRefus = null; // Rempli par le bibliothécaire si refus

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateTraitement = null; // Date acceptation/refus

    #[ORM\ManyToOne(targetEntity: Emprunt::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Emprunt $empruntCree = null; // Lien vers l'emprunt créé si accepté

    public function __construct()
    {
        $this->dateDemande = new \DateTime();
        $this->statut = self::STATUT_EN_ATTENTE;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): static
    {
        $this->adherent = $adherent;

        return $this;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): static
    {
        $this->document = $document;

        return $this;
    }

    public function getBibliothecaire(): ?User
    {
        return $this->bibliothecaire;
    }

    public function setBibliothecaire(?User $bibliothecaire): static
    {
        $this->bibliothecaire = $bibliothecaire;

        return $this;
    }

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTimeInterface $dateDemande): static
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    public function getDateEmpruntSouhaitee(): ?\DateTimeInterface
    {
        return $this->dateEmpruntSouhaitee;
    }

    public function setDateEmpruntSouhaitee(?\DateTimeInterface $dateEmpruntSouhaitee): static
    {
        $this->dateEmpruntSouhaitee = $dateEmpruntSouhaitee;

        return $this;
    }

    public function getDureeSouhaiteeJours(): ?int
    {
        return $this->dureeSouhaiteeJours;
    }

    public function setDureeSouhaiteeJours(?int $dureeSouhaiteeJours): static
    {
        $this->dureeSouhaiteeJours = $dureeSouhaiteeJours;

        return $this;
    }

    public function getMessageAdherent(): ?string
    {
        return $this->messageAdherent;
    }

    public function setMessageAdherent(?string $messageAdherent): static
    {
        $this->messageAdherent = $messageAdherent;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getMotifRefus(): ?string
    {
        return $this->motifRefus;
    }

    public function setMotifRefus(?string $motifRefus): static
    {
        $this->motifRefus = $motifRefus;

        return $this;
    }

    public function getDateTraitement(): ?\DateTimeInterface
    {
        return $this->dateTraitement;
    }

    public function setDateTraitement(?\DateTimeInterface $dateTraitement): static
    {
        $this->dateTraitement = $dateTraitement;

        return $this;
    }

    public function getEmpruntCree(): ?Emprunt
    {
        return $this->empruntCree;
    }

    public function setEmpruntCree(?Emprunt $empruntCree): static
    {
        $this->empruntCree = $empruntCree;

        return $this;
    }
}
