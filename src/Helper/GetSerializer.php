<?php

namespace App\Helper;

use App\DTO\ProductOutputDTO;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class GetSerializer
{
    public function outputDtoSerializer(array $productsRepo):array
    {
        $outputDtoArr = [];
        foreach ($productsRepo as $product) {
            $outputDto = new ProductOutputDTO(
                $product->getId(),
                $product->getName(),
                $product->getDescription(),
                $product->getPrice(),
                $product->getCreatedAt()->format('U'),
                $product->getCategory(),
            );
//            dump($product->getCategory());
            $outputDtoArr[] = $outputDto;
        }
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer]);
        $data = $serializer->normalize($outputDtoArr, null, ['groups' => 'group1']);
//        dump($data);

        return $data;

    }
}
