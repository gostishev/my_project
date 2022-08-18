<?php

namespace App\Controller\Product;

use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Category;
use App\Entity\Product;
use App\Exception\CustomErrorException;
use App\Helper\GetSerializer;
use App\Validator\Constraints\CategoryProductConstraint;
use App\Validator\Constraints\DescriptionProductConstraint;
use App\Validator\Constraints\NameProductConstraint;
use App\Validator\Constraints\PriceProductConstraint;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/product/{id}", name="product_edit", methods={"PATCH"})
 */
class EditController extends AbstractController
//"http://localhost:8082/category/id"
{
    public function __invoke(EntityManagerInterface $entityManager, ManagerRegistry $doctrine, Request $request, ValidatorInterface $validator, int $id): mixed
    {
        try {
            $product = $entityManager->getRepository(Product::class)->find($id);
            if (!$product) {
                throw new NotFoundHttpException(
                    "Product  not found for id: $id", null, 404
                );
            }

            $categoryIdNowThereIs = $product->getCategory()->getId();
            $result = [
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'category_id' => $categoryIdNowThereIs ,
            ];

            $violations = new ConstraintViolationList();
            $properties = [
                'name' => new NameProductConstraint(),
                'description' => new DescriptionProductConstraint(),
                'price' => new PriceProductConstraint(),
                'category_id' => new CategoryProductConstraint(),
            ];
            foreach ($properties as $key => $value) {
                if ($request->request->has($key)) {
                    $violation = $validator->validate($request->request->get($key), $value);
                    $violations->addAll($violation);
                    $result[$key] = $request->request->get($key);
                }
            }
            if (0 !== count($violations)) {
                throw  new CustomErrorException("", 422, null, $violations->getIterator());
            }

            $product->SetName($result["name"]);
            $product->setDescription($result["description"]);
            $product->setPrice($result["price"]);
            $product->setCreatedAt(new \DateTimeImmutable());
            $categoryIdRequest = $result["category_id"];

            if ($categoryIdNowThereIs === $categoryIdRequest) {
                $entityManager->flush();
                $productRepo[] = $product;
                $data = (new GetSerializer())->outputDtoSerializer($productRepo);
                return new JsonResponse($data, 200);
            }

            $repository = $doctrine->getRepository(Category::class);
            $category = $repository->find($categoryIdRequest);
            if (!isset($category)) {
                throw new NotFoundHttpException(
                    'Category not found for id :' . $categoryIdRequest, null, 404
                );
            }
            $product->setCategory($category);
            $entityManager->flush();
            $productRepo[] = $product;
            $data = (new GetSerializer())->outputDtoSerializer($productRepo);
            return new JsonResponse($data, 200);

        } catch
        (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => $e->getMessage(),
            ];
            return new JsonResponse($data, 404);

        } catch (CustomErrorException $e) {
            return new JsonResponse($e->getViolations(), $e->getCode());
        }
    }
}
