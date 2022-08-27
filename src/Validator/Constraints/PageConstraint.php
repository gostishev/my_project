<?php


namespace App\Validator\Constraints;

use PhpParser\Builder\Class_;
use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class PageConstraint extends Compound
{
    public int $pageCount;

    public function __construct(mixed $options = null, int $pageCount = 1)
    {
        $this->pageCount = $pageCount;
        parent::__construct($options);
    }

    public function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(),
            new Assert\NotNull(),
            new Assert\Type('integer'),
            new Assert\Positive,
            new Assert\LessThanOrEqual(
                $this->pageCount
            ),
        ];
    }

}
