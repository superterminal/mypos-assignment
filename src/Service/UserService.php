<?php

namespace App\Service;

use App\DTO\UserRegistrationDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EmailService $emailService
    ) {
    }

    public function register(UserRegistrationDTO $dto): User
    {
        $user = new User();
        $user->setEmail($dto->email);
        $user->setFirstName($dto->firstName);
        $user->setLastName($dto->lastName);
        
        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
        $user->setPassword($hashedPassword);
        
        // Set role
        $role = $dto->role === 'merchant' ? User::ROLE_MERCHANT : User::ROLE_BUYER;
        $user->setRoles([$role]);

        // Ensure createdAt is set (should be set in constructor, but let's be explicit)
        if (!$user->getCreatedAt()) {
            $user->setCreatedAt(new \DateTime());
        }

        $this->userRepository->save($user, true);

        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    public function generateResetToken(string $email): bool
    {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = new \DateTime('+1 hour');

        $user->setResetToken($token);
        $user->setResetTokenExpiresAt($expiresAt);

        $this->userRepository->save($user, true);

        // Send reset email
        $this->emailService->sendPasswordResetEmail($user, $token);

        return true;
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $user = $this->userRepository->findByResetToken($token);
        
        if (!$user || !$user->isResetTokenValid()) {
            return false;
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);

        $this->userRepository->save($user, true);

        return true;
    }

    public function verifyEmail(User $user): void
    {
        $user->setIsVerified(true);
        $this->userRepository->save($user, true);
    }
}
