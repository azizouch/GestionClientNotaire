<?php

namespace App\Entity;

use App\Repository\ContratRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContratRepository::class)]
class Contrat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }


    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_promettant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_beneficiaire = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_maitre = null;

    #[ORM\Column(length: 255)]
    private ?string $repertoir = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Designation $designation = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, PersonnePhysique>
     */
    #[ORM\ManyToMany(targetEntity: PersonnePhysique::class, inversedBy: 'contrats',cascade: ['persist'])]
    private Collection $pphysique;

    /**
     * @var Collection<int, PersonneMorale>
     */
    #[ORM\ManyToMany(targetEntity: PersonneMorale::class, inversedBy: 'contrats',cascade: ['persist'])]
    private Collection $pmorale;

    #[ORM\ManyToOne(targetEntity: Dossier::class, inversedBy: 'compromis')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Dossier $dossier = null; // Add this to the Contrat class

    public function __construct()
    {

        $this->pphysique = new ArrayCollection();
        $this->pmorale = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getDatePromettant(): ?\DateTimeInterface
    {
        return $this->date_promettant;
    }

    public function setDatePromettant(?\DateTimeInterface $date_promettant): static
    {
        $this->date_promettant = $date_promettant;

        return $this;
    }

    public function getDateBeneficiaire(): ?\DateTimeInterface
    {
        return $this->date_beneficiaire;
    }

    public function setDateBeneficiaire(?\DateTimeInterface $date_beneficiaire): static
    {
        $this->date_beneficiaire = $date_beneficiaire;

        return $this;
    }

    public function getDateMaitre(): ?\DateTimeInterface
    {
        return $this->date_maitre;
    }

    public function setDateMaitre(?\DateTimeInterface $date_maitre): static
    {
        $this->date_maitre = $date_maitre;

        return $this;
    }



    public function getRepertoir(): ?string
    {
        return $this->repertoir;
    }

    public function setRepertoir(string $repertoir): static
    {
        $this->repertoir = $repertoir;

        return $this;
    }

    public function getDesignation(): ?Designation
    {
        return $this->designation;
    }

    public function setDesignation(Designation $designation): static
    {
        $this->designation = $designation;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, PersonnePhysique>
     */
    public function getPphysique(): Collection
    {
        return $this->pphysique;
    }

    public function addPphysique(PersonnePhysique $pphysique): static
    {
        if (!$this->pphysique->contains($pphysique)) {
            $this->pphysique->add($pphysique);
        }

        return $this;
    }

    public function removePphysique(PersonnePhysique $pphysique): static
    {
        $this->pphysique->removeElement($pphysique);

        return $this;
    }

    /**
     * @return Collection<int, PersonneMorale>
     */
    public function getPmorale(): Collection
    {
        return $this->pmorale;
    }

    public function addPmorale(PersonneMorale $pmorale): static
    {
        if (!$this->pmorale->contains($pmorale)) {
            $this->pmorale->add($pmorale);
        }

        return $this;
    }

    public function removePmorale(PersonneMorale $pmorale): static
    {
        $this->pmorale->removeElement($pmorale);

        return $this;
    }

    public function getDossier(): ?Dossier
    {
        return $this->dossier;
    }

    public function setDossier(?Dossier $dossier): static
    {
        $this->dossier = $dossier;

        return $this;
    }
}
