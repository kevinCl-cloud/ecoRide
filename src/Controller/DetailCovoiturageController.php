<?php

namespace App\Controller;

use App\Entity\Covoiturage;
use App\Entity\Reservation;
use App\Entity\User;
use App\Repository\ReservationRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DetailCovoiturageController extends AbstractController
{
    #[Route('/detail/covoiturage{id}', name: 'app_detail_covoiturage')]
    public function index(Covoiturage $covoiturage): Response
    {
        $user = new User;
        $user = $user->getId();
        return $this->render('detail_covoiturage/index.html.twig', [
            'covoiturage' => $covoiturage
        ]);
    }


    #[Route('/participer{id}', name: 'app_participer')]
    public function participer(Request $request, Covoiturage $covoiturage, EntityManagerInterface $em, ReservationRepository $reservationRepository): Response
    {  
        $user = $this->getUser();

        if(!$user){
            throw $this->createAccessDeniedException('Vous devez etre connecter pour continuer.');
        }

        // verifier CSRF
        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('participer' . $covoiturage->getId(), $token)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // verifier que le chauffeur n'es pas le user
        if($covoiturage->getIdDriver() === $user) {
            $this->addFlash('danger', 'Vous êtes le chauffeur de ce trajet');
            return $this->redirectToRoute('app_detail_covoiturage', [
                'id' => $covoiturage->getId()
            ]);
        }

        // verifier s'il reste de la place
        if($covoiturage->getPlacesNbr() < 1) {
            $this->addFlash('danger', 'Ce trajet est complet');
            return $this->redirectToRoute('app_detail_covoiturage', [
                'id' => $covoiturage->getId()
            ]);
        }

        //verifier si crédits suffisant
        if($covoiturage->getPrice() > $user->getCredits()){
            $this->addFlash('danger', 'Vos crédits sont insuffisants');
            return $this->redirectToRoute('app_detail_covoiturage', [
                'id' => $covoiturage->getId()
            ]);
        }

        $alreadyReserved = $reservationRepository->findOneBy([
            'idUser' => $user,
            'idCovoiturage' => $covoiturage,
        ]);

        if ($alreadyReserved) {
            $this->addFlash('warning', 'Vous participez déjà à ce trajet.');
            return $this->redirectToRoute('app_detail_covoiturage', ['id' => $covoiturage->getId()]);
        }

        // 7) Création de la réservation + MAJ crédits & places
        $reservation = new Reservation();
        $reservation->setIdUser($user);
        $reservation->setIdCovoiturage($covoiturage);
        $reservation->setCreatedAt(new DateTime());

        // MAJ SI LE STATUT DE VALIDATION EST VALIDE
        $user->setCredits($user->getCredits() - $covoiturage->getPrice());
        $covoiturage->setPlacesNbr($covoiturage->getPlacesNbr() - 1);

        $em->persist($reservation);
        $em->flush();

        $this->addFlash('success', 'Participation confirmée !');

        // 8) Redirection (POST -> Redirect -> GET)
        return $this->redirectToRoute('app_detail_covoiturage', ['id' => $covoiturage->getId()]);

    }

}

