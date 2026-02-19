<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\CovoiturageRepository;
use App\Repository\CreditTransactionRepository;
use App\Repository\RoleRepository;
use App\Enum\CreditTransactionReason;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('', name: 'admin_dashboard', methods: ['GET'])]
    public function dashboard(
        CovoiturageRepository $covoiturageRepository,
        CreditTransactionRepository $creditTransactionRepository
    ): Response {

        $platformReason = CreditTransactionReason::COMMISSION_PLATEFORME; 

        $covoituragesPerDay = $covoiturageRepository->countPerDayLastDays(7);
        $platformCreditsPerDay = $creditTransactionRepository->platformCreditsPerDayLastDays(7, $platformReason);
        $totalPlatformCredits = $creditTransactionRepository->totalPlatformCredits($platformReason);
        $lastTransactions = $creditTransactionRepository->findLast(10);


        return $this->render('admin/index.html.twig', [
            'covoituragesPerDay' => $covoituragesPerDay,
            'platformCreditsPerDay' => $platformCreditsPerDay,
            'totalPlatformCredits' => $totalPlatformCredits,
            'lastTransactions' => $lastTransactions
        ]);
    }

    #[Route('/users', name: 'admin_users_index', methods: ['GET'])]
    public function usersIndex(UserRepository $userRepository): Response
    {
        $users = $userRepository->findBy([], ['id' => 'DESC']);

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/{id}/toggle-suspend', name: 'admin_users_toggle_suspend', methods: ['POST'])]
    public function toggleSuspend(
        User $user,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isCsrfTokenValid('toggle_suspend_' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_users_index');
        }

        // Eviter de se suspendre soi-même
        $current = $this->getUser();
        if ($current instanceof User && $current->getId() === $user->getId()) {
            $this->addFlash('warning', 'Vous ne pouvez pas suspendre votre propre compte.');
            return $this->redirectToRoute('admin_users_index');
        }

        $newValue = !(bool) $user->IsSupended();
        $user->setIsSupended($newValue);

        $em->flush();

        $this->addFlash('success', $newValue ? 'Compte suspendu.' : 'Compte réactivé.');
        return $this->redirectToRoute('admin_users_index');
    }

    #[Route('/employees/new', name: 'admin_employees_new', methods: ['GET', 'POST'])]
    public function newEmployee(
        Request $request,
        EntityManagerInterface $em,
        RoleRepository $roleRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $employee = new User();

        $form = $this->createForm(\App\Form\EmployeeCreateType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Password
            $plain = $form->get('plainPassword')->getData();
            $employee->setPassword($passwordHasher->hashPassword($employee, $plain));

            // Champs par défaut cohérents avec ton User
            $employee->setCredits(0);
            $employee->setIsDriver(false);
            $employee->setIsPassenger(false);
            $employee->setIsSupended(false);
            $employee->setCreatedAt(new \DateTimeImmutable());


            $roleEmployee = $roleRepository->findOneBy(['libel' => 'EMPLOYE']);
            if (!$roleEmployee) {
                $roleEmployee = $roleRepository->findOneBy(['libel' => 'ROLE_EMPLOYE']);
            }

            if ($roleEmployee) {
                $employee->setRole($roleEmployee);
            } else {
                $this->addFlash('warning', "Rôle employé introuvable (table Role). Le compte est créé sans rôle.");
            }

            $em->persist($employee);
            $em->flush();

            $this->addFlash('success', 'Compte employé créé.');
            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/employee.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
