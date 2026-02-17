<?php

namespace App\Controller;

use App\Form\CovoiturageSearchType;
use App\Model\CovoiturageSearch;
use App\Repository\CovoiturageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccountController extends AbstractController
{
    #[Route('/compte', name: 'app_account')]
    public function index(Request $request, CovoiturageRepository $repository): Response
    {
        //creer un formulaire de recherche a partir de CovoiturageSearch
        $search = new CovoiturageSearch();
        $covoiturageForm = $this->createForm(CovoiturageSearchType::class, $search);
        $covoiturageForm->handleRequest($request);

        $covoiturages = $repository->search($search);

        return $this->render('account/index.html.twig', [ 
            'covoiturageForm' => $covoiturageForm->createView(),
            'covoiturages' => $covoiturages
        ]);
    }
}
