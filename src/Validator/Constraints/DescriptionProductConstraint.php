<?php

namespace App\Validator\Constraints;

use PhpParser\Builder\Class_;
use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class DescriptionProductConstraint extends Compound
{
    public function getConstraints(array $options): array
    {
        return [
            new Assert\Type('string'),
            new Assert\Length(
                max: 1000,
                maxMessage: "Your description cannot be longer than 1000 characters"
            )
        ];
    }


}
