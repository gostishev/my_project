<?php

namespace App\Controller\Category;


use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Helper\TransformJsonBody;
use App\Helper\ResponseJson;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use App\Entity\Category;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Exception\CustomErrorException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\DTO\CategoryInputDTO;

/**
 * @Route("/category/{id}", name="category_replace", methods={"PUT"})
 */
class ReplaceController extends AbstractController
//"http://localhost:8082/category/id"
{
    public function __invoke(ManagerRegistry $doctrine, Request $request, ValidatorInterface $validator, int $id): JsonResponse
    {
        try {

            $entityManager = $doctrine->getManager();
            $category = $entityManager->getRepository(Category::class)->find($id);

            if (!$category) {
                throw new NotFoundHttpException(
                    "Category not found for id: $id", null, 404
                );
            }

            $request = (new TransformJsonBody())->transformJsonBody($request);
            $dto = new CategoryInputDTO($request->get('name'), $request->get('sort'));
            /** @var   ConstraintViolationList $violations */
            $violations = $validator->validate($dto);
            if (0 !== count($violations)) {
                throw  new CustomErrorException("", 422, null, $violations->getIterator());
            }

            $category->setName($request->get('name'));
            $category->setSort($request->get('sort'));
            $entityManager->persist($category);
            $entityManager->flush();

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
        } catch (CustomErrorException $e) {
            return (new ResponseJson())->response($e->getViolations(), $e->getCode());
        }
    }
}
