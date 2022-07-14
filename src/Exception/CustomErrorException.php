<?php

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolation;

class CustomErrorException extends \Exception
{
    private \ArrayIterator $violations;

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null, \ArrayIterator $violations = new \ArrayIterator([]))
    {
        parent::__construct($message, $code, $previous);
        $this->violations = $violations;
    }

    public function getViolations(): array
    {
        $errors = [];
        /** @var ConstraintViolation $violation */
        foreach ($this->violations as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }
        return $errors;


    }

}