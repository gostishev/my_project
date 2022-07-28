<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

class ProductOutputDTO
{
    /**
     * @Groups ("group1")
     */
    public $id;

//    /**
//     * @Assert\NotBlank
//     * @Assert\Type("string")
//     * @Assert\NotNull
//     */
    /**
     * @Groups ("group1")
     */
    public $name;

//    /**
//     * @Assert\Type("string")
//     * @Assert\Length(
//     *      max = 50,
//     *      maxMessage = "Your description cannot be longer than 1000 characters"
//     * )
//     */
    /**
     * @Groups ("group1")
     */
    public $description;

//    /**
//     * @Assert\NotBlank
//     * @Assert\Type("float")
//     * @Assert\NotNull
//     */
    /**
     * @Groups ("group1")
     */
    public $price;

//@Assert\DateTime
// @var string A "Y-m-d H:i:s" formatted value
//    /**
//     * @Assert\NotBlank
//     * @Assert\NotNull
//     * @Assert\Type ("integer")
//     */
    /**
     * @Groups ("group1")
     */
    public $createdAt;

//    /**
//     * @Assert\NotBlank
//     * @Assert\Type("integer")
//     * @Assert\NotNull
//     */
    /**
     * @Groups ("group1")
     */
    public $category;

    public function __construct($id, $name, $description, $price, $createdAt, $category )
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->createdAt = $createdAt;
        $this->category = $category;
    }

}
