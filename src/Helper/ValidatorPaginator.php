<?php

namespace App\Helper;

use App\Entity\Category;
use App\Entity\Product;
use App\Exception\CustomErrorException;
use App\Repository\ProductRepository;
use App\Validator\Constraints\PageConstraint;
use App\Validator\Constraints\PageSizeConstraint;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Helper\NotPassedClass;

class ValidatorPaginator
{
    public function offsetCalculateValidate(int $page, int $pageSize, int $entityCount, ValidatorInterface $validator): array|JsonResponse
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

    public function validateFilterValue(EntityManagerInterface $entityManager, $filterValue): mixed
    {
        if ($filterValue === NotPassedClass::NOT_PASSED) {
            throw new NotFoundHttpException("Query parameter filterValue not found in URL");
        }

        $category = $entityManager->getRepository(Category::class)->find($filterValue);
        if (!$category) {
            throw new NotFoundHttpException(
                "Category not found for id: $filterValue", null, 404
            );
        }

        /** @var ProductRepository $repoProduct */
        $repoProduct = $entityManager->getRepository(Product::class);
        $entityCount = $repoProduct->countByFilterValue($filterValue);
        if ($entityCount < 1) {
            throw new NotFoundHttpException (
                "Not found products with category_id :" . $filterValue, null, 404
            );
        }

       return $entityCount;
    }
}
