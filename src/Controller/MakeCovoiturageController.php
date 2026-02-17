<?php

namespace App\Controller;

use App\Entity\Covoiturage;
use App\Form\CovoiturageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MakeCovoiturageController extends AbstractController
{
    #[Route('/creer/covoiturage', name: 'app_make_covoiturage')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        //verifier si l'utilisateur est bien un chauffeur
        if(!$user || !$user->isDriver() ) {
            $this->addFlash('warning', 'Accès réservé aux chauffeurs.');
            return $this->redirectToRoute('app_account');
            }
        // recuperer les infos du vehicule via le user
        $vehicule = $user->getVehicules();

        // creer un formulaire a partir de l'objet covoiturage
        $covoiturage = new Covoiturage();
        $covoiturageForm = $this->createForm(CovoiturageType::class, $covoiturage);
        $covoiturageForm->handleRequest($request);

        //verifier si le formulaire est bien soumis et valide
        if($covoiturageForm->isSubmitted() && $covoiturageForm->isValid()){
            $covoiturage->setCreateAt(new \DateTimeImmutable());
            $covoiturage->setIdDriver($user);
            $covoiturage->setIdVehicule($vehicule[0]);
        //insérer les données en base
            $entityManager->persist($covoiturage);   
            $entityManager->flush();
            $this->addFlash(
            'success',
            'Votre covoiturage a été créé avec succès !'
            );

            return $this->redirectToRoute('app_account');
        }
        return $this->render('account/make_covoiturage/index.html.twig', [
            'covoiturageForm' => $covoiturageForm->createView()
        ]);
    }
}
