<?php

namespace App\Helper;

use App\Exception\CustomErrorException;
use App\Validator\Constraints\PageConstraint;
use App\Validator\Constraints\PageSizeConstraint;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
}
