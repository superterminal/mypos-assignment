<?php

namespace App\Controller;

use App\DTO\UserRegistrationDTO;
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
        private ValidatorInterface $validator
    ) {
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
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
            return $this->redirectToRoute('app_home');
        }

        $dto = new UserRegistrationDTO();
        $errors = [];

        if ($request->isMethod('POST')) {
            $dto->email = $request->request->get('email', '');
            $dto->password = $request->request->get('password', '');
            $dto->firstName = $request->request->get('firstName', '');
            $dto->lastName = $request->request->get('lastName', '');
            $dto->role = $request->request->get('role', 'buyer');

            $violations = $this->validator->validate($dto);
            if (count($violations) === 0) {
                try {
                    $user = $this->userService->register($dto);
                    $this->addFlash('success', 'Registration successful! You can now log in.');
                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Registration failed. Please try again.');
                }
            } else {
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }
            }
        }

        return $this->render('auth/register.html.twig', [
            'dto' => $dto,
            'errors' => $errors,
        ]);
    }

    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $email = '';
        $message = '';

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email', '');
            
            if ($this->userService->generateResetToken($email)) {
                $message = 'If an account with that email exists, a password reset link has been sent.';
            } else {
                $message = 'If an account with that email exists, a password reset link has been sent.';
            }
        }

        return $this->render('auth/forgot_password.html.twig', [
            'email' => $email,
            'message' => $message,
        ]);
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function resetPassword(string $token, Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $message = '';
        $error = '';

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password', '');
            $confirmPassword = $request->request->get('confirmPassword', '');

            if ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters long.';
            } else {
                if ($this->userService->resetPassword($token, $password)) {
                    $message = 'Password reset successful! You can now log in.';
                } else {
                    $error = 'Invalid or expired reset token.';
                }
            }
        }

        return $this->render('auth/reset_password.html.twig', [
            'token' => $token,
            'message' => $message,
            'error' => $error,
        ]);
    }
}
