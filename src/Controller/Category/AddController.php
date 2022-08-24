<?php

namespace App\Controller\Category;

use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Category;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\DTO\CategoryInputDTO;
use App\Helper\ValidatorInputDTO;

/**
 * @Route("/category", name="category_add", methods={"POST"})
 */
class AddController extends AbstractController
//"http://localhost:8082/category"
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request, ValidatorInterface $validator): Category
    {
        $dto = new CategoryInputDTO($request->request->get('name'), $request->request->get('sort'));
        (new ValidatorInputDTO())->validateInput($validator, $dto);

        $category = new Category();
        $category->setName($request->request->get('name'));
        $category->setSort($request->request->get('sort'));
        $entityManager->persist($category);
        $entityManager->flush();

        return $category;
    }
}
