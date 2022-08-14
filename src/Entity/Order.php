<?php

namespace App\Entity;

use App\DTO\OrderItemOutputDTO;
use App\DTO\OrderOutputDTO;
use App\Repository\OrderRepository;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $customerEmail;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $shipmentDate;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private $orderTotal;

    #[ORM\ManyToOne(targetEntity: BillingType::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    /**
     * @ORM\JoinColumn(name="billing_type_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $billingType;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, orphanRemoval: true)]
//    #[ORM\JoinColumn(nullable: true)]
    private $orderItems;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(string $customerEmail): self
    {
        $this->customerEmail = $customerEmail;

        return $this;
    }

    public function getShipmentDate(): ?\DateTimeImmutable
    {
        return $this->shipmentDate;
    }

    public function setShipmentDate(\DateTimeImmutable $shipmentDate): self
    {
        $this->shipmentDate = $shipmentDate;

        return $this;
    }

    public function getOrderTotal(): ?float
    {
        return $this->orderTotal;
    }

    public function setOrderTotal(float $orderTotal): self
    {
        $this->orderTotal = $orderTotal;

        return $this;
    }

    public function getBillingType(): ?BillingType
    {
        return $this->billingType;
    }

    public function setBillingType(?BillingType $billingType): self
    {
        $this->billingType = $billingType;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): self
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems[] = $orderItem;
            $orderItem->setOrder($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): self
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getOrder() === $this) {
                $orderItem->setOrder(null);
            }
        }

        return $this;
    }

    public function orderGetSerializer(array $ordersRepo): array
    {
        $orderOutputDTOArr = [];
        foreach ($ordersRepo as $order) {
            $outputDto = new OrderOutputDTO(
                $order->getCustomerEmail(),
                $order->getShipmentDate()->format('U'),
                $order->getBillingType(),
                $order->orderItemOutputDataTransform(),
            );
            $orderOutputDTOArr[] = $outputDto;
        }

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer]);
        $data = $serializer->normalize($orderOutputDTOArr, null, ['groups' => 'group1']);

        return $data;
    }

    public function orderItemOutputDataTransform(): array
    {
        $orderItemArrayObjects = $this->getOrderItems();
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
