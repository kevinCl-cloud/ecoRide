<?php

namespace App\Controller;

use App\Entity\Role;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminSetupController extends AbstractController
{
    #[Route('/setup-admin', name: 'app_setup_admin', methods: ['GET'])]
    public function setupAdmin(
        Request $request,
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        EntityManagerInterface $em
    ): Response {
        $providedToken = (string) $request->query->get('token', '');
        $expectedToken = (string) ($_ENV['ADMIN_SETUP_TOKEN'] ?? getenv('ADMIN_SETUP_TOKEN') ?? '');

        if ($expectedToken === '' || $providedToken === '' || !hash_equals($expectedToken, $providedToken)) {
            return new Response('Accès refusé (token invalide).', 403);
        }

        $email = 'admin@ecoride.fr';
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return new Response("Utilisateur introuvable : {$email}", 404);
        }

        $roleAdmin = $roleRepository->findOneBy(['libel' => 'ROLE_ADMIN']);
        if (!$roleAdmin) {
            $roleAdmin = new Role();
            $roleAdmin->setLibel('ROLE_ADMIN');
            $em->persist($roleAdmin);
        }

        $user->setRole($roleAdmin);
        $em->flush();

        return new Response("OK ROLE_ADMIN attribué à {$email}");
    }
}