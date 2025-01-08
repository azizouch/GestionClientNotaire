<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, PersonnePhysique>
     */
    #[ORM\ManyToMany(targetEntity: PersonnePhysique::class, inversedBy: 'roles')]
    private Collection $pphysique;

    /**
     * @var Collection<int, PersonneMorale>
     */
    #[ORM\ManyToMany(targetEntity: PersonneMorale::class, inversedBy: 'roles')]
    private Collection $pmorale;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    public function __construct()
    {
        $this->pphysique = new ArrayCollection();
        $this->pmorale = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
