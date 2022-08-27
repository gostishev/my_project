<?php

namespace App\Controller\Order;

use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Order;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/order/{id}", name= "order_remove", methods={"DELETE"})
 */
class RemoveController extends AbstractController
//"http://localhost:8082/order/id"
{
    public function __invoke(EntityManagerInterface $entityManager, int $id): mixed
    {
        $order = $entityManager->getRepository(Order::class)->find($id);

        if (!$order) {
            throw new NotFoundHttpException(
                "Order not found for id: $id", null, 404
            );
        }

        $entityManager->remove($order);
        $entityManager->flush();

        return $order->orderGetSerializer($order);
    }
}
