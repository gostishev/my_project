<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;
use Symfony\Component\Serializer\Annotation\Groups;

class OrderOutputDTO
{
    /**
     * @Groups ("group1")
     */
    public $customerEmail;

    /**
     * @Groups ("group1")
     */
    public $shipmentDate;

    /**
     * @Groups ("group1")
     */
    public $billingType;

//    /**
//     * @Groups ("group1")
//     */
//    public $billingTypeName;

    /**
     * @Groups ("group1")
     */
    public $orderItems;

    public function __construct($customerEmail, $shipmentDate, $billingType,array $orderItems)
    {
        $this->customerEmail = $customerEmail;
        $this->shipmentDate = $shipmentDate;
        $this->billingType = $billingType;
        $this->orderItems = $orderItems;
    }

}
