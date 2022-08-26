<?php

namespace App\Controller\Order;

use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Order;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Helper\ValidatorPaginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/order", name="order_get", methods={"GET"})
 */
class GetController extends AbstractController
//  "http://localhost:8082/order?pageSize=2&page=1"
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request, ValidatorInterface $validator): mixed
    {
            /** @var ProductRepository $repoProduct */
            $repoOrder = $entityManager->getRepository(Order::class);
            $entityCount = $repoOrder->countEntity();
            if ($entityCount < 1) {
                throw new NotFoundHttpException ("Not found orders ");
            }

            $pageSize = $request->query->has('pageSize') ? $request->query->get('pageSize') : 2;
            $page = $request->query->has('page') ? $request->query->get('page') : 1;

            [$pageSize, $offset] = (new ValidatorPaginator)->offsetCalculateValidate($page, $pageSize, $entityCount, $validator);

            $ordersRepo = $entityManager->getRepository(Order::class)->findBy([], ["id" => "ASC"], $pageSize, $offset);

            return (new Order())->arrOrderGetSerializer($ordersRepo);
    }
}
