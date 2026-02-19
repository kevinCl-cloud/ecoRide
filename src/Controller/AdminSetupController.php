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
        // 1) Protection simple par token (évite qu'un inconnu devienne admin)
        $providedToken = (string) $request->query->get('token', '');
        $expectedToken = hash('sha256', (string) ($_ENV['APP_SECRET'] ?? getenv('APP_SECRET') ?? ''));

        if ($providedToken === '' || !hash_equals($expectedToken, $providedToken)) {
            return new Response('Accès refusé (token invalide).', 403);
        }

        // 2) Récupérer l'utilisateur cible
        $email = 'admin@ecoride.fr';
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return new Response(sprintf('Utilisateur introuvable pour l’email : %s', $email), 404);
        }

        // 3) Créer le rôle ROLE_ADMIN s'il n'existe pas
        $roleAdmin = $roleRepository->findOneBy(['libel' => 'ROLE_ADMIN']);

        if (!$roleAdmin) {
            $roleAdmin = new Role();
            $roleAdmin->setLibel('ROLE_ADMIN');
            $em->persist($roleAdmin);
        }

        // 4) Assigner le rôle à l'utilisateur
        $user->setRole($roleAdmin);
        $em->flush();

        return new Response(
            "OK ✅ ROLE_ADMIN créé (si besoin) et attribué à {$email}.\n" .
            "⚠️ Supprime ensuite ce contrôleur + redeploy.\n"
        );
    }
}