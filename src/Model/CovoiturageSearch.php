<?php

namespace App\Model;

class CovoiturageSearch
{
    private ?string $placeDeparture = null;
    private ?string $placeArrival = null;
    private ?\DateTimeInterface $dateDeparture = null;

    public function getPlaceDeparture() {
        return $this->placeDeparture;
    }
    public function setPlaceDeparture($placeDeparture) {
        $this->placeDeparture = $placeDeparture;
    }
    public function getPlaceArrival() {
        return $this->placeArrival;
    }
    public function setPlaceArrival($placeArrival) {
        $this->placeArrival = $placeArrival;
    }
    public function getDateDeparture() {
        return $this->dateDeparture;
    }
    public function setDateDeparture($dateDeparture) {
        $this->dateDeparture = $dateDeparture ;
    }
}
