<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\User;
use App\Entity\Vehicule;
use App\Repository\BrandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DriverController extends AbstractController
{
    #[Route('/driver', name: 'app_driver', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        BrandRepository $brandRepository
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) {

            $brandName = trim((string) $request->request->get('brand'));
            $model = trim((string) $request->request->get('model'));
            $color = trim((string) $request->request->get('color'));
            $registration = trim((string) $request->request->get('registration'));
            $firstRegistration = (string) $request->request->get('firstRegistration');
            $energie = trim((string) $request->request->get('energie'));
            $placesNbr = (int) $request->request->get('placesNbr');

            // bool plus fiable si ton form envoie "0"/"1"
            $petsAllowed = $request->request->get('petsAllowed') === '1';
            $smokingAllowed = $request->request->get('smokingAllowed') === '1';

            // Normalisation de la marque
            $brandNameNormalized = mb_convert_case($brandName, MB_CASE_TITLE, "UTF-8");

            $brand = $brandRepository->findOneBy(['libel' => $brandNameNormalized]);

            if (!$brand) {
                $brand = new Brand();
                $brand->setLibel($brandNameNormalized);
                $em->persist($brand);
            }

            $vehicule = new Vehicule();
            $vehicule->setIdDriver($user);  
            $vehicule->setIdBrand($brand);  

            $vehicule->setPlacesNbr($placesNbr);
            $vehicule->setModel($model);
            $vehicule->setColor($color);
            $vehicule->setRegistration($registration);
            $vehicule->setFirstRegistration(new \DateTime($firstRegistration));
            $vehicule->setEnergy($energie);

            $em->persist($vehicule);

            $user->setIsDriver(true);
            $user->setPetsAllowed($petsAllowed);
            $user->setSmokingAllowed($smokingAllowed);

            $em->flush();

            $this->addFlash('success', 'Profil chauffeur activé');
            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/driver/index.html.twig');
    }
}