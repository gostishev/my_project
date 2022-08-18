<?php

namespace App\Controller\Product;

use App\Entity\Product;
use App\Helper\GetSerializer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/product/{id}", name= "product_remove", methods={"DELETE"})
 */
class RemoveController extends AbstractController
//"http://localhost:8082/product/id"
{
    public function __invoke(EntityManagerInterface $entityManager, ManagerRegistry $doctrine, int $id): JsonResponse
    {
        try {
            $product = $doctrine->getRepository(Product::class)->find($id);
            if (!$product) {
                throw new NotFoundHttpException(
                    "Product not found for id: $id", null, 404
                );
            }

            $entityManager->remove($product);
            $entityManager->flush();

            $productRepo[] = $product;
            $data = (new GetSerializer())->outputDtoSerializer($productRepo);
            return new JsonResponse($data, 200);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Category not found for id: $id",
            ];
            return new JsonResponse($data, 404);
        }
    }
}
