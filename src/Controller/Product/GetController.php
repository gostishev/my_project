<?php

namespace App\Controller\Product;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use App\Exception\CustomErrorException;
use App\Helper\CountEntityProduct;
use App\Helper\GetSerializer;
use App\Helper\ValidatorPaginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Category;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/product", name="product_get", methods={"GET"})
 */
class GetController extends AbstractController
//  "http://localhost:8082/product?orderType=ASC&orderBy=id&filterBy=category&filterValue=26pageSize=5&page=1"
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request, ValidatorInterface $validator): JsonResponse
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

//                $entityCount = (new CountEntityProduct())->countByFilterValue($doctrine, $filterValue);
                /** @var ProductRepository $repoProduct */
                $repoProduct = $entityManager->getRepository(Product::class);
                $entityCount = $repoProduct->countByFilterValue($filterValue);
//                dump($entityCount);
                if ($entityCount < 1) {
                    throw new NotFoundHttpException ("Not found products with category_id :" . $filterValue);
                }
                $pageSize = $request->query->has('pageSize') ? $request->query->get('pageSize') : 5;
                $page = $request->query->has('page') ? $request->query->get('page') : 1;

                [$pageSize, $offset] = (new ValidatorPaginator())->offsetCalculateValidate($page, $pageSize, $entityCount, $validator);

                $productsRepo = $entityManager->getRepository(Product::class)->findBy([$filterBy => $filterValue], [$orderBy => $orderType], $pageSize, $offset);

                $data = (new GetSerializer())->outputDtoSerializer($productsRepo);

                return new JsonResponse($data);

            }

//            $entityCount = (new CountEntityProduct())->countAll($doctrine);
            /** @var ProductRepository $repoProduct */
            $repoProduct = $entityManager->getRepository(Product::class);
            $entityCount = $repoProduct->countEntity();
//            dump($entityCount);
            $pageSize = $request->query->has('pageSize') ? $request->query->get('pageSize') : 5;
            $page = $request->query->has('page') ? $request->query->get('page') : 1;

            [$pageSize, $offset] = (new ValidatorPaginator())->offsetCalculateValidate($page, $pageSize, $entityCount, $validator);

            $productsRepo = $entityManager->getRepository(Product::class)->findBy([], [$orderBy => $orderType], $pageSize, $offset);

            $data = (new GetSerializer())->outputDtoSerializer($productsRepo);

            return new JsonResponse($data);

        } catch (CustomErrorException $e) {

            return new JsonResponse($e->getViolations(), $e->getCode());

        } catch
        (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Query parameter not found ",
                'message' => $e->getMessage(),
            ];

            return new JsonResponse($data, 404);
        }
    }
}
