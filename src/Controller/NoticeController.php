<?php

namespace App\Controller;

use App\Entity\Notice;
use App\Entity\Reservation;
use App\Enum\NoticeStatus;
use App\Enum\ReservationStatus;
use App\Form\NoticeType;
use App\Service\CreditTransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NoticeController extends AbstractController
{
    #[Route('/reservation/{id}/notice', name: 'app_notice_create', methods: ['GET','POST'])]
    public function create(
        Reservation $reservation,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($reservation->getIdUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($reservation->getIdCovoiturage()->getStatut()->value !== 'TERMINE') {
            $this->addFlash('warning', "Vous ne pouvez laisser un avis que lorsque le trajet est terminé.");
            return $this->redirectToRoute('app_account');
        }

        if (count($reservation->getNotices()) > 0) {
            $this->addFlash('warning', "Vous avez déjà laissé un avis pour cette réservation.");
            return $this->redirectToRoute('app_account');
        }

        $notice = new Notice();
        $notice->setIdReservation($reservation);
        $notice->setStatus(NoticeStatus::EN_ATTENTE);
        $notice->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(NoticeType::class, $notice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($notice);
            $em->flush();

            $this->addFlash('success', 'Merci ! Votre avis a bien été envoyé.');
            return $this->redirectToRoute('app_account');
        }

        return $this->render('notice/index.html.twig', [
            'form' => $form->createView(),
            'reservation' => $reservation,
        ]);
    }

    #[Route('/reservation/{id}/ignorer', name: 'app_notice_ignore', methods: ['POST'])]
    public function ignore(
        Reservation $reservation,
        Request $request,
        EntityManagerInterface $em,
        CreditTransactionService $creditService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($reservation->getIdUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'ignore_notice' . $reservation->getId(),
            (string) $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Token invalide.');
        }

        $covoiturage = $reservation->getIdCovoiturage();
        if ($covoiturage->getStatut()->value !== 'TERMINE') {
            $this->addFlash('warning', 'Le trajet doit être terminé.');
            return $this->redirectToRoute('app_account');
        }

        if ($reservation->getStatus()->value !== 'EN_ATTENTE') {
            $this->addFlash('info', 'Cette réservation est déjà traitée.');
            return $this->redirectToRoute('app_account');
        }

        // Transaction DB (statut + paiement conducteur)
        $em->beginTransaction();
        try {
            // On valide la réservation sans avis
            $reservation->setStatus(ReservationStatus::TERMINEE);

            // Crédit chauffeur (prix - commission plateforme 2)
            $driver = $covoiturage->getIdDriver();
            $driverAmount = max(0, (int) $covoiturage->getPrice() - 2);

            $creditService->payDriver($driver, $reservation, $driverAmount);
            $creditService->flush();

            $em->commit();
        } catch (\Throwable $e) {
            $em->rollback();
            throw $e;
        }

        $this->addFlash('success', 'Trajet validé.');
        return $this->redirectToRoute('app_account');
    }
}

