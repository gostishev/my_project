<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;
use Symfony\Component\Validator\Constraints\ExpressionSyntax;

class OrderGetInputDTO
{
    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @Assert\NotNull
     */
    public $page;

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @Assert\NotNull
     */
    public $pageSize;

    /**
     * @Assert\NotBlank
     * @Assert\GreaterThan(1)
     * @Assert\NotNull
     */
    public $entityCount;

    public function __construct($page, $pageSize, $entityCount)
    {
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->entityCount = $entityCount;

    }
}
