<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterUserType;
use App\Service\CreditTransactionService;
use App\Enum\CreditTransactionReason;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterController extends AbstractController
{
    #[Route('/inscription', name: 'inscription', methods: ['GET','POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        CreditTransactionService $creditService
    ): Response {
        $user = new User();
        $registerForm = $this->createForm(RegisterUserType::class, $user);
        $registerForm->handleRequest($request);

        if ($registerForm->isSubmitted() && $registerForm->isValid()) {

            $photoFile = $registerForm->get('photo')->getData();

            if ($photoFile) {
                $newFilename = uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/photos',
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw $e;
                }

                $user->setPhoto($newFilename);
            }

            if (method_exists($user, 'setCreatedAt')) {
                $user->setCreatedAt(new \DateTimeImmutable());
            }

            $em->beginTransaction();
            try {
                $em->persist($user);
                $em->flush(); 

                $creditService->credit($user, null, 20, CreditTransactionReason::BONUS_INSCRIPTION);
                $creditService->flush();

                $em->commit();
            } catch (\Throwable $e) {
                $em->rollback();
                throw $e;
            }

            $this->addFlash('success', 'Votre compte a été créé avec succès !');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('register/index.html.twig', [
            'registerForm' => $registerForm->createView()
        ]);
    }
}
