<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity("name")
 */
class CategoryInputDTO
{
    /**
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Assert\NotNull
     */
    public ?string $name;

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @Assert\NotNull
     */
    public ?int $sort;

    public function __construct($name, $sort)
    {
        $this->name = $name;
        $this->sort = $sort;
    }

}