<?php

namespace App\Controller;

use App\Form\CovoiturageSearchType;
use App\Model\CovoiturageSearch;
use App\Repository\CovoiturageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    public function index(Request $request, CovoiturageRepository $repository): Response
    {
         //creer un formulaire de recherche a partir de CovoiturageSearch
        $search = new CovoiturageSearch();
        $covoiturageForm = $this->createForm(CovoiturageSearchType::class, $search);
        $covoiturageForm->handleRequest($request);

        if($covoiturageForm->isSubmitted() && $covoiturageForm->isValid()){
            return $this->redirectToRoute('app_covoiturage_list', [
                'placeDeparture' => $search->getPlaceDeparture(),
                'placeArrival' => $search->getPlaceArrival(),
                'dateDeparture' => $search->getDateDeparture(),
            ]);
        }

        $covoiturages = $repository->search($search);
        
        return $this->render('reservation/index.html.twig', [
            'covoiturageForm' => $covoiturageForm->createView(),
            'covoiturages' => $covoiturages
        ]);
    }
}
