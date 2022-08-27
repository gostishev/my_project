<?php

namespace App\Controller\Order;

use Symfony\Component\Routing\Annotation\Route;
use App\DTO\OrderInputDTO;
use App\DTO\OrderItemInputDTO;
use App\Entity\BillingType;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Exception\CustomErrorException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/order", name="order_add", methods={"POST"})
 */
class AddController extends AbstractController
//"http://localhost:8082/order"
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request, ValidatorInterface $validator): mixed
    {
        $violations = new ConstraintViolationList();

        $billingTypeId = $request->request->get('billing_type');
        $requestOrderItemAll = $request->request->all("order_items");

        $orderInputDTO = new OrderInputDTO(
            $request->get('customer_email'),
            $request->get('shipment_date'),
            $billingTypeId,
            $requestOrderItemAll);
        $violations->addAll($validator->validate($orderInputDTO));

        foreach ($requestOrderItemAll as $item) {
            $orderItemInputDTO = new OrderItemInputDTO($item);
            $violations->addAll($validator->validate($orderItemInputDTO));
        }
        if (0 !== count($violations)) {
            throw  new CustomErrorException("", 422, null, $violations->getIterator());
        }

        $order = new Order();
        $order->setCustomerEmail($request->get('customer_email'));
        $shipmentDate = date("Y-m-d ", $request->request->get('shipment_date'));
        $order->setShipmentDate(new \DateTimeImmutable($shipmentDate));

        $billingType = $entityManager->getRepository(BillingType::class)->find($billingTypeId);
        $order->setBillingType($billingType);

//  Build array [[4=>2], [10=>1], [26=>1]] from [["id"=>4,"quantity"=>2], ["id"=>10,"quantity"=>1], ["id"=>26,"quantity"=>1]]
        $orderItemIdQuantityArray = [];
        foreach ($requestOrderItemAll as $item) {
            $orderItemIdQuantityArray [$item["id"]] = $item["quantity"];
        }
//  Sort array ASC by key
        ksort($orderItemIdQuantityArray);
//  Build array [2,1,1] from [[4=>2], [10=>1], [26=>1]]
        $orderItemQuantityArray = [];
//  Build array [4,10,26] from [[4=>2], [10=>1], [26=>1]]
        $orderItemIdArray = [];
        foreach ($orderItemIdQuantityArray as $key => $item) {
            $orderItemQuantityArray[] = $item;
            $orderItemIdArray[] = $key;
        }
//  Count of array [[4=>2], [10=>1], [26=>1]]
        $countOrderItem = count($orderItemIdQuantityArray);
//  Build array of Products from productRepository, sorted ASC by "id"
        $productArrayObjects = $entityManager->getRepository(Product::class)->findBy(['id' => $orderItemIdArray], ["id" => 'ASC']);
//  Comparison count orderItems from request and count of Products from productRepository
        if ($countOrderItem !== count($productArrayObjects)) {
            $productIdArray = [];
            foreach ($productArrayObjects as $product) {
                $productIdArray[] = $product->getId();
            }
            $error = implode(',', array_diff($orderItemIdArray, $productIdArray));
            throw new NotFoundHttpException(
                'Not found product for id :' . $error, null, 404
            );
        }

        $sumOrderTotal = 0.00;
        for ($i = 0; $i < $countOrderItem; $i++) {
            $orderItem = new OrderItem();
            $orderItem->setProductName($productArrayObjects [$i]->getName());
            $orderItem->setProductId($productArrayObjects [$i]->getId());
            $orderItem->setProductPrice($productArrayObjects [$i]->getPrice());
            $orderItem->setProductQuantity($orderItemQuantityArray[$i]);
            $order->addOrderItem($orderItem);
            $entityManager->persist($orderItem);

            $sumOrderTotal = $sumOrderTotal + ($productArrayObjects [$i]->getPrice() * $orderItemQuantityArray[$i]);
        }
        $order->setOrderTotal($sumOrderTotal);

        $entityManager->persist($order);
        $entityManager->flush();

        return $order->orderGetSerializer($order);
    }
}

