<?php

namespace App\Validator\Constraints;

use PhpParser\Builder\Class_;
use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class CategoryProductConstraint extends Compound
{
    public function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(),
            new Assert\NotNull(),
            new Assert\Type('integer'),
        ];
    }

}
