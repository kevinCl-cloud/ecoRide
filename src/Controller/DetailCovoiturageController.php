<?php

namespace App\Controller;

use App\Entity\Covoiturage;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DetailCovoiturageController extends AbstractController
{
    #[Route('/detail/covoiturage{id}', name: 'app_detail_covoiturage')]
    public function index(Covoiturage $covoiturage): Response
    {
        $user = new User;
        $user = $user->getId();
        return $this->render('detail_covoiturage/index.html.twig', [
            'covoiturage' => $covoiturage
        ]);
    }
}
