<?php

namespace App\Controller\Category;

use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Category;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Exception\CustomErrorException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\DTO\CategoryInputDTO;

/**
 * @Route("/category/{id}", name="category_replace", methods={"PUT"})
 */
class ReplaceController extends AbstractController
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

            $dto = new CategoryInputDTO($request->request->get('name'), $request->request->get('sort'));
            /** @var   ConstraintViolationList $violations */
            $violations = $validator->validate($dto);
            if (0 !== count($violations)) {
                throw new CustomErrorException("", 422, null, $violations->getIterator());
            }

            $category->setName($request->request->get('name'));
            $category->setSort($request->request->get('sort'));
            $entityManager->flush();

            return $category;
    }
}
