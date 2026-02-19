<?php

namespace App\Controller;

use App\Entity\Role;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminSetupController extends AbstractController
{
    #[Route('/setup-admin', name: 'app_setup_admin')]
    public function setupAdmin(
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        EntityManagerInterface $em
    ): Response {

        $email = 'admin@ecoride.fr';

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return new Response("Utilisateur introuvable : $email");
        }

        $roleAdmin = $roleRepository->findOneBy(['libel' => 'ROLE_ADMIN']);

        if (!$roleAdmin) {
            $roleAdmin = new Role();
            $roleAdmin->setLibel('ROLE_ADMIN');
            $em->persist($roleAdmin);
        }

        $user->setRole($roleAdmin);
        $em->flush();

        return new Response("ROLE_ADMIN attribué avec succès à $email");
    }
}