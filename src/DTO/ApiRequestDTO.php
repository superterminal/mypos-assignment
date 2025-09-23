<?php

namespace App\DTO;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class ApiRequestDTO
{
    abstract public static function fromRequest(Request $request): self;
    
    public function validate(ValidatorInterface $validator): array
    {
        $violations = $validator->validate($this);
        $errors = [];
        
        foreach ($violations as $violation) {
            $errors[] = $violation->getMessage();
        }
        
        return $errors;
    }
    
    public function isValid(ValidatorInterface $validator): bool
    {
        return count($this->validate($validator)) === 0;
    }
}
