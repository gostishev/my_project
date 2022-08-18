<?php

namespace App\Controller\Product;

use Symfony\Component\Routing\Annotation\Route;
use App\DTO\ProductInputDTO;
use App\Entity\Category;
use App\Entity\Product;
use App\Exception\CustomErrorException;
use App\Helper\GetSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/product", name="product_add", methods={"POST"})
 */
class AddController extends AbstractController
//"http://localhost:8082/product"
{
    public function __invoke(EntityManagerInterface $entityManager, ManagerRegistry $doctrine, Request $request, ValidatorInterface $validator): JsonResponse
    {
        try {
            $inputDto = new ProductInputDTO($request->request->get('name'),
                $request->request->get('price'),
                $request->request->get('category_id'),
                $request->request->get('description'),
            );
            /** @var   ConstraintViolationList $violations */
            $violations = $validator->validate($inputDto);

            if (0 !== count($violations)) {
                throw  new CustomErrorException("", 422, null, $violations->getIterator());
            }

            $product = new Product();
            $categoryId = $request->request->get('category_id');
            $repository = $doctrine->getRepository(Category::class);
            $category = $repository->find($categoryId);

            if (!isset($category)) {
                throw new NotFoundHttpException(
                    'Not found category for id :' . $categoryId, null, 404
                );
            }

            $product->setName($request->request->get('name'));
            $product->setDescription($request->request->get('description'));
            $product->setPrice($request->request->get('price'));
            $product->setCreatedAt(new \DateTimeImmutable());

            $product->setCategory($category);

            $entityManager->persist($product);
            $entityManager->flush();

            $productRepo[] = $product;
            $data = (new GetSerializer())->outputDtoSerializer($productRepo);
            return new JsonResponse($data, 200);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Category not found for id: $categoryId",
            ];
            return new JsonResponse($data, 404);
        } catch (CustomErrorException $e) {
            return new JsonResponse($e->getViolations(), $e->getCode());
        }
    }
}