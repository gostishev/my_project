<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;
use App\Validator\Constraints\DescriptionProductConstraint;
use App\Helper\NotPassedClass;

class ProductInputDTO
{

    /**
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Assert\NotNull
     */
    public $name;

    /**
     * @Assert\Type("string")
     * @Assert\Length(
     *      max = 1000,
     *      maxMessage = "Your description cannot be longer than 1000 characters"
     * )
     */
    public $description;

    /**
     * @Assert\NotBlank
     * @Assert\Type("float")
     * @Assert\NotNull
     */
    public $price;

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @Assert\NotNull
     */
    public $category;

    public function __construct($name, $price, $category, $description = "")
    {
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->category = $category;
    }

}
