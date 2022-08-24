<?php

namespace App\Controller;

use App\DTO\OrderInputDTO;
use App\DTO\OrderItemInputDTO;
use App\DTO\OrderItemOutputDTO;
use App\DTO\OrderOutputDTO;
use App\Entity\BillingType;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Exception\CustomErrorException;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Validator\Constraints\PageConstraint;
use App\Validator\Constraints\PageSizeConstraint;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class OrderController extends AbstractController

{
//    #[Route('/order', name: 'app_order')]
//    public function index(): Response
//    {
//        return $this->render('order/index.html.twig', [
//            'controller_name' => 'OrderController',
//        ]);
//    }

    protected function transformJsonBody(Request $request)
    {
        $data = json_decode($request->getContent(), true, 128, \JSON_THROW_ON_ERROR);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);
        return $request;
    }

    public function response($data, $status = 200, $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * @Route("/order", name="order_add", methods={"POST"})
     */
    public function orderAdd(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator): JsonResponse
    {

        try {
            $request = $this->transformJsonBody($request);

            $violations = new ConstraintViolationList();

            $billingTypeId = $request->request->get('billing_type');
            $requestOrderItemAll = $request->request->all("order_items");
            $orderInputDTO = new OrderInputDTO(
                $request->get('customer_email'),
                $request->get('shipment_date'),
                $billingTypeId,
                $requestOrderItemAll);
            $violationOrderInputDTO = $validator->validate($orderInputDTO);
            $violations->addAll($violationOrderInputDTO);

            foreach ($requestOrderItemAll as $item) {
                $orderItemInputDTO = new OrderItemInputDTO($item);
                $violationOrderItemInputDTO = $validator->validate($orderItemInputDTO);
                $violations->addAll($violationOrderItemInputDTO);
            }

            if (0 !== count($violations)) {
                throw  new CustomErrorException("", 422, null, $violations->getIterator());
            }


            $order = new Order();
            $order->setCustomerEmail($request->get('customer_email'));
            $shipmentDate = date("Y-m-d", $request->request->get('shipment_date'));
            $order->setShipmentDate(new \DateTimeImmutable($shipmentDate));

            $repoBillingType = $doctrine->getRepository(BillingType::class);
            $billingType = $repoBillingType->find($billingTypeId);
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
            $repoProducts = $doctrine->getRepository(Product::class);
            $productArrayObjects = $repoProducts->findBy(['id' => $orderItemIdArray], ["id" => 'ASC']);

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
                $entityManager = $doctrine->getManager();
                $entityManager->persist($orderItem);

                $sumOrderTotal = $sumOrderTotal + ($productArrayObjects [$i]->getPrice() * $orderItemQuantityArray[$i]);
            }
            $order->setOrderTotal($sumOrderTotal);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($order);
            $entityManager->flush();

            $ordersRepo[] = $order;
            $dataOrder = $order->orderGetSerializer($ordersRepo);
            return $this->response($dataOrder);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'message' => $e->getMessage(),
            ];
            return $this->response($data, 404);
        } catch (CustomErrorException $e) {
            return $this->response($e->getViolations(), $e->getCode());
        }

    }

    /**
     * @Route("/order/{id}", name="order_get_fo_id", methods={"GET"})
     */
    public function orderGetFoId(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        try {

            $order = $doctrine->getRepository(Order::class)->find($id);
            if (!$order) {
                throw new NotFoundHttpException(
                    "order not found for id: $id", null, 404
                );
            }

            $ordersRepo[] = $order;
            $dataOrder = $order->orderGetSerializer($ordersRepo);
            return $this->response($dataOrder);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Product not found for id: $id",
            ];
            return $this->response($data, 404);
        }
    }

    /**
     * @Route("/order", name="orders", methods={"GET"})
     */
//  "http://localhost:8082/order?page=1&pageSize=2"
    public function getOrders(EntityManagerInterface $entityManager, Request $request, ValidatorInterface $validator): JsonResponse
    {
        try {
            /** @var ProductRepository $repoProduct */
            $repoProduct = $entityManager->getRepository(Product::class);
            $entityCount = $repoProduct->countEntity();
            dump($entityCount);
            if ($entityCount < 1) {
                throw new NotFoundHttpException ("Not found orders ");
            }

            $pageSize = $request->query->has('pageSize') ? $request->query->get('pageSize') : 2;
            $page = $request->query->has('page') ? $request->query->get('page') : 1;

            [$pageSize, $offset] = $this->validatorPagination($page, $pageSize, $entityCount, $validator);

            $ordersRepo = $entityManager->getRepository(Order::class)->findBy([], ["id" => "ASC"], $pageSize, $offset);
            $dataOrder = (new Order())->orderGetSerializer($ordersRepo);
            return $this->response($dataOrder);

        } catch (CustomErrorException $e) {

            return $this->response($e->getViolations(), $e->getCode());
        } catch
        (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'message' => $e->getMessage(),
            ];
            return $this->response($data, 404);
        }
    }

    private function validatorPagination(int $page, int $pageSize, int $entityCount, ValidatorInterface $validator): array|JsonResponse
    {
        $pageSizeConstraint = new PageSizeConstraint();
        /** @var   ConstraintViolationList $violationsPageSize */
        $violationsPageSize = $validator->validate($pageSize, $pageSizeConstraint);
        if (0 !== count($violationsPageSize)) {
            throw new CustomErrorException("", 422, null, $violationsPageSize->getIterator());
        }

        $offset = ($page - 1) * $pageSize;
        $pageCount = ceil($entityCount / $pageSize);

        $pageConstraint = new PageConstraint([], $pageCount);
        /** @var   ConstraintViolationList $violationsPage */
        $violationsPage = $validator->validate($page, $pageConstraint);
        if (0 !== count($violationsPage)) {
            throw new CustomErrorException("", 422, null, $violationsPage->getIterator());
        }

        return [$pageSize, $offset];
    }

    /**
     * @Route("/order/{id}", name= "order_remove", methods={"DELETE"} )
     */
    public function removeOrder(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        try {
            $em = $doctrine->getManager();
            $order = $doctrine->getRepository(Order::class)->find($id);

            if (!$order) {
                throw new NotFoundHttpException(
                    "Order not found for id: $id", null, 404
                );
            }

            $em->remove($order);
            $em->flush();

            $orderRepo[] = $order;
            $data = $order->orderGetSerializer($orderRepo);
            return $this->response($data);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Order not found for id: $id",
            ];
            return $this->response($data, 404);
        }
    }


    /**
     * @Route("/order/{id}", name="order_edit", methods={"PUT"})
     */
    public function productPut(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator, int $id): JsonResponse
    {
        try {

            $entityManager = $doctrine->getManager();
            $order = $entityManager->getRepository(Order::class)->find($id);

            if (!$order) {
                throw new NotFoundHttpException(
                    "Order not found for id: $id", null, 404
                );
            }

            $request = $this->transformJsonBody($request);

            $violations = new ConstraintViolationList();

            $billingTypeId = $request->request->get('billing_type');
            $requestOrderItemAll = $request->request->all("order_items");
            $orderInputDTO = new OrderInputDTO(
                $request->get('customer_email'),
                $request->get('shipment_date'),
                $billingTypeId,
                $requestOrderItemAll);
            $violationOrderInputDTO = $validator->validate($orderInputDTO);
            $violations->addAll($violationOrderInputDTO);

            foreach ($requestOrderItemAll as $item) {
                $orderItemInputDTO = new OrderItemInputDTO($item);
                $violationOrderItemInputDTO = $validator->validate($orderItemInputDTO);
                $violations->addAll($violationOrderItemInputDTO);
            }

            if (0 !== count($violations)) {
                throw  new CustomErrorException("", 422, null, $violations->getIterator());
            }

            $order->setCustomerEmail($request->get('customer_email'));
            $shipmentDate = date("Y-m-d", $request->request->get('shipment_date'));
            $order->setShipmentDate(new \DateTimeImmutable($shipmentDate));

            $repoBillingType = $doctrine->getRepository(BillingType::class);
            $billingType = $repoBillingType->find($billingTypeId);
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
            $repoProducts = $doctrine->getRepository(Product::class);
            $productArrayObjects = $repoProducts->findBy(['id' => $orderItemIdArray], ["id" => 'ASC']);

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

            $deletePreviousOrderItems = $order->getorderItems();
            foreach ($deletePreviousOrderItems as $orderItem) {
                $entityManager = $doctrine->getManager();
                $entityManager->remove($orderItem);
                $entityManager->flush();
            }

            $sumOrderTotal = 0.00;
            for ($i = 0; $i < $countOrderItem; $i++) {
                $orderItem = new OrderItem();
                $orderItem->setProductName($productArrayObjects [$i]->getName());
                $orderItem->setProductId($productArrayObjects [$i]->getId());
                $orderItem->setProductPrice($productArrayObjects [$i]->getPrice());
                $orderItem->setProductQuantity($orderItemQuantityArray[$i]);
                $order->addOrderItem($orderItem);
                $entityManager = $doctrine->getManager();
                $entityManager->persist($orderItem);

                $sumOrderTotal = $sumOrderTotal + ($productArrayObjects [$i]->getPrice() * $orderItemQuantityArray[$i]);
            }
            $order->setOrderTotal($sumOrderTotal);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($order);
            $entityManager->flush();

            $ordersRepo[] = $order;
            $dataOrder = $order->orderGetSerializer($ordersRepo);
            return $this->response($dataOrder);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'message' => $e->getMessage(),
            ];
            return $this->response($data, 404);
        } catch (CustomErrorException $e) {
            return $this->response($e->getViolations(), $e->getCode());
        }

//            $product->setName($request->get('name'));
//            $product->setDescription($request->get('description'));
//            $product->setPrice($request->get('price'));
//            $product->setCreatedAt(new \DateTimeImmutable());
//
//            if ($request->request->get('category_id') === $product->getCategory()->getId()) {
//
//                $entityManager->flush();
////
//                $ordersRepo[] = $order;
//                $dataOrder = $order->orderGetSerializer($ordersRepo);
//                return $this->response($dataOrder);
//
//            }
//
//            $categoryIdRequest = $request->request->get('category_id');
//            $repository = $doctrine->getRepository(Category::class);
//            $category = $repository->find($categoryIdRequest);
//            if (!isset($category)) {
//                throw new NotFoundHttpException(
//                    'Not found category for id :' . $categoryIdRequest, null, 404
//                );
//            }
//            $product->setCategory($category);
//
//            $entityManager->flush();
////
////
//            $ordersRepo[] = $order;
//            $dataOrder = $order->orderGetSerializer($ordersRepo);
//            return $this->response($dataOrder);
//
//        } catch (NotFoundHttpException $e) {
//            $data = [
//                'status' => 404,
//                'errors' => "Category not found for id: $id",
//            ];
//            return $this->response($data, 404);
//        } catch (CustomErrorException $e) {
//            return $this->response($e->getViolations(), $e->getCode());
//        }
    }


}
