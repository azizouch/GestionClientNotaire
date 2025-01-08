<?php

namespace App\Entity;

use App\Repository\DesignationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DesignationRepository::class)]
class Designation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $numero_bien = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
    private ?string $etage = null;

    #[ORM\Column]
    private ?int $superficie = null;

    #[ORM\Column(length: 255)]
    private ?string $superficie_text = null;

    #[ORM\Column(length: 255)]
    private ?string $numero_divise = null;

    #[ORM\Column(length: 255)]
    private ?string $titre_foncier = null;

    #[ORM\Column(length: 255)]
    private ?string $titre_foncier_mere = null;

    #[ORM\Column(length: 255)]
    private ?string $indivision = null;

    #[ORM\Column(length: 255)]
    private ?string $residence = null;

    #[ORM\Column]
    private ?float $montant_TTC = null;

    #[ORM\Column(length: 255)]
    private ?string $montant_TTC_text = null;

    #[ORM\Column]
    private ?float $TVA = null;

    #[ORM\Column(length: 255)]
    private ?string $TVA_text = null;

    #[ORM\Column]
    private ?float $montant_HT = null;

    #[ORM\Column(length: 255)]
    private ?string $montant_HT_text = null;

    #[ORM\Column]
    private ?int $delai = null;

    #[ORM\Column]
    private ?int $Nombre_salon = null;

    #[ORM\Column]
    private ?int $nombre_chambre = null;

    #[ORM\Column]
    private ?int $nombre_cuisine = null;

    #[ORM\Column]
    private ?int $nombre_sallon_de_bain = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroBien(): ?string
    {
        return $this->numero_bien;
    }

    public function setNumeroBien(string $numero_bien): static
    {
        $this->numero_bien = $numero_bien;

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

    public function getEtage(): ?string
    {
        return $this->etage;
    }

    public function setEtage(string $etage): static
    {
        $this->etage = $etage;

        return $this;
    }

    public function getSuperficie(): ?int
    {
        return $this->superficie;
    }

    public function setSuperficie(int $superficie): static
    {
        $this->superficie = $superficie;

        return $this;
    }

    public function getSuperficieText(): ?string
    {
        return $this->superficie_text;
    }

    public function setSuperficieText(string $superficie_text): static
    {
        $this->superficie_text = $superficie_text;

        return $this;
    }

    public function getNumeroDivise(): ?string
    {
        return $this->numero_divise;
    }

    public function setNumeroDivise(string $numero_divise): static
    {
        $this->numero_divise = $numero_divise;

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

    public function getTitreFoncierMere(): ?string
    {
        return $this->titre_foncier_mere;
    }

    public function setTitreFoncierMere(string $titre_foncier_mere): static
    {
        $this->titre_foncier_mere = $titre_foncier_mere;

        return $this;
    }

    public function getIndivision(): ?string
    {
        return $this->indivision;
    }

    public function setIndivision(string $indivision): static
    {
        $this->indivision = $indivision;

        return $this;
    }

    public function getResidence(): ?string
    {
        return $this->residence;
    }

    public function setResidence(string $residence): static
    {
        $this->residence = $residence;

        return $this;
    }

    public function getMontantTTC(): ?float
    {
        return $this->montant_TTC;
    }

    public function setMontantTTC(float $montant_TTC): static
    {
        $this->montant_TTC = $montant_TTC;

        return $this;
    }

    public function getMontantTTCText(): ?string
    {
        return $this->montant_TTC_text;
    }

    public function setMontantTTCText(string $montant_TTC_text): static
    {
        $this->montant_TTC_text = $montant_TTC_text;

        return $this;
    }

    public function getTVA(): ?float
    {
        return $this->TVA;
    }

    public function setTVA(float $TVA): static
    {
        $this->TVA = $TVA;

        return $this;
    }

    public function getTVAText(): ?string
    {
        return $this->TVA_text;
    }

    public function setTVAText(string $TVA_text): static
    {
        $this->TVA_text = $TVA_text;

        return $this;
    }

    public function getMontantHT(): ?float
    {
        return $this->montant_HT;
    }

    public function setMontantHT(float $montant_HT): static
    {
        $this->montant_HT = $montant_HT;

        return $this;
    }

    public function getMontantHTText(): ?string
    {
        return $this->montant_HT_text;
    }

    public function setMontantHTText(string $montant_HT_text): static
    {
        $this->montant_HT_text = $montant_HT_text;

        return $this;
    }

    public function getDelai(): ?int
    {
        return $this->delai;
    }

    public function setDelai(int $delai): static
    {
        $this->delai = $delai;

        return $this;
    }

    public function getNombreSalon(): ?int
    {
        return $this->Nombre_salon;
    }

    public function setNombreSalon(int $Nombre_salon): static
    {
        $this->Nombre_salon = $Nombre_salon;

        return $this;
    }

    public function getNombreChambre(): ?int
    {
        return $this->nombre_chambre;
    }

    public function setNombreChambre(int $nombre_chambre): static
    {
        $this->nombre_chambre = $nombre_chambre;

        return $this;
    }

    public function getNombreCuisine(): ?int
    {
        return $this->nombre_cuisine;
    }

    public function setNombreCuisine(int $nombre_cuisine): static
    {
        $this->nombre_cuisine = $nombre_cuisine;

        return $this;
    }

    public function getNombreSallonDeBain(): ?int
    {
        return $this->nombre_sallon_de_bain;
    }

    public function setNombreSallonDeBain(int $nombre_sallon_de_bain): static
    {
        $this->nombre_sallon_de_bain = $nombre_sallon_de_bain;

        return $this;
    }


}
