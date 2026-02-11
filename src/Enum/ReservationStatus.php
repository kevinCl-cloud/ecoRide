<?php
namespace App\Enum;

enum ReservationStatus: string
{
    case EN_ATTENTE = 'EN_ATTENTE';
    case CONFIRMEE = 'CONFIRMEE';
    case TERMINEE = 'TERMINEE';
    case ANNULEE = 'ANNULEE';
    case PROBLEME = 'PROBLEME';
    }
?>