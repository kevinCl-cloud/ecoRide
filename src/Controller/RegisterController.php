<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterController extends AbstractController
{
    #[Route('/inscription', name: 'inscription')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $registerForm = $this->createForm(RegisterUserType::class, $user);
        $registerForm->handleRequest($request);

        if($registerForm->isSubmitted() && $registerForm->isValid()){
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->render('register/index.html.twig',[
            'registerForm'=> $registerForm->createView()
        ]);
    }
}
