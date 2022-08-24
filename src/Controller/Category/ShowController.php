<?php

namespace App\Controller\Category;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Category;

/**
 * @Route("/category/{id}", name="category_show", methods={"GET"})
 */
class ShowController extends AbstractController
//"http://localhost:8082/category/id"
{
    public function __invoke(ManagerRegistry $doctrine, int $id): mixed
    {
            $category = $doctrine->getRepository(Category::class)->find($id);
            if (!$category) {
                throw new NotFoundHttpException(
                    "Category not found for id: $id", null, 404
                );
            }

            return $category;
    }
}
