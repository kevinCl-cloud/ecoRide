<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MakeCovoiturageController extends AbstractController
{
    #[Route('/make/covoiturage', name: 'app_make_covoiturage')]
    public function index(): Response
    {
        return $this->render('account/make_covoiturage/index.html.twig');
    }
}
