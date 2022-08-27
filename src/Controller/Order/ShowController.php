<?php

namespace App\Controller\Order;

use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/order/{id}", name="order_show", methods={"GET"})
 */
class ShowController extends AbstractController
//"http://localhost:8082/order/id"
{
    public function __invoke(EntityManagerInterface $entityManager, int $id): mixed
    {
        $order = $entityManager->getRepository(Order::class)->find($id);
        if (!$order) {
            throw new NotFoundHttpException(
                "order not found for id: $id", null, 404
            );
        }

        return $order->orderGetSerializer($order);
    }
}
