<?php

namespace App\Controller\Product;

use Symfony\Component\Routing\Annotation\Route;
use App\DTO\ProductOutputDTO;
use App\Helper\ValidatorInputDTO;
use App\DTO\ProductInputDTO;
use App\Entity\Category;
use App\Entity\Product;
use App\Helper\GetSerializer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/product/{id}", name="product_replace", methods={"PUT"})
 */
class ReplaceController extends AbstractController
//"http://localhost:8082/product/id"
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request, ValidatorInterface $validator, int $id): ProductOutputDTO
    {
        $product = $entityManager->getRepository(Product::class)->find($id);
        if (!$product) {
            throw new NotFoundHttpException(
                "product not found for id: $id", null, 404
            );
        }
        $inputDto = new ProductInputDTO(
            $request->request->get('name'),
            $request->request->get('price'),
            $request->request->get('category_id'),
            $description = $request->request->has('description') ? $request->request->get('description') : $product->getDescription(),
        );
        (new ValidatorInputDTO())->validateInput($validator, $inputDto);

        $product->setName($request->request->get('name'));
        $product->setDescription($description);
        $product->setPrice($request->request->get('price'));
        $product->setCreatedAt(new \DateTimeImmutable());

        $categoryIdRequest = $request->request->get('category_id');
        if ($categoryIdRequest === $product->getCategory()->getId()) {
            $entityManager->flush();

            return (new GetSerializer())->outputDtoSerializer($product);
        }
        $repository = $entityManager->getRepository(Category::class);
        $category = $repository->find($categoryIdRequest);
        if (!isset($category)) {
            throw new NotFoundHttpException(
                'Not found category for id :' . $categoryIdRequest, null, 404
            );
        }
        $product->setCategory($category);
        $entityManager->flush();

        return (new GetSerializer())->outputDtoSerializer($product);
    }
}
