<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\Column(type: 'date')]
    private $shipmentDate;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private $orderTotal;

    #[ORM\ManyToOne(targetEntity: BillingType::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    /**
     * @ORM\JoinColumn(name="billing_type_id", referencedColumnName="id", onDelete="CASCADE")
     */

    private $billingType;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class)]
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
}
