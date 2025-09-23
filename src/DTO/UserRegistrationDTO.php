<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserRegistrationDTO
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 255)]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
        message: 'Password must contain at least one lowercase letter, one uppercase letter, and one number'
    )]
    public string $password = '';

    #[Assert\NotBlank]
    #[Assert\EqualTo(propertyPath: 'password', message: 'Passwords do not match')]
    public string $confirmPassword = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public string $firstName = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public string $lastName = '';

    #[Assert\Choice(choices: ['merchant', 'buyer'])]
    public string $userType = 'buyer';

    #[Assert\Choice(choices: ['merchant', 'buyer'])]
    public string $role = 'buyer';
}
