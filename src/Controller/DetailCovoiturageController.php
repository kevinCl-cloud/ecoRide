<?php

namespace App\Controller;

use App\Entity\Covoiturage;
use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Service\CreditTransactionService;
use App\Enum\CreditTransactionReason;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DetailCovoiturageController extends AbstractController
{
    #[Route('/detail/covoiturage/{id}', name: 'app_detail_covoiturage', methods: ['GET'])]
    public function index(Covoiturage $covoiturage): Response
    {
        return $this->render('detail_covoiturage/index.html.twig', [
            'covoiturage' => $covoiturage
        ]);
    }

    #[Route('/participer/{id}', name: 'app_participer', methods: ['POST'])]
    public function participer(
        Request $request,
        Covoiturage $covoiturage,
        EntityManagerInterface $em,
        ReservationRepository $reservationRepository,
        CreditTransactionService $creditService
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour continuer.');
        }

        // CSRF
        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('participer' . $covoiturage->getId(), $token)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // Chauffeur ne peut pas réserver son propre trajet
        if ($covoiturage->getIdDriver() === $user) {
            $this->addFlash('danger', 'Vous êtes le chauffeur de ce trajet.');
            return $this->redirectToRoute('app_detail_covoiturage', ['id' => $covoiturage->getId()]);
        }

        // Places
        if ($covoiturage->getPlacesNbr() < 1) {
            $this->addFlash('danger', 'Ce trajet est complet.');
            return $this->redirectToRoute('app_detail_covoiturage', ['id' => $covoiturage->getId()]);
        }

        // Déjà réservé ?
        $alreadyReserved = $reservationRepository->findOneBy([
            'idUser' => $user,
            'idCovoiturage' => $covoiturage,
        ]);
        if ($alreadyReserved) {
            $this->addFlash('warning', 'Vous participez déjà à ce trajet.');
            return $this->redirectToRoute('app_detail_covoiturage', ['id' => $covoiturage->getId()]);
        }

        //  Crédits nécessaires = prix + commission plateforme (2)
        $price = (int) $covoiturage->getPrice();
        $fee = 2;
        $totalNeeded = $price + $fee;

        if ($user->getCredits() < $totalNeeded) {
            $this->addFlash('danger', 'Vos crédits sont insuffisants (prix + commission plateforme).');
            return $this->redirectToRoute('app_detail_covoiturage', ['id' => $covoiturage->getId()]);
        }

        // Transaction DB : réservation + places + transactions 
        $em->beginTransaction();
        try {
            //  Créer réservation
            $reservation = new Reservation();
            $reservation->setIdUser($user);
            $reservation->setIdCovoiturage($covoiturage);
            $reservation->setCreatedAt(new \DateTime());

            //  statut si tu l'as ajouté
            if (method_exists($reservation, 'setStatut')) {
                $reservation->setStatus('CONFIRMEE');
            }

            $em->persist($reservation);

            //  MAJ places
            $covoiturage->setPlacesNbr($covoiturage->getPlacesNbr() - 1);

            // Débit + log transactions 
            $creditService->debit($user, $reservation, $price, CreditTransactionReason::PAIEMENT_RESERVATION);
            $creditService->debit($user, $reservation, $fee, CreditTransactionReason::COMMISSION_PLATEFORME);

            // 4) Flush
            $creditService->flush();

            $em->commit();
        } catch (\Throwable $e) {
            $em->rollback();
            throw $e;
        }

        $this->addFlash('success', 'Participation confirmée !');
        return $this->redirectToRoute('app_account');
    }
}
