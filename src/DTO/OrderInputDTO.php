<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;

class OrderInputDTO
{
    /**
     * @Assert\NotBlank
     * @Assert\Email(message = "The email is not a valid")
     * @Assert\NotNull
     */
    public $customerEmail;

    /**
     * @Assert\NotBlank
     * @Assert\NotNull
     * @Assert\Type("integer")
     */
    public $shipmentDate;

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @Assert\NotNull
     */
    public $billingType;

    /**
     * @Assert\NotBlank
     * @Assert\Type("array")
     * @Assert\NotNull
     */
    public $orderItems;

    public function __construct($customerEmail, $shipmentDate, $billingType, $orderItems)
    {
        $this->customerEmail = $customerEmail;
        $this->shipmentDate = $shipmentDate;
        $this->billingType = $billingType;
        $this->orderItems = $orderItems;
//        $this->requestOrderItemsAll = $requestOrderItemsAll;
    }

}
