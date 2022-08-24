<?php

namespace App\Controller\Product;

use Symfony\Component\Routing\Annotation\Route;
use App\DTO\ProductInputDTO;
use App\Helper\ValidatorInputDTO;
use App\Entity\Category;
use App\Entity\Product;
use App\Helper\GetSerializer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\DTO\ProductOutputDTO;
/**
 * @Route("/product/{id}", name="product_edit", methods={"PATCH"})
 */
class EditController extends AbstractController
//"http://localhost:8082/category/id"
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request, ValidatorInterface $validator, int $id): ProductOutputDTO
    {
        $product = $entityManager->getRepository(Product::class)->find($id);
        if (!$product) {
            throw new NotFoundHttpException(
                "Product  not found for id: $id", null, 404
            );
        }
        $categoryIdRepo = $product->getCategory()->getId();

        $inputDto = new ProductInputDTO(
            $name = $request->request->has('name') ? $request->request->get('name') : $product->getName(),
            $price = $request->request->has('price') ? $request->request->get('price') : $product->getPrice(),
            $categoryId = $request->request->has('category_id') ? $request->request->get('category_id') : $categoryIdRepo,
            $description = $request->request->has('description') ? $request->request->get('description') : $product->getDescription(),
        );
        (new ValidatorInputDTO())->validateInput($validator, $inputDto);

        $product->SetName($name);
        $product->setPrice($price);
        $product->setCreatedAt(new \DateTimeImmutable());
        $product->setDescription($description);
        $categoryIdRequest = $categoryId;


        if ($categoryIdRepo === $categoryIdRequest) {
            $entityManager->flush();

            return (new GetSerializer())->outputDtoSerializer($product);
        }

        $category = $entityManager->getRepository(Category::class)->find($categoryIdRequest);
        if (!isset($category)) {
            throw new NotFoundHttpException(
                'Category not found for id :' . $categoryIdRequest, null, 404
            );
        }
        $product->setCategory($category);
        $entityManager->flush();

        return (new GetSerializer())->outputDtoSerializer($product);
    }
}
