<?php

namespace App\Validator\Constraints;

use PhpParser\Builder\Class_;
use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;
use App\Validator\FieldUnique;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class PageSizeConstraint extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(),
            new Assert\NotNull(),
            new Assert\Type('integer'),
            new Assert\Range([
                    'min' => 1,
                    'max' => 101,
                    'notInRangeMessage' => "Query parameter pageSize must be between {{ min }} and {{ max }} tall to enter",]
            ),
        ];
    }

}
