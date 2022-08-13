<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

class ProductOutputDTO
{
    /**
     * @Groups ("group1")
     */
    public $id;


    /**
     * @Groups ("group1")
     */
    public $name;

    /**
     * @Groups ("group1")
     */
    public $description;

    /**
     * @Groups ("group1")
     */
    public $price;

    /**
     * @Groups ("group1")
     */
    public $createdAt;

    /**
     * @Groups ("group1")
     */
    public $category;

    public function __construct($id, $name, $description, $price, $createdAt, $category)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->createdAt = $createdAt;
        $this->category = $category;
    }

}
