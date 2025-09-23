<?php

namespace App\Controller;

use App\DTO\UserRegistrationDTO;
use App\Service\EmailService;
use App\Service\LoggerService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private ValidatorInterface $validator,
        private EmailService $emailService,
        private LoggerService $logger
    ) {
    }

    #[Route('/login', name: 'app_login', methods: ['GET'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirect('/');
        }

        // Redirect to React app
        return $this->redirect('/');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirect('/');
        }

        // Redirect to React app
        return $this->redirect('/');
    }

    // Removed forgot-password route - now handled by React app
    // The React app will make API calls to handle forgot password functionality

    // Removed reset-password route - now handled by React app
    // The React app will make API calls to handle password reset functionality
}
