<?php

namespace App\Helper;

use App\Entity\Product;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class CountEntityProduct
{
    public function countByFilterValue(ManagerRegistry $doctrine, $filterValue)
    {
        $entityManager = $doctrine->getManager();
        $repoEntity = $entityManager->getRepository(Product::class);
        return $repoEntity->createQueryBuilder('a')
            ->where("a.category = $filterValue ")
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countAll(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $repoEntity = $entityManager->getRepository(Product::class);
        return $repoEntity->createQueryBuilder('a')
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
