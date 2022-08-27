<?php

namespace App\Helper;

use App\Exception\CustomErrorException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorInputDTO
{
    public function validateInput(ValidatorInterface $validator, $dto)
    {
        /** @var   ConstraintViolationList $violations */
        $violations = $validator->validate($dto);
        if (0 !== count($violations)) {
            throw new CustomErrorException("", 422, null, $violations->getIterator());
        }

        return true;
    }
}
