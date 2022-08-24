<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;
use Symfony\Component\Validator\Constraints\ExpressionSyntax;

class CategoryOrderInputDTO
{
    /**
     * @Assert\ExpressionSyntax(
     *     allowedVariables={"ASC", "DESC"},
     *     message="Query parameter order not equal to ASC or DESC"
     * )
     */
    public $order;

    public function __construct($order)
    {
        $this->order = $order;
    }
}
