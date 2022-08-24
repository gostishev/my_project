<?php

namespace App\Validator\Constraints;

use PhpParser\Builder\Class_;
use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;
use App\Validator\FieldUnique;

/**
 * @Annotation
 */
class NameConstraint extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(),
            new Assert\NotNull(),
            new Assert\Type('string'),
            new CustomAssert\FieldUnique(),
        ];
    }

}
