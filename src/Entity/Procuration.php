<?php

namespace App\Entity;

use App\Repository\ProcurationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProcurationRepository::class)]
class Procuration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $repertoir = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_mandant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_mandataire = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_maitre = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, PersonnePhysique>
     */
    #[ORM\ManyToMany(targetEntity: PersonnePhysique::class, inversedBy: 'procurations',cascade: ['persist'])]
    private Collection $persons;

    #[ORM\Column(length: 255)]
    private ?string $titre_foncier = null;

    public function __construct()
    {
        $this->persons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateMandant(): ?\DateTimeInterface
    {
        return $this->date_mandant;
    }

    public function setDateMandant(?\DateTimeInterface $date_mandant): static
    {
        $this->date_mandant = $date_mandant;

        return $this;
    }

    public function getDateMandataire(): ?\DateTimeInterface
    {
        return $this->date_mandataire;
    }

    public function setDateMandataire(?\DateTimeInterface $date_mandataire): static
    {
        $this->date_mandataire = $date_mandataire;

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

    /**
     * @return Collection<int, PersonnePhysique>
     */
    public function getPersons(): Collection
    {
        return $this->persons;
    }

    public function addPerson(PersonnePhysique $person): static
    {
        if (!$this->persons->contains($person)) {
            $this->persons->add($person);
        }

        return $this;
    }

    public function removePerson(PersonnePhysique $person): static
    {
        $this->persons->removeElement($person);

        return $this;
    }

    public function getTitreFoncier(): ?string
    {
        return $this->titre_foncier;
    }

    public function setTitreFoncier(string $titre_foncier): static
    {
        $this->titre_foncier = $titre_foncier;

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
}
