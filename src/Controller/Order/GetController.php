<?php

namespace App\Controller\Order;

use App\Helper\ValidatorInputDTO;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Order;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Helper\ValidatorPaginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\DTO\OrderGetInputDTO;

/**
 * @Route("/order", name="order_get", methods={"GET"})
 */
class GetController extends AbstractController
//  "http://localhost:8082/order?pageSize=2&page=1"
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request, ValidatorInterface $validator): mixed
    {
        $dto = new OrderGetInputDTO(
            $page = $request->query->has('page') ? (int)$request->query->get('page') : 1,
            $pageSize = $request->query->has('pageSize') ? (int)$request->query->get('pageSize') : 2,
            $entityCount = (int)$entityManager->getRepository(Order::class)->countEntity(),
        );
        (new ValidatorInputDTO())->validateInput($validator, $dto);

        [$pageSize, $offset] = (new ValidatorPaginator)->offsetCalculateValidate($page, $pageSize, $entityCount, $validator);

        $ordersRepo = $entityManager->getRepository(Order::class)->findBy([], ["id" => "ASC"], $pageSize, $offset);

        return (new Order())->arrOrderGetSerializer($ordersRepo);
    }
}
