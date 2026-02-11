<?php
namespace App\Enum;

enum NoticeStatus: string
{
    case EN_ATTENTE = 'EN_ATTENTE';
    case VALIDE = 'VALIDE';
    case REFUS = 'REFUS';
    }
    
?>