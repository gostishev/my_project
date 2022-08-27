<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;

class OrderItemInputDTO
{
    /**
     * @Assert\Collection(
     *     fields={
     *         "id"  = @Assert\Required({@Assert\NotBlank, @Assert\NotNull, @Assert\Type("integer")}),
     *         "quantity" = @Assert\Required({@Assert\NotBlank, @Assert\NotNull, @Assert\Type("integer")})
     *     }
     * )
     */
    public array $requestOrderItem;


    public function __construct( $requestOrderItem)
    {
        $this->requestOrderItem = $requestOrderItem;
    }

}
