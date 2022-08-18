<?php

namespace App\Controller\Product;

use Symfony\Component\Routing\Annotation\Route; 
use App\Entity\Product;
use App\Helper\GetSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/product/{id}", name="product_show", methods={"GET"})
 */
class ShowController extends AbstractController
//"http://localhost:8082/category/id"
{
    public function __invoke(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        try {
            $product = $doctrine->getRepository(Product::class)->find($id);
            if (!$product) {
                throw new NotFoundHttpException(
                    "Product not found for id: $id", null, 404
                );
            }
            $productRepo[] = $product;
            $data = (new GetSerializer())->outputDtoSerializer($productRepo);
            return new JsonResponse($data);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Product not found for id: $id",
            ];
            return new JsonResponse($data, 404);
        }
    }
}

