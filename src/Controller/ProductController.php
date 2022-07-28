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
                $request->request->get('description'),
                $request->request->get('price'),
                $request->request->get('created_at'),
                $request->request->get('category_id'),
            );
            /** @var   ConstraintViolationList $violations */
            $violations = $validator->validate($inputDto);

            if (0 !== count($violations)) {
                throw  new CustomErrorException("", 422, null, $violations->getIterator());
            }

            $product = new Product();
            $categoryId = $request->request->get('category_id');
            $repository = $doctrine->getRepository(Category::class);
            $category = $repository->findOneBy(
                ['id' => $categoryId],
            );

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

            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
            $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer($classMetadataFactory)];
            $serializer = new Serializer($normalizers);
            $data = $serializer->normalize($product, null, ['groups' => 'group1']);

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
    public function getProducts(ManagerRegistry $doctrine, Request $request, ManagerRegistry $registry): JsonResponse
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
                    throw new NotFoundHttpException ("Not found product with category_id :" . $filterValue);
                }

                $pageSize = $request->query->has('pageSize') ? $request->query->get('pageSize') : 5;
                if (($pageSize < 1) || ($pageSize > 100)) {
                    throw new \Exception("Enter query parameter pageSize between 1 and 100 in URL");
                }
                $page = $request->query->has('page') ? $request->query->get('page') : 1;
                $offset = ($page - 1) * $pageSize;
                $pageCount = ceil($entityCount / $pageSize);
                if (($page > $pageCount)) {
                    throw new \Exception(
                        'Query parameter page >' . $pageCount
                    );
                }

                $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
                $productsRepo = $doctrine->getRepository(Product::class)->findBy([$filterBy => $filterValue], [$orderBy => $orderType], $pageSize, $offset);
                $outputDtoArr = [];
                foreach ($productsRepo as $product) {
                    $createdAtTimestamp = $product->getCreatedAt()->format('U');
                    $outputDto = new ProductOutputDTO(
                        $product->getId(),
                        $product->getName(),
                        $product->getDescription(),
                        $product->getPrice(),
                        $createdAtTimestamp,
                        $product->getCategory(),
                    );
                    $outputDtoArr[] = $outputDto;
                }
                $normalizer = new ObjectNormalizer($classMetadataFactory);
                $serializer = new Serializer([$normalizer]);
                $data = $serializer->normalize($outputDtoArr, null, ['groups' => 'group1']);
                return $this->response($data);
            }

            $pageSize = $request->query->has('pageSize') ? $request->query->get('pageSize') : 5;
            if (($pageSize < 1) || ($pageSize > 100)) {
                throw new \Exception("Enter query parameter pageSize between 1 and 100 in URL");
            }
            $page = $request->query->has('page') ? $request->query->get('page') : 1;
            $offset = ($page - 1) * $pageSize;
            $entityCount = (new ProductRepository($registry))->countEntity($doctrine);
            $pageCount = ceil($entityCount / $pageSize);
            if ($page > $pageCount) {
                throw new \Exception(
                    'Query parameter page >' . $pageCount
                );
            }

            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
            $productsRepo = $doctrine->getRepository(Product::class)->findBy([], [$orderBy => $orderType], $pageSize, $offset);;
            $outputDtoArr = [];
            foreach ($productsRepo as $product) {
                $createdAtTimestamp = $product->getCreatedAt()->format('U');
                $outputDto = new ProductOutputDTO(
                    $product->getId(),
                    $product->getName(),
                    $product->getDescription(),
                    $product->getPrice(),
                    $createdAtTimestamp,
                    $product->getCategory(),
                );
                $outputDtoArr[] = $outputDto;
            }
            $normalizer = new ObjectNormalizer($classMetadataFactory);
            $serializer = new Serializer([$normalizer]);
            $data = $serializer->normalize($outputDtoArr, null, ['groups' => 'group1']);

            return $this->response($data);
        } catch
        (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Query parameter not found ",
                'message' => $e->getMessage(),
            ];
            return $this->response($data, 404);

        } catch (\Exception $e) {
            $data = [
                'status' => 422,
                'errors' => "Query parameter not valid",
                'message' => $e->getMessage(),
            ];
            return $this->response($data, 422);
        }
    }


//    /**
//     * @Route("/product", name="product")
//     */
//    public
//    function productIndex(ManagerRegistry $doctrine, $category_id = 30): Response
//    {
////        $category = new Category();
////        $category->setName('Computer Peripherals');
////        $category->setSort(333);
//        $category = $doctrine->getRepository(Category::class)->find($category_id);
//
//        $product = new Product();
//        $product->setName('Keyboards');
//        $product->setPrice(12345.99);
//        $product->setDescription('not Ergonomic not stylish!');
//        $product->setCreatedAt(new \DateTimeImmutable('2022-07-20 12:28:10'));
//
//        // relates this product to the category
////        $category->addProduct($product);
//        $product->setCategory($category);
//
//        $entityManager = $doctrine->getManager();
////        $entityManager->persist($category);
//        $entityManager->persist($product);
//        $entityManager->flush();
//
//        return new Response(
//            'Saved new product with id: ' . $product->getId()
//            . ' and new category with id: ' . $category->getId()
//        );
//    }


//    /**
//     * @Route("/product/{id}", name="product_show", methods={"GET"})
//     */
//    public function productShow(ManagerRegistry $doctrine, int $id): JsonResponse
//    {
//
//        $category = $doctrine->getRepository(Category::class)->find(30);
//        $products = $category->getProducts();
////        echo "<pre>";
////        dump($products);
//
//        $productArr = [];
//        foreach ($products as $key => $product) {
//            $productArr[] = (array)$product;
//        }
////        $keysProductArr = array_keys($productArr);
////        $productStr = json_encode($productArr[$keysProductArr[0]]);
//
//        return $this->response($productArr[0]);
//
//    }


}
