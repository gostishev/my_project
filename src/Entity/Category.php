<?php

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category implements \JsonSerializable
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
     * @Groups({"group1","group2"})
     */
    private $name;

    #[ORM\Column(type: 'integer')]
    /**
     * @Groups("group2")
     */
    private $sort;

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

    public function jsonSerialize()
    {
        return [
            "name" => $this->getName(),
            "sort" => $this->getSort()
        ];
    }
}
