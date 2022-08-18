<?php

namespace App\Controller\Product;

use Symfony\Component\Routing\Annotation\Route;
use App\DTO\ProductInputDTO;
use App\Entity\Category;
use App\Entity\Product;
use App\Exception\CustomErrorException;
use App\Helper\GetSerializer;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/product/{id}", name="product_replace", methods={"PUT"})
 */
class ReplaceController extends AbstractController
//"http://localhost:8082/product/id"
{
    public function __invoke (EntityManagerInterface $entityManager, ManagerRegistry $doctrine, Request $request, ValidatorInterface $validator, int $id): JsonResponse
    {
        try {
            $product = $entityManager->getRepository(Product::class)->find($id);
            if (!$product) {
                throw new NotFoundHttpException(
                    "product not found for id: $id", null, 404
                );
            }
            $inputDto = new ProductInputDTO(
                $request->request->get('name'),
                $request->request->get('price'),
                $request->request->get('category_id'),
                $request->request->get('description')
            );
            /** @var   ConstraintViolationList $violations */
            $violations = $validator->validate($inputDto);

            if (0 !== count($violations)) {
                throw  new CustomErrorException("", 422, null, $violations->getIterator());
            }

            $product->setName($request->get('name'));
            $product->setDescription($request->get('description'));
            $product->setPrice($request->get('price'));
            $product->setCreatedAt(new \DateTimeImmutable());

            if ($request->request->get('category_id') === $product->getCategory()->getId()) {
                $entityManager->flush();
                $productRepo[] = $product;
                $data = (new GetSerializer())->outputDtoSerializer($productRepo);
                return new JsonResponse($data);
            }

            $categoryIdRequest = $request->request->get('category_id');
            $repository = $doctrine->getRepository(Category::class);
            $category = $repository->find($categoryIdRequest);
            if (!isset($category)) {
                throw new NotFoundHttpException(
                    'Not found category for id :' . $categoryIdRequest, null, 404
                );
            }
            $product->setCategory($category);
            $entityManager->flush();
            $productRepo[] = $product;
            $data = (new GetSerializer())->outputDtoSerializer($productRepo);
            return new JsonResponse($data);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Category not found for id: $id",
            ];
            return new JsonResponse($data, 404);
        } catch (CustomErrorException $e) {
            return new JsonResponse($e->getViolations(), $e->getCode());
        }
    }
}
