<?php

namespace App\Controller\Product;

use Symfony\Component\Routing\Annotation\Route;
use App\DTO\ProductOutputDTO;
use App\Entity\Product;
use App\Helper\GetSerializer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/product/{id}", name= "product_remove", methods={"DELETE"})
 */
class RemoveController extends AbstractController
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
            $entityManager->remove($product);
            $entityManager->flush();

        return (new GetSerializer())->outputDtoSerializer($product);
    }
}
