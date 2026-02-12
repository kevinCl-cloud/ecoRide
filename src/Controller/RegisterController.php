<?php

namespace App\Controller;
use App\Form\RegisterUserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterController extends AbstractController
{
    #[Route('/inscription', name: 'inscription')]
    public function index(): Response
    {
        $registerForm = $this->createForm(RegisterUserType::class);
        return $this->render('register/index.html.twig',[
            'registerForm'=> $registerForm->createView()
        ]);
    }
}
