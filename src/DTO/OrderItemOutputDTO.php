<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;
use Symfony\Component\Serializer\Annotation\Groups;

class OrderItemOutputDTO
{
    /**
     * @Groups ("group1")
     */
    public $productName;

    /**
     * @Groups ("group1")
     */
    public $productPrice;

    /**
     * @Groups ("group1")
     */
    public $productQuantity;

    public function __construct($productName, $productPrice, $productQuantity)
    {
        $this->productName = $productName;
        $this->productPrice = $productPrice;
        $this->productQuantity = $productQuantity;
    }

}
