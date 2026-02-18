<?php

namespace App\Controller;

use App\Entity\Covoiturage;
use App\Form\CovoiturageSearchType;
use App\Model\CovoiturageSearch;
use App\Repository\CovoiturageRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccountController extends AbstractController
{
    #[Route('/compte', name: 'app_account')]
    public function index(
        Request $request,
        CovoiturageRepository $repository,
        ReservationRepository $reservationRepository
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        // Formulaire de recherche
        $search = new CovoiturageSearch();
        $covoiturageForm = $this->createForm(CovoiturageSearchType::class, $search);
        $covoiturageForm->handleRequest($request);

        $covoiturages = $repository->search($search);

        // Réservations de l'utilisateur
        $reservations = $reservationRepository->findBy(
            ['idUser' => $user],
            ['created_at' => 'DESC']
        );

        //  Covoiturages créés par l'utilisateur (chauffeur)
        $userCovoituragesDriver = $repository->findBy(
            ['idDriver' => $user],
            ['dateDeparture' => 'DESC']
        );

        //  Covoiturages auxquels l'utilisateur participe (passager)
        $userCovoituragesPassenger = [];
        foreach ($reservations as $reservation) {
            $covoiturage = $reservation->getIdCovoiturage();

            if (!$covoiturage instanceof Covoiturage) {
                continue;
            }

            // éviter d'afficher un covoiturage en "passager" si je suis le chauffeur
            if ($covoiturage->getIdDriver() && $covoiturage->getIdDriver() === $user) {
                continue;
            }

            // éviter doublons
            $userCovoituragesPassenger[$covoiturage->getId()] = $covoiturage;
        }
        $userCovoituragesPassenger = array_values($userCovoituragesPassenger);

        return $this->render('account/index.html.twig', [
            'covoiturageForm' => $covoiturageForm->createView(),
            'covoiturages' => $covoiturages,
            'userCovoituragesDriver' => $userCovoituragesDriver,
            'userCovoituragesPassenger' => $userCovoituragesPassenger,
            'reservations' => $reservations,
        ]);
    }
}

