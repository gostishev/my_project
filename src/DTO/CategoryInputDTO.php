<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;
use App\Validator\Constraints\NameConstraint;
use App\Helper\NotPassedClass;

class CategoryInputDTO
{
    /**
     * @Assert\AtLeastOneOf({
     *     @CustomAssert\Constraints\NameConstraint,
     *     @Assert\EqualTo(value=NotPassedClass::NOT_PASSED),
     *     })
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