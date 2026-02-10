<?php

namespace App\Entity;

use App\Enum\CovoiturageStatus;
use App\Repository\CovoiturageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CovoiturageRepository::class)]
class Covoiturage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'covoiturages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $idDriver = null;

    #[ORM\ManyToOne(inversedBy: 'covoiturages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vehicule $idVehicule = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $placesNbr = null;

    #[ORM\Column]
    private ?int $travelTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $departureTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $arrivalTime = null;

    #[ORM\Column(length: 100)]
    private ?string $placeDeparture = null;

    #[ORM\Column(length: 100)]
    private ?string $placeArrival = null;

    #[ORM\Column(enumType: CovoiturageStatus::class)]
    private ?CovoiturageStatus $statut = CovoiturageStatus::PREVU;

    #[ORM\Column]
    private ?\DateTimeImmutable $create_at = null;

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

    public function getIdVehicule(): ?Vehicule
    {
        return $this->idVehicule;
    }

    public function setIdVehicule(?Vehicule $idVehicule): static
    {
        $this->idVehicule = $idVehicule;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

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

    public function getTravelTime(): ?int
    {
        return $this->travelTime;
    }

    public function setTravelTime(int $travelTime): static
    {
        $this->travelTime = $travelTime;

        return $this;
    }

    public function getDepartureTime(): ?\DateTime
    {
        return $this->departureTime;
    }

    public function setDepartureTime(\DateTime $departureTime): static
    {
        $this->departureTime = $departureTime;

        return $this;
    }

    public function getArrivalTime(): ?\DateTime
    {
        return $this->arrivalTime;
    }

    public function setArrivalTime(\DateTime $arrivalTime): static
    {
        $this->arrivalTime = $arrivalTime;

        return $this;
    }

    public function getPlaceDeparture(): ?string
    {
        return $this->placeDeparture;
    }

    public function setPlaceDeparture(string $placeDeparture): static
    {
        $this->placeDeparture = $placeDeparture;

        return $this;
    }

    public function getPlaceArrival(): ?string
    {
        return $this->placeArrival;
    }

    public function setPlaceArrival(string $placeArrival): static
    {
        $this->placeArrival = $placeArrival;

        return $this;
    }

    public function getStatut(): ?CovoiturageStatus
    {
        return $this->statut;
    }

    public function setStatut(CovoiturageStatus $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->create_at;
    }

    public function setCreateAt(\DateTimeImmutable $create_at): static
    {
        $this->create_at = $create_at;

        return $this;
    }
}
