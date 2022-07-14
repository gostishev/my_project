<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;

class CategoryInputDTO
{
    /**
     * @CustomAssert\FieldUnique()
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Assert\NotNull
     */
    public $name;

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @Assert\NotNull
     */
    public $sort;

    public function __construct($name, $sort)
    {
        $this->name = $name;
        $this->sort = $sort;
    }

}