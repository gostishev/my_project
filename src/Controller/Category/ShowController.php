<?php

namespace App\Controller\Category;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Helper\ResponseJson;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use App\Entity\Category;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/category/{id}", name="category_show", methods={"GET"})
 */
class ShowController extends AbstractController
//"http://localhost:8082/category/id"
{
    public function __invoke(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        try {

            $category = $doctrine->getRepository(Category::class)->find($id);
            if (!$category) {
                throw new NotFoundHttpException(
                    "Category not found for id: $id", null, 404
                );
            }

            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
            $normalizer = new ObjectNormalizer($classMetadataFactory);
            $serializer = new Serializer([$normalizer]);
            $data = $serializer->normalize($category, null, ['groups' => 'group1']);

            return (new ResponseJson())->response($data);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Category not found for id: $id",
            ];
            return (new ResponseJson())->response($data, 404);
        }

    }

}
