<?php

namespace App\Helper;

use App\DTO\ProductOutputDTO;

class GetSerializer
{
    public function outputDtoSerializer($product): ProductOutputDTO
    {
        return new ProductOutputDTO(
            $product->getId(),
            $product->getName(),
            $product->getDescription(),
            $product->getPrice(),
            $product->getCreatedAt()->format('U'),
            $product->getCategory(),
        );
    }

    public function arrOutputDtoSerializer($productsRepo):mixed
    {
        $outputDtoArr = [];
        foreach ($productsRepo as $product) {
            $outputDtoArr[] =  $this->outputDtoSerializer($product);
        }

        return $outputDtoArr;
    }
}