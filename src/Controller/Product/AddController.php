<?php

namespace App\Controller\Product;

use Symfony\Component\Routing\Annotation\Route;
use App\DTO\ProductInputDTO;
use App\Entity\Category;
use App\Entity\Product;
use App\Helper\GetSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Helper\ValidatorInputDTO;
use App\DTO\ProductOutputDTO;

/**
 * @Route("/product", name="product_add", methods={"POST"})
 */
class AddController extends AbstractController
//"http://localhost:8082/product"
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request, ValidatorInterface $validator): ProductOutputDTO
    {
        $inputDto = new ProductInputDTO(
            $request->request->get('name'),
            $request->request->get('price'),
            $request->request->get('category_id'),
            $description = $request->request->has('description') ? $request->request->get('description') : "",
        );
        (new ValidatorInputDTO())->validateInput($validator, $inputDto);

        $product = new Product();
        $categoryId = $request->request->get('category_id');
        $repository = $entityManager->getRepository(Category::class);
        $category = $repository->find($categoryId);
        if (!isset($category)) {
            throw new NotFoundHttpException(
                'Not found category for id :' . $categoryId, null, 404
            );
        }

        $product->setName($request->request->get('name'));
        $product->setDescription($description);
        $product->setPrice($request->request->get('price'));
        $product->setCreatedAt(new \DateTimeImmutable());
        $product->setCategory($category);

        $entityManager->persist($product);
        $entityManager->flush();

        return (new GetSerializer())->outputDtoSerializer($product);
    }
}