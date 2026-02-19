<?php

namespace App\Controller;

use App\Entity\Covoiturage;
use App\Form\CovoiturageSearchType;
use App\Form\UserEditType;
use App\Model\CovoiturageSearch;
use App\Repository\CovoiturageRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class AccountController extends AbstractController
{
    #[Route('/compte', name: 'app_account', methods: ['GET','POST'])]
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

        $covoiturages = $covoiturageRepository->search($search);

        // Réservations de l'utilisateur 
        $reservations = $reservationRepository->findBy(
            ['idUser' => $user],
            ['created_at' => 'DESC']
        );

        // Covoiturages créés par le chauffeur
        $userCovoituragesDriver = $covoiturageRepository->findBy(
            ['idDriver' => $user],
            ['dateDeparture' => 'DESC']
        );

        /**
         *  Liste "passager" : on boucle sur les covoiturages auxquels l'utilisateur participe
         * MAIS on garde aussi la réservation associée (pour annuler / avis => reservation.id)
         */
        $passengerParticipations = [];

        foreach ($reservations as $reservation) {
            $covoiturage = $reservation->getIdCovoiturage();

            if (!$covoiturage instanceof Covoiturage) {
                continue;
            }

            // éviter d'afficher un covoiturage en "passager" si je suis le chauffeur
            if ($covoiturage->getIdDriver() && $covoiturage->getIdDriver() === $user) {
                continue;
            }

            // éviter doublons : 1 covoiturage = 1 entrée
            $cid = $covoiturage->getId();
            if (!isset($passengerParticipations[$cid])) {
                $passengerParticipations[$cid] = [
                    'covoiturage' => $covoiturage,
                    'reservation' => $reservation,
                ];
            }
        }

        $passengerParticipations = array_values($passengerParticipations);

        return $this->render('account/index.html.twig', [
            'covoiturageForm' => $covoiturageForm->createView(),
            'covoiturages' => $covoiturages,
            'userCovoituragesDriver' => $userCovoituragesDriver,
            'passengerParticipations' => $passengerParticipations,
            'reservations' => $reservations,
        ]);
    }


    #[Route('/compte/modifier', name: 'app_account_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile|null $photoFile */
            $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                $photoFile->move($this->getParameter('user_photos_dir'), $newFilename);

                // IMPORTANT : ici tu dois stocker un STRING dans l'entity (nom de fichier)
                // donc il faut un champ type string en BDD (ex: photoPath) ou photo (string)
                $user->setPhoto($newFilename);
            }

            $em->flush();
            $this->addFlash('success', 'Informations mises à jour.');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}


