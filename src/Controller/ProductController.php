<?php

namespace App\Controller;

use App\DTO\CategoryInputDTO;
use App\DTO\ProductInputDTO;
use App\DTO\ProductOutputDTO;
use App\Entity\Category;
use App\Entity\Product;
use App\Exception\CustomErrorException;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Monolog\DateTimeImmutable;

//use mysql_xdevapi\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Doctrine\Common\Annotations\AnnotationReader;

use App\Validator\Constraints\PageConstraint;
use App\Validator\Constraints\PageSizeConstraint;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\DescriptionProductConstraint;
use App\Validator\Constraints\NameProductConstraint;
use App\Validator\Constraints\PriceProductConstraint;
use App\Validator\Constraints\CategoryProductConstraint;


class ProductController extends AbstractController
{

    protected function transformJsonBody(Request $request)
    {
        $data = json_decode($request->getContent(), true, 128, \JSON_THROW_ON_ERROR);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);
        return $request;
    }

    public function response($data, $status = 200, $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }

//    public function countEntityByFilterValue(ManagerRegistry $doctrine, $filterValue)
//    {
//        $em = $doctrine->getManager();
//        $repoEntity = $em->getRepository(Product::class);
//        return $repoEntity->createQueryBuilder('a')
//            // Filter by some parameter if you want
//            ->where("a.category = $filterValue ")
//            ->select('count(a.id)')
//            ->getQuery()
//            ->getSingleScalarResult();
//    }

//    public function countEntity(ManagerRegistry $doctrine)
//    {
//        $em = $doctrine->getManager();
//        $repoEntity = $em->getRepository(Product::class);
//        return $repoEntity->createQueryBuilder('a')
//            // Filter by some parameter if you want
////            ->where("a.category = $filterValue ")
//            ->select('count(a.id)')
//            ->getQuery()
//            ->getSingleScalarResult();
//    }


    /**
     * @Route("/product", name="product_show", methods={"POST"})
     */
    public function showProduct(ManagerRegistry $doctrine, Request $request, ValidatorInterface $validator): JsonResponse
    {
        try {
            $request = $this->transformJsonBody($request);
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
//            $category = $repository->findOneBy(
//                ['id' => $categoryId],
//            );

            if (!isset($category)) {
                throw new NotFoundHttpException(
                    'Not found category for id :' . $categoryId, null, 404
                );
            }

            $product->setName($request->request->get('name'));
            $product->setDescription($request->request->get('description'));
            $product->setPrice($request->request->get('price'));
//            $createdAtDateTime = date("Y-m-d H:i:s", $request->request->get('created_at'));
//            $product->setCreatedAt(new \DateTimeImmutable($createdAtDateTime));
            $product->setCreatedAt(new \DateTimeImmutable());

            $product->setCategory($category);


            $entityManager = $doctrine->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

//            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
//            $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer($classMetadataFactory)];
//            $serializer = new Serializer($normalizers);
//            $data = $serializer->normalize($product, null, ['groups' => 'group1']);
//
//            return $this->response($data);
            $productRepo[] = $product;
            $data = $this->productGetSerializer($productRepo);
            return $this->response($data);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Category not found for id: $categoryId",
            ];
            return $this->response($data, 404);
        } catch (CustomErrorException $e) {
            return $this->response($e->getViolations(), $e->getCode());
        }
    }

    /**
     * @Route("/product", name="products", methods={"GET"})
     */
    public function getProducts(ManagerRegistry $doctrine, Request $request, ManagerRegistry $registry, ValidatorInterface $validator): JsonResponse
    {
        try {

            $orderType = ($request->query->has('orderType')) ? $request->query->get('orderType') : "ASC";
            $orderBy = ($request->query->has('orderBy')) ? $request->query->get('orderBy') : "id";

            if (!in_array($orderBy, ['id', 'name', 'price', 'createdAt'])) {
                throw new NotFoundHttpException ("Query parameter orderBy not found in array [" . 'id,' . ' name,' . ' price,' . ' createdAt' . "]");
            }
            if (!in_array($orderType, ['ASC', 'DESC'])) {
                throw new NotFoundHttpException ("Query parameter orderType not found in array [" . 'ASC,' . ' DESC' . "]");
            }

            if ($request->query->has('filterBy')) {

                $filterBy = 'category';
                $filterValue = !($request->query->has('filterValue')) ? throw new NotFoundHttpException ("Query parameter filterValue not found in URL") : $request->query->get('filterValue');

                $entityCount = (new ProductRepository($registry))->countEntityByFilterValue($doctrine, $filterValue);
                if ($entityCount < 1) {
                    throw new NotFoundHttpException ("Not found products with category_id :" . $filterValue);
                }
                $pageSize = $request->query->has('pageSize') ? $request->query->get('pageSize') : 5;
                $page = $request->query->has('page') ? $request->query->get('page') : 1;

                [$pageSize, $offset] = $this->validatorPagination($page, $pageSize, $entityCount, $validator);

                $productsRepo = $doctrine->getRepository(Product::class)->findBy([$filterBy => $filterValue], [$orderBy => $orderType], $pageSize, $offset);

                $data = $this->productGetSerializer($productsRepo);

                return $this->response($data);

            }

            $entityCount = (new ProductRepository($registry))->countEntity($doctrine);
            $pageSize = $request->query->has('pageSize') ? $request->query->get('pageSize') : 5;
            $page = $request->query->has('page') ? $request->query->get('page') : 1;

            [$pageSize, $offset] = $this->validatorPagination($page, $pageSize, $entityCount, $validator);

            $productsRepo = $doctrine->getRepository(Product::class)->findBy([], [$orderBy => $orderType], $pageSize, $offset);

            $data = $this->productGetSerializer($productsRepo);

            return $this->response($data);

        } catch (CustomErrorException $e) {

            return $this->response($e->getViolations(), $e->getCode());
        } catch
        (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Query parameter not found ",
                'message' => $e->getMessage(),
            ];
            return $this->response($data, 404);

        }
    }

    private function productGetSerializer(array $productsRepo): array
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


    private function validatorPagination(int $page, int $pageSize, int $entityCount, ValidatorInterface $validator): array|JsonResponse
    {
        $pageSizeConstraint = new PageSizeConstraint();
        /** @var   ConstraintViolationList $violationsPageSize */
        $violationsPageSize = $validator->validate($pageSize, $pageSizeConstraint);

        if (0 !== count($violationsPageSize)) {
            throw  new CustomErrorException("", 422, null, $violationsPageSize->getIterator());
        }

        $offset = ($page - 1) * $pageSize;
        $pageCount = ceil($entityCount / $pageSize);

        $pageConstraint = new PageConstraint([], $pageCount);
        /** @var   ConstraintViolationList $violationsPage */
        $violationsPage = $validator->validate($page, $pageConstraint);

        if (0 !== count($violationsPage)) {
            throw  new CustomErrorException("", 422, null, $violationsPage->getIterator());
        }

        return [$pageSize, $offset];
    }


    /**
     * @Route("/product/{id}", name="product_edit", methods={"PUT"})
     */
    public function productPut(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator, int $id): JsonResponse
    {
        try {

            $entityManager = $doctrine->getManager();
            $product = $entityManager->getRepository(Product::class)->find($id);

            if (!$product) {
                throw new NotFoundHttpException(
                    "product not found for id: $id", null, 404
                );
            }

            $request = $this->transformJsonBody($request);

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
                $data = $this->productGetSerializer($productRepo);
                return $this->response($data);

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
            $data = $this->productGetSerializer($productRepo);
            return $this->response($data);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Category not found for id: $id",
            ];
            return $this->response($data, 404);
        } catch (CustomErrorException $e) {
            return $this->response($e->getViolations(), $e->getCode());
        }
    }

    /**
     * @Route("/product/{id}", name="product_get_fo_id", methods={"GET"})
     */
    public function showCategory(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        try {

            $product = $doctrine->getRepository(Product::class)->find($id);
            if (!$product) {
                throw new NotFoundHttpException(
                    "Product not found for id: $id", null, 404
                );
            }

            $productRepo[] = $product;
            $data = $this->productGetSerializer($productRepo);
            return $this->response($data);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Product not found for id: $id",
            ];
            return $this->response($data, 404);
        }
    }

    /**
     * @Route("/product/{id}", name= "product_remove", methods={"DELETE"} )
     */
    public function removeProduct(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        try {
            $em = $doctrine->getManager();
            $product = $doctrine->getRepository(Product::class)->find($id);

            if (!$product) {
                throw new NotFoundHttpException(
                    "Product not found for id: $id", null, 404
                );
            }

            $em->remove($product);
            $em->flush();

            $productRepo[] = $product;
            $data = $this->productGetSerializer($productRepo);
            return $this->response($data);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Category not found for id: $id",
            ];
            return $this->response($data, 404);
        }
    }

    /**
     * @Route("/product/{id}", name="product_parameter_edit", methods={"PATCH"})
     */
    public function productPatch(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator, int $id): JsonResponse
    {
        try {
            $entityManager = $doctrine->getManager();
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
            $request = $this->transformJsonBody($request);

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
                $data = $this->productGetSerializer($productRepo);
                return $this->response($data);

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
            $data = $this->productGetSerializer($productRepo);
            return $this->response($data);

        } catch
        (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => $e->getMessage(),
            ];
            return $this->response($data, 404);

        } catch (CustomErrorException $e) {
            return $this->response($e->getViolations(), $e->getCode());
        }

    }
}

