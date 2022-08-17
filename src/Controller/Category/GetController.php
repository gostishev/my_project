<?php

namespace App\Controller\Category;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Category;

/**
 * @Route("/category", name="categories", methods={"GET"})
 */
class GetController extends AbstractController
//"http://localhost:8082/category?order=ASC"
{
    public function __invoke(ManagerRegistry $doctrine, Request $request): mixed
    {
        try {
            $order = $request->query->has('order') ? $request->query->get('order') : 'ASC';

            if (!in_array($order, ['ASC', 'DESC'])) {
                throw new NotFoundHttpException();
            }

            return $doctrine->getRepository(Category::class)->findBy([], ['sort' => $order]);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 422,
                'errors' => "Query parameter 'order' not equal to 'ASC' or 'DESC'",
            ];
            return new JsonResponse($data, 422);
        }

    }

}
