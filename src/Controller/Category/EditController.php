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
use App\Validator\Constraints\SortConstraint;
use App\Validator\Constraints\NameConstraint;

/**
 * @Route("/category/{id}", name="category_edit", methods={"PATCH"})
 */
class EditController extends AbstractController
//"http://localhost:8082/category/id"
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request, ValidatorInterface $validator, int $id): mixed
    {
        try {
            $category = $entityManager->getRepository(Category::class)->find($id);
            if (!$category) {
                throw new NotFoundHttpException(
                    "Category not found for id: $id", null, 404
                );
            }
            $name = $category->getName();
            $sort = $category->getSort();

            $violations = new ConstraintViolationList();

            if ($request->request->has('name')) {
                $nameConstraint = new NameConstraint;
                /** @var   ConstraintViolationList $violationsName */
                $violationsName = $validator->validate($request->request->get('name'), $nameConstraint);
                $violations->addAll($violationsName);
                $name = $request->request->get('name');
            }
            if ($request->request->has('sort')) {
                $sortConstraint = new SortConstraint;
                /** @var   ConstraintViolationList $violationsSort */
                $violationsSort = $validator->validate($request->request->get('sort'), $sortConstraint);
                $violations->addAll($violationsSort);
                $sort = $request->request->get('sort');
            }

            if (0 !== count($violations)) {
                throw  new CustomErrorException("", 422, null, $violations->getIterator());
            }

            $category->setSort($sort);
            $category->setName($name);
            $entityManager->flush();

            return $category;

        } catch
        (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Category not found for id: $id",
            ];
            return new JsonResponse($data, 404);

        } catch (CustomErrorException $e) {
            return new JsonResponse($e->getViolations(), $e->getCode());
        }
    }
}
