<?php

namespace App\Controller\Category;

use Symfony\Component\Routing\Annotation\Route;
use App\Helper\ValidatorInputDTO;
use App\DTO\CategoryInputDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Category;
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
        (new ValidatorInputDTO())->validateInput($validator, $dto);

        if ($name === NotPassedClass::NOT_PASSED) {
            $name = $category->getName();
        }

        $category->setSort($sort);
        $category->setName($name);
        $entityManager->flush();

        return $category;
    }
}
