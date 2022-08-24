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
//    private Serializer $serialazer;
//    public function outputDtoSerializer(array $productsRepo): mixed
      public function outputDtoSerializer( $product): ProductOutputDTO
    {
//        $outputDtoArr = [];
//        foreach ($productsRepo as $product) {
            $outputDto = new ProductOutputDTO(
                $product->getId(),
                $product->getName(),
                $product->getDescription(),
                $product->getPrice(),
                $product->getCreatedAt()->format('U'),
                $product->getCategory(),
            );
//            dump($outputDto);
            return $outputDto;
//            $outputDtoArr[] = $outputDto;
//        }
//        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
//        $normalizer = new ObjectNormalizer($classMetadataFactory);
//        $serializer = new Serializer([$normalizer]);
//
//        $data = $serializer->normalize($outputDtoArr, null, ['groups' => 'group1']);
//
//        return $data;
//        dump($outputDtoArr[]);
//        return $outputDtoArr[];

    }
}
