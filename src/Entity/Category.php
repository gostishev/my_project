<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    /**
     * @Groups("group1")
     */
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    /**
     * @Groups("group1")
     */
    private $name;

    #[ORM\Column(type: 'integer')]
    /**
     * @Groups("group2")
     */
    private $sort;

//    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Product::class,  fetch: "EAGER")]
//    private $products;

//    public function __construct()
//    {
//        $this->products = new ArrayCollection();
//    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

//    /**
//     * @return Collection<int, Product>
//     */
//    public function getProducts(): Collection
//    {
//        return $this->products;
//    }
//
//    public function addProduct(Product $product): self
//    {
//        if (!$this->products->contains($product)) {
//            $this->products[] = $product;
//            $product->setCategory($this);
//        }
//
//        return $this;
//    }
//
//    public function removeProduct(Product $product): self
//    {
//        if ($this->products->removeElement($product)) {
//            // set the owning side to null (unless already changed)
//            if ($product->getCategory() === $this) {
//                $product->setCategory(null);
//            }
//        }
//
//        return $this;
//    }

}
