<?php

namespace App\Controller\Category;

use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Category;

/**
 * @Route("/category/{id}", name= "category_remove", methods={"DELETE"})
 */
class RemoveController extends AbstractController
//"http://localhost:8082/category/id"
{
    public function __invoke(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $category = $entityManager->getRepository(Category::class)->find($id);
        if (!$category) {
            throw new NotFoundHttpException(
                "Category not found for id: $id", null, 404
            );
        }
            $entityManager->remove($category);
            $entityManager->flush();

        return new JsonResponse([], 200);
    }
}
