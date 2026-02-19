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
    #[Route('/covoiturage_list', name: 'app_covoiturage_list', methods: ['GET'])]
    public function index(Request $request, CovoiturageRepository $repository): Response
    {
        $search = new CovoiturageSearch();

        $covoiturageForm = $this->createForm(CovoiturageSearchType::class, $search, [
            'method' => 'GET',
        ]);
        
        $covoiturageForm->handleRequest($request);
        $covoiturages = [];
        $filters = [
            'energy' => $request->query->get('energy'),
            'maxPrice' => $request->query->get('maxPrice'),
            'maxDuration' => $request->query->get('maxDuration'),
            'minPlaces' => $request->query->get('minPlaces'),
        ];

        if($covoiturageForm->isSubmitted() && $covoiturageForm->isValid()){
            $covoiturages = $repository->search($search, $filters);
        }


        return $this->render('covoiturage_list/index.html.twig', [
            'covoiturageForm' => $covoiturageForm->createView(),
            'covoiturages' => $covoiturages,
        ]);
    }
}

