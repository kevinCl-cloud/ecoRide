<?php

namespace App\Entity;

use App\Repository\VehiculeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehiculeRepository::class)]
class Vehicule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'vehicules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $idDriver = null;

    #[ORM\ManyToOne(inversedBy: 'vehicules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Brand $idBrand = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $placesNbr = null;

    #[ORM\Column(length: 50)]
    private ?string $model = null;

    #[ORM\Column(length: 50)]
    private ?string $color = null;

    #[ORM\Column(length: 50)]
    private ?string $registration = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $firstRegistration = null;

    #[ORM\Column(length: 50)]
    private ?string $energy = null;

    /**
     * @var Collection<int, Covoiturage>
     */
    #[ORM\OneToMany(targetEntity: Covoiturage::class, mappedBy: 'idVehicule', orphanRemoval: true)]
    private Collection $covoiturages;

    public function __construct()
    {
        $this->covoiturages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdDriver(): ?User
    {
        return $this->idDriver;
    }

    public function setIdDriver(?User $idDriver): static
    {
        $this->idDriver = $idDriver;

        return $this;
    }

    public function getIdBrand(): ?brand
    {
        return $this->idBrand;
    }

    public function setIdBrand(?brand $idBrand): static
    {
        $this->idBrand = $idBrand;

        return $this;
    }

    public function getPlacesNbr(): ?int
    {
        return $this->placesNbr;
    }

    public function setPlacesNbr(int $placesNbr): static
    {
        $this->placesNbr = $placesNbr;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getRegistration(): ?string
    {
        return $this->registration;
    }

    public function setRegistration(string $registration): static
    {
        $this->registration = $registration;

        return $this;
    }

    public function getFirstRegistration(): ?\DateTime
    {
        return $this->firstRegistration;
    }

    public function setFirstRegistration(\DateTime $firstRegistration): static
    {
        $this->firstRegistration = $firstRegistration;

        return $this;
    }

    public function getEnergy(): ?string
    {
        return $this->energy;
    }

    public function setEnergy(string $energy): static
    {
        $this->energy = $energy;

        return $this;
    }

    /**
     * @return Collection<int, Covoiturage>
     */
    public function getCovoiturages(): Collection
    {
        return $this->covoiturages;
    }

    public function addCovoiturage(Covoiturage $covoiturage): static
    {
        if (!$this->covoiturages->contains($covoiturage)) {
            $this->covoiturages->add($covoiturage);
            $covoiturage->setIdVehicule($this);
        }

        return $this;
    }

    public function removeCovoiturage(Covoiturage $covoiturage): static
    {
        if ($this->covoiturages->removeElement($covoiturage)) {
            // set the owning side to null (unless already changed)
            if ($covoiturage->getIdVehicule() === $this) {
                $covoiturage->setIdVehicule(null);
            }
        }

        return $this;
    }
}
