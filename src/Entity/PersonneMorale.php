<?php

namespace App\Entity;

use App\Repository\PersonneMoraleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonneMoraleRepository::class)]
class PersonneMorale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\ManyToMany(targetEntity: Role::class, mappedBy: 'pmorale')]
    private Collection $roles;

    /**
     * @var Collection<int, Contrat>
     */
    #[ORM\ManyToMany(targetEntity: Contrat::class, mappedBy: 'pmorale')]
    private Collection $contrats;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 2000)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $RC = null;

    #[ORM\Column(length: 255)]
    private ?string $identifiant_fiscal = null;

    #[ORM\Column(length: 255)]
    private ?string $iCE = null;

    #[ORM\Column(length: 255)]
    private ?string $ville = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->contrats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            $role->addPmorale($this);
        }

        return $this;
    }

    public function removeRole(Role $role): static
    {
        if ($this->roles->removeElement($role)) {
            $role->removePmorale($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Contrat>
     */
    public function getContrats(): Collection
    {
        return $this->contrats;
    }

    public function addContrat(Contrat $contrat): static
    {
        if (!$this->contrats->contains($contrat)) {
            $this->contrats->add($contrat);
            $contrat->addPmorale($this);
        }

        return $this;
    }

    public function removeContrat(Contrat $contrat): static
    {
        if ($this->contrats->removeElement($contrat)) {
            $contrat->removePmorale($this);
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getRC(): ?string
    {
        return $this->RC;
    }

    public function setRC(string $RC): static
    {
        $this->RC = $RC;

        return $this;
    }

    public function getIdentifiantFiscal(): ?string
    {
        return $this->identifiant_fiscal;
    }

    public function setIdentifiantFiscal(string $identifiant_fiscal): static
    {
        $this->identifiant_fiscal = $identifiant_fiscal;

        return $this;
    }

    public function getICE(): ?string
    {
        return $this->iCE;
    }

    public function setICE(string $iCE): static
    {
        $this->iCE = $iCE;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }
}
