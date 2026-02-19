<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminSetupController extends AbstractController
{
    #[Route('/make-admin', name: 'make_admin')]
    public function makeAdmin(
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {

        $user = $userRepository->findOneBy([
            'email' => 'admin@ecoride.com'
        ]);

        if (!$user) {
            return new Response('Utilisateur non trouvé');
        }

        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $em->flush();

        return new Response('Admin créé avec succès');
    }
}