<?php

namespace App\Controller\Product;

use Symfony\Component\Routing\Annotation\Route;
use App\DTO\ProductOutputDTO;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;
use App\Helper\GetSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/product/{id}", name="product_show", methods={"GET"})
 */
class ShowController extends AbstractController
//"http://localhost:8082/product/id"
{
    public function __invoke(EntityManagerInterface $entityManager, int $id): ProductOutputDTO
    {
            $product = $entityManager->getRepository(Product::class)->find($id);
            if (!$product) {
                throw new NotFoundHttpException(
                    "Product not found for id: $id", null, 404
                );
            }

        return (new GetSerializer())->outputDtoSerializer($product);
    }
}

