<?php

namespace App\Validator\Constraints;

use PhpParser\Builder\Class_;
use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class SortConstraint extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(),
            new Assert\Type('integer'),
            new Assert\NotNull(),

        ];
    }

}
