<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\CovoiturageSearchType;
use App\Model\CovoiturageSearch;
use App\Repository\CovoiturageRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    public function index(Request $request, CovoiturageRepository $repository, ReservationRepository $reservationRepository): Response
    {
        //creer un formulaire de recherche a partir de CovoiturageSearch
        $search = new CovoiturageSearch();
        $covoiturageForm = $this->createForm(CovoiturageSearchType::class, $search);
        $covoiturageForm->handleRequest($request);

        if ($covoiturageForm->isSubmitted() && $covoiturageForm->isValid()) {
            return $this->redirectToRoute('app_covoiturage_list', [
                'placeDeparture' => $search->getPlaceDeparture(),
                'placeArrival' => $search->getPlaceArrival(),
                'dateDeparture' => $search->getDateDeparture(),
            ]);
        }

        $covoiturages = $repository->search($search);

        $reservations = $reservationRepository->findBy(
            ['idUser' => $this->getUser()],
            ['created_at' => 'DESC']
        );

        return $this->render('reservation/index.html.twig', [
            'covoiturageForm' => $covoiturageForm->createView(),
            'covoiturages' => $covoiturages,
            'reservations' => $reservations
        ]);
    }

    #[Route('/reservation/{id}/annuler', name: 'app_reservation_annuler', methods: ['POST'])]
    public function annuler(
        Reservation $reservation,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        
        $user = $this->getUser();

        // verifier que la réservation appartiens à l'utilisateur
        if ($reservation->getIdUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        // verifier CSRF
        if (!$this->isCsrfTokenValid('cancel' . $reservation->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $covoiturage = $reservation->getIdCovoiturage();

        // Empêcher l’annulation si le trajet est déjà passé (ou commencé)
        if ($covoiturage->getDateDeparture() < new \DateTimeImmutable('today')) {
            $this->addFlash('danger', 'Impossible d’annuler un trajet déjà passé.');
            return $this->redirectToRoute('app_account');
        }

        //  Rembourser + rendre une place
        $user->setCredits($user->getCredits() + $covoiturage->getPrice());
        $covoiturage->setPlacesNbr($covoiturage->getPlacesNbr() + 1);

        // Supprimer la réservation 
        $em->remove($reservation);
        $em->flush();

        $this->addFlash('success', 'Réservation annulée. Vos crédits ont été remboursés.');

        return $this->redirectToRoute('app_account');
    }
}
