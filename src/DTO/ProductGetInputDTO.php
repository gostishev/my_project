<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;
use App\Helper\NotPassedClass;

class ProductGetInputDTO
{
    /**
     * @Assert\ExpressionSyntax(
     *     allowedVariables={"ASC", "DESC"},
     *     message="Query parameter order not equal to ASC or DESC"
     * )
     */
    public $orderType;

    /**
     * @Assert\ExpressionSyntax(
     *     allowedVariables={"id", "name", "price", "createdAt"},
     *     message="Query parameter order not equal to id or name or price or createdAt"
     * )
     */
    public $orderBy;

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @Assert\NotNull
     */
    public $pageSize;

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @Assert\NotNull
     */
    public $page;

    /**
     * @Assert\EqualTo(value="category")
     */
    public $filterBy;

//    /**
//     * @Assert\Type("integer")
//     */
    /**
     * @Assert\AtLeastOneOf({
     *     @Assert\Type("integer"),
     *     @Assert\EqualTo(value=NotPassedClass::NOT_PASSED),
     *     })
     */
    public $filterValue;

    public function __construct($orderType, $orderBy, $pageSize, $page, $filterValue, $filterBy = "category")
    {
        $this->orderType = $orderType;
        $this->orderBy = $orderBy;
        $this->pageSize = $pageSize;
        $this->page = $page;
        $this->filterBy = $filterBy;
        $this->filterValue = $filterValue;
    }
}
