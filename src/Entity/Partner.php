<?php

namespace App\Entity;

use App\Repository\PartnerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartnerRepository::class)]
class Partner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $first_name = null;

    #[ORM\Column(length: 255)]
    private ?string $last_name = null;

    #[ORM\Column(length: 255)]
    private ?string $mariage_place = null;

    #[ORM\ManyToOne(inversedBy: 'partenaire')]
    private ?PersonnePhysique $personnePhysique = null;

    #[ORM\Column]
    private ?int $mariage_year = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getMariagePlace(): ?string
    {
        return $this->mariage_place;
    }

    public function setMariagePlace(string $mariage_place): static
    {
        $this->mariage_place = $mariage_place;

        return $this;
    }

    public function getPersonnePhysique(): ?PersonnePhysique
    {
        return $this->personnePhysique;
    }

    public function setPersonnePhysique(?PersonnePhysique $personnePhysique): static
    {
        $this->personnePhysique = $personnePhysique;

        return $this;
    }

    public function getMariageYear(): ?int
    {
        return $this->mariage_year;
    }

    public function setMariageYear(int $mariage_year): static
    {
        $this->mariage_year = $mariage_year;

        return $this;
    }


}
