<?php

namespace App\Controller\Category;

use Symfony\Component\Routing\Annotation\Route;
use App\DTO\CategoryInputDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Category;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Exception\CustomErrorException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Helper\NotPassedClass;

/**
 * @Route("/category/{id}", name="category_edit", methods={"PATCH"})
 */
class EditController extends AbstractController
//"http://localhost:8082/category/id"
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request, ValidatorInterface $validator, int $id): mixed
    {
        $category = $entityManager->getRepository(Category::class)->find($id);
        if (!$category) {
            throw new NotFoundHttpException(
                "Category not found for id: $id", null, 404
            );
        }

        if ($request->request->has('name')) {
            $name = $request->request->get('name');
        } else {
            $name = NotPassedClass::NOT_PASSED;
        }
        if ($request->request->has('sort')) {
            $sort = $request->request->get('sort');
        } else {
            $sort = $category->getSort();
        }

        $dto = new CategoryInputDTO($name, $sort);
        /** @var   ConstraintViolationList $violations */
        $violations = $validator->validate($dto);
        if (0 !== count($violations)) {
            throw new CustomErrorException("", 422, null, $violations->getIterator());
        }

        if ($name === NotPassedClass::NOT_PASSED) {
            $name = $category->getName();
        }

        $category->setSort($sort);
        $category->setName($name);
        $entityManager->flush();

        return $category;
    }
}
