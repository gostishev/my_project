<?php

namespace App\Controller\Product;

use Symfony\Component\Routing\Annotation\Route;
use App\DTO\ProductGetInputDTO;
use App\Helper\ValidatorInputDTO;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;
use App\Helper\GetSerializer;
use App\Helper\ValidatorPaginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Helper\NotPassedClass;

/**
 * @Route("/product", name="product_get", methods={"GET"})
 */
class GetController extends AbstractController
//  "http://localhost:8082/product?orderType=ASC&orderBy=id&filterBy=category&filterValue=26&pageSize=5&page=1"
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request, ValidatorInterface $validator): mixed

    {
        $dto = new ProductGetInputDTO(
            $orderType = ($request->query->has('orderType')) ? $request->query->get('orderType') : "ASC",
            $orderBy = ($request->query->has('orderBy')) ? $request->query->get('orderBy') : "id",
            $pageSize = $request->query->has('pageSize') ? (int)$request->query->get('pageSize') : 5,
            $page = $request->query->has('page') ? (int)$request->query->get('page') : 1,
            $filterValue = $request->query->has('filterValue') ? (int)$request->query->get('filterValue') : NotPassedClass::NOT_PASSED,
            $filterBy = $request->query->get('filterBy'),
        );
        (new ValidatorInputDTO())->validateInput($validator, $dto);

        if ($request->query->has('filterBy')) {
            $validatorPaginator = new ValidatorPaginator();
            $entityCount = $validatorPaginator->validateFilterValue($entityManager, $filterValue);

            [$pageSize, $offset] = $validatorPaginator->offsetCalculateValidate($page, $pageSize, $entityCount, $validator);

            $productsRepo = $entityManager->getRepository(Product::class)->findBy([$filterBy => $filterValue], [$orderBy => $orderType], $pageSize, $offset);

            return (new GetSerializer())->arrOutputDtoSerializer($productsRepo);
        }

        /** @var ProductRepository $repoProduct */
        $repoProduct = $entityManager->getRepository(Product::class);
        $entityCount = $repoProduct->countEntity();

        [$pageSize, $offset] = (new ValidatorPaginator())->offsetCalculateValidate($page, $pageSize, $entityCount, $validator);

        $productsRepo = $entityManager->getRepository(Product::class)->findBy([], [$orderBy => $orderType], $pageSize, $offset);

        return (new GetSerializer())->arrOutputDtoSerializer($productsRepo);
    }
}
