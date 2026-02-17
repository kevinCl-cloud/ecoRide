<?php

namespace App\Controller;

use App\Form\CovoiturageSearchType;
use App\Repository\CovoiturageRepository;
use App\Model\CovoiturageSearch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CovoiturageListController extends AbstractController
{
    #[Route('/covoiturage', name: 'app_covoiturage_list')]
    public function index(Request $request, CovoiturageRepository $repository): Response
    {
        //creer un formulaire de recherche a partir de CovoiturageSearch
        $search = new CovoiturageSearch();
        $covoiturageForm = $this->createForm(CovoiturageSearchType::class, $search);
        $covoiturageForm->handleRequest($request);

        $covoiturages = $repository->search($search);

        return $this->render('covoiturage_list/index.html.twig', [
            'covoiturageForm' => $covoiturageForm->createView(),
            'covoiturages' => $covoiturages
        ]);
    }
}
