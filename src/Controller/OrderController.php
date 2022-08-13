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
use Doctrine\Common\Annotations\AnnotationReader;
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
            $orderItemsQuantityArray = [];
//  Build array [4,10,26] from [[4=>2], [10=>1], [26=>1]]
            $orderItemsIdArray = [];
            foreach ($orderItemIdQuantityArray as $key => $item) {
                $orderItemQuantityArray[] = $item;
                $orderItemIdArray[] = $key;
            }

//  Count in array [[4=>2], [10=>1], [26=>1]]
            $countOrderItem = count($orderItemIdQuantityArray);

//  Build array of Products from productRepository, sorted ASC by "id"
            $repoProducts = $doctrine->getRepository(Product::class);
            $productArrayObjects = $repoProducts->findBy(['id' => $orderItemIdArray], ["id" => 'ASC']);

//  Comparison count orderItems from request and count of Products from productRepository
            if (count($orderItemIdArray) !== count( $productArrayObjects )) {
                $productIdArray = [];
                foreach ( $productArrayObjects  as $product) {
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
                $orderItem->setProductName( $productArrayObjects [$i]->getName());
                $orderItem->setProductId( $productArrayObjects [$i]->getId());
                $orderItem->setProductPrice( $productArrayObjects [$i]->getPrice());
                $orderItem->setProductQuantity($orderItemQuantityArray[$i]);
                $order->addOrderItem($orderItem);
                $entityManager = $doctrine->getManager();
                $entityManager->persist($orderItem);

                $sumOrderTotal = $sumOrderTotal + ( $productArrayObjects [$i]->getPrice() * $orderItemQuantityArray[$i]);
            }
            $order->setOrderTotal($sumOrderTotal);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($order);
            $entityManager->flush();

            $ordersRepo[] = $order;
            $orderItemArrayObjects = $order->getOrderItems();
            $orderItemOutputDTOArray = $this->orderItemOutputDataTransform($orderItemArrayObjects);
            $dataOrder = $this->orderGetSerializer($ordersRepo, $orderItemOutputDTOArray );
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

    private function orderGetSerializer(array $ordersRepo, $countOrderItems): array
    {
        $orderOutputDTOArr = [];
        foreach ($ordersRepo as $order) {
            $outputDto = new OrderOutputDTO(
                $order->getCustomerEmail(),
                $order->getShipmentDate()->format('U'),
                $order->getBillingType(),
                $countOrderItems,
            );
            $orderOutputDTOArr[] = $outputDto;
        }
//        dump($orderOutputDTOArr);
//        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
//        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer($classMetadataFactory)];
//        $serializer = new Serializer($normalizers);
//        $data = $serializer->normalize($orderOutputDTOArr, null, ['groups' => 'group1']);
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer]);
        $data = $serializer->normalize($orderOutputDTOArr, null, ['groups' => 'group1']);

        return $data;
    }

    private function orderItemOutputDataTransform($orderItemArrayObjects): array
    {
        $orderItemOutputDTOArray = [];
        /** @var   OrderItem $orderItem */
        foreach ($orderItemArrayObjects as $orderItem) {
            $orderItemDTO = new OrderItemOutputDTO(
                $orderItem->getProductName(),
                $orderItem->getProductPrice(),
                $orderItem->getProductQuantity(),
            );
            $orderItemOutputDTOArray[] = $orderItemDTO;
        }
        return $orderItemOutputDTOArray;
    }


}
