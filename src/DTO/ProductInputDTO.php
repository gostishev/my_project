<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;

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
     *      max = 50,
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

//@Assert\DateTime
// @var string A "Y-m-d H:i:s" formatted value
    /**
     * @Assert\NotBlank
     * @Assert\NotNull
     * @Assert\Type ("integer")
     */
    public $createdAt;

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @Assert\NotNull
     */
    public $category;

    public function __construct($name, $description, $price, $createdAt, $category )
    {
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->createdAt = $createdAt;
        $this->category = $category;
    }

}
