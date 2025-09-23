<?php

namespace App\Controller\Api;

use App\DTO\UserRegistrationDTO;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class AuthApiController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private ValidatorInterface $validator,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    #[Route('/user/me', name: 'app_api_user_me')]
    public function getCurrentUser(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'fullName' => $user->getFullName(),
            'isMerchant' => $user->isMerchant(),
            'isBuyer' => $user->isBuyer(),
            'roles' => $user->getRoles()
        ]);
    }

    #[Route('/login', name: 'app_api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'Email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userService->authenticateUser($data['email'], $data['password']);
            
            if (!$user) {
                return new JsonResponse(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
            }

            // Create authentication token
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $this->tokenStorage->setToken($token);

            return new JsonResponse([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'fullName' => $user->getFullName(),
                    'isMerchant' => $user->isMerchant(),
                    'isBuyer' => $user->isBuyer(),
                    'roles' => $user->getRoles()
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Authentication failed: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/register', name: 'app_api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        $dto = new UserRegistrationDTO();
        $dto->email = $data['email'] ?? '';
        $dto->password = $data['password'] ?? '';
        $dto->confirmPassword = $data['confirmPassword'] ?? '';
        $dto->firstName = $data['firstName'] ?? '';
        $dto->lastName = $data['lastName'] ?? '';
        $dto->userType = $data['userType'] ?? 'buyer';
        $dto->role = $data['userType'] ?? 'buyer'; // Map userType to role for backward compatibility

        // Validate the DTO
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userService->register($dto);
            
            return new JsonResponse([
                'message' => 'User registered successfully',
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'fullName' => $user->getFullName(),
                'isMerchant' => $user->isMerchant(),
                'isBuyer' => $user->isBuyer(),
                'roles' => $user->getRoles()
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Registration failed: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/forgot-password', name: 'app_api_forgot_password', methods: ['POST'])]
    public function forgotPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['email'])) {
            return new JsonResponse(['error' => 'Email is required'], Response::HTTP_BAD_REQUEST);
        }

        $email = $data['email'];
        
        try {
            $success = $this->userService->generateResetToken($email);
            
            if ($success) {
                // User found and email sent successfully
                return new JsonResponse([
                    'message' => 'If an account with that email exists, a password reset link has been sent.'
                ]);
            } else {
                // User not found, but still return success message for security
                return new JsonResponse([
                    'message' => 'If an account with that email exists, a password reset link has been sent.'
                ]);
            }
        } catch (\Exception $e) {
            // Log the actual error for debugging
            error_log('Password reset error: ' . $e->getMessage());
            
            // Still return success message for security
            return new JsonResponse([
                'message' => 'If an account with that email exists, a password reset link has been sent.'
            ]);
        }
    }

    #[Route('/reset-password', name: 'app_api_reset_password', methods: ['POST'])]
    public function resetPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['token']) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'Token and password are required'], Response::HTTP_BAD_REQUEST);
        }

        $token = $data['token'];
        $password = $data['password'];
        
        try {
            if ($this->userService->resetPassword($token, $password)) {
                return new JsonResponse(['message' => 'Password reset successful! You can now log in.']);
            } else {
                return new JsonResponse(['error' => 'Invalid or expired reset token.'], Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Password reset failed: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/logout', name: 'app_api_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        // Clear the authentication token
        $this->tokenStorage->setToken(null);
        
        return new JsonResponse(['message' => 'Logout successful']);
    }
}
