<?php

namespace App\Controller\Category;

use Symfony\Component\Routing\Annotation\Route;
use App\Helper\ValidatorInputDTO;
use App\DTO\CategoryOrderInputDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Category;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/category", name="category_get", methods={"GET"})
 */
class GetController extends AbstractController
//"http://localhost:8082/category?order=ASC"
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request, ValidatorInterface $validator): JsonResponse|array
    {
        $order = $request->query->has('order') ? $request->query->get('order') : 'ASC';
        $dto = new CategoryOrderInputDTO($request->query->get('order'));
        (new ValidatorInputDTO())->validateInput($validator, $dto);

        return $entityManager->getRepository(Category::class)->findBy([], ['sort' => $order]);
    }
}
