<?php

namespace App\Controller\Category;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Helper\ResponseJson;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use App\Entity\Category;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/category", name="categories", methods={"GET"})
 */
class GetController extends AbstractController
//"http://localhost:8082/category?order=ASC"
{
    public function __invoke(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        try {

            $order = $request->query->get('order');

            if (!in_array($order, ['ASC', 'DESC'])) {
                throw new NotFoundHttpException();
            }

            $category = $doctrine->getRepository(Category::class)->findBy([], ['sort' => $order]);

            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
            $normalizer = new ObjectNormalizer($classMetadataFactory);
            $serializer = new Serializer([$normalizer]);
            $data = $serializer->normalize($category, null, ['groups' => 'group1']);

            return (new ResponseJson())->response($data);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 422,
                'errors' => "Query parameter 'order' not equal to 'ASC' or 'DESC'",
            ];
            return (new ResponseJson())->response($data, 422);
        }

    }

}
