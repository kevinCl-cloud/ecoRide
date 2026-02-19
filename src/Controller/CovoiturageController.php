<?php

namespace App\Controller;

use App\Entity\Covoiturage;
use App\Enum\CovoiturageStatus;
use App\Form\CovoiturageSearchType;
use App\Model\CovoiturageSearch;
use App\Repository\CovoiturageRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CovoiturageController extends AbstractController
{
    #[Route('/covoiturage', name: 'app_covoiturage', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        CovoiturageRepository $covoiturageRepository,
        ReservationRepository $reservationRepository
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        // Formulaire de recherche
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

        // Historique chauffeur (mes covoiturages créés)
        $covoituragesDriver = $covoiturageRepository->findBy(
            ['idDriver' => $user],
            ['dateDeparture' => 'DESC']
        );

        // Réservations de l'utilisateur (sert à construire l'historique passager)
        $reservations = $reservationRepository->findBy(
            ['idUser' => $user],
            ['created_at' => 'DESC']
        );

        $passengerParticipations = [];

        foreach ($reservations as $reservation) {
            $covoiturage = $reservation->getIdCovoiturage();

            if (!$covoiturage instanceof Covoiturage) {
                continue;
            }

            // éviter d'afficher en passager un trajet dont je suis le chauffeur
            if ($covoiturage->getIdDriver() && $covoiturage->getIdDriver() === $user) {
                continue;
            }

            $cid = $covoiturage->getId();

            // éviter doublons : 1 covoiturage = 1 entrée
            if (!isset($passengerParticipations[$cid])) {
                $passengerParticipations[$cid] = [
                    'covoiturage' => $covoiturage,
                    'reservation' => $reservation,
                ];
            }
        }

        $passengerParticipations = array_values($passengerParticipations);

        return $this->render('covoiturage/index.html.twig', [
            'covoiturageForm' => $covoiturageForm->createView(),
            'covoituragesDriver' => $covoituragesDriver,
            'passengerParticipations' => $passengerParticipations,
            'reservations' => $reservations,
        ]);
    }

    
    #[Route('/covoiturage/{id}/demarrer', name: 'app_covoiturage_start', methods: ['POST'])]
    public function start(
        Covoiturage $covoiturage,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->isCsrfTokenValid('start_covoiturage' . $covoiturage->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        if ($covoiturage->getIdDriver() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($covoiturage->getStatut() !== CovoiturageStatus::PREVU) {
            $this->addFlash('warning', 'Ce covoiturage ne peut pas être démarré.');
            return $this->redirectToRoute('app_covoiturage');
        }

        $covoiturage->setStatut(CovoiturageStatus::EN_COURS);
        $em->flush();

        $this->addFlash('success', 'Le covoiturage a démarré.');
        return $this->redirectToRoute('app_covoiturage');
    }


    #[Route('/covoiturage/{id}/annuler', name: 'app_covoiturage_cancel', methods: ['POST'])]
    public function cancel(
        Covoiturage $covoiturage,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // CSRF
        if (!$this->isCsrfTokenValid('cancel_covoiturage' . $covoiturage->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // Seul le chauffeur peut annuler
        if ($covoiturage->getIdDriver() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        //  on annule seulement si PREVU
        if ($covoiturage->getStatut() !== CovoiturageStatus::PREVU) {
            $this->addFlash('warning', 'Ce covoiturage ne peut pas être annulé.');
            return $this->redirectToRoute('app_account'); 
        }

        // Mise à jour statut
        $covoiturage->setStatut(CovoiturageStatus::ANNULE);

        $em->flush();

        $this->addFlash('success', 'Le covoiturage a été annulé.');
        return $this->redirectToRoute('app_account'); 
    }



    #[Route('/covoiturage/{id}/terminer', name: 'app_covoiturage_finish', methods: ['POST'])]
    public function finish(
        Covoiturage $covoiturage,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    
        // CSRF
        if (!$this->isCsrfTokenValid('finish_covoiturage' . $covoiturage->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }
    
        // Seul le chauffeur peut terminer
        if ($covoiturage->getIdDriver() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
    
        // On ne peut terminer que si EN_COURS
        if ($covoiturage->getStatut() !== CovoiturageStatus::EN_COURS) {
            $this->addFlash('warning', 'Ce covoiturage ne peut pas être terminé.');
            return $this->redirectToRoute('app_account'); 
        }
    
        // Mise à jour statut
        $covoiturage->setStatut(CovoiturageStatus::TERMINE);
    
        $em->flush();
    
        $this->addFlash('success', 'Le covoiturage est maintenant terminé.');
        return $this->redirectToRoute('app_account'); 
    }


    #[Route('/covoiturage/{id}/supprimer', name: 'app_covoiturage_delete', methods: ['POST'])]
    public function delete(
        Covoiturage $covoiturage,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // CSRF
        if (!$this->isCsrfTokenValid('delete_covoiturage' . $covoiturage->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // Seul le chauffeur peut supprimer
        if ($covoiturage->getIdDriver() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        //  suppression uniquement si terminé ou annulé
        if (!in_array($covoiturage->getStatut(), [CovoiturageStatus::TERMINE, CovoiturageStatus::ANNULE], true)) {
            $this->addFlash('warning', 'Ce covoiturage ne peut pas être supprimé (statut invalide).');
            return $this->redirectToRoute('app_account');
        }

        $em->remove($covoiturage);
        $em->flush();

        $this->addFlash('success', 'Le covoiturage a bien été supprimé.');
        return $this->redirectToRoute('app_account');
    }

}

