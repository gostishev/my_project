<?php

namespace App\Controller;

use App\DTO\CategoryInputDTO;
use App\Exception\CustomErrorException;
use App\Helper\ResponseJson;
use App\Helper\TransformJsonBody;
use Doctrine\Persistence\ManagerRegistry as ManagerRegistryAlias;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Validator\Constraints\SortConstraint;
use App\Validator\Constraints\NameConstraint;


class CategoryController extends AbstractController
{

//    /**
//     * @Route("/category", name="categories", methods={"GET"})
//     */
//    public function getCategories(ManagerRegistry $doctrine, Request $request): JsonResponse
//    {
//        try {
//
//            $order = $request->query->get('order');
//
//            if (!in_array($order, ['ASC', 'DESC'])) {
//                throw new NotFoundHttpException();
//            }
//
//            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
//            $category = $doctrine->getRepository(Category::class)->findBy([], ['sort' => $order]);
//
//            $normalizer = new ObjectNormalizer($classMetadataFactory);
//            $serializer = new Serializer([$normalizer]);
//
//            $data = $serializer->normalize($category, null, ['groups' => 'group1']);
//            return (new ResponseJson())->response($data);
//
//        } catch (NotFoundHttpException $e) {
//            $data = [
//                'status' => 422,
//                'errors' => "Query parameter 'order' not equal to 'ASC' or 'DESC'",
//            ];
//            return (new ResponseJson())->response($data, 422);
//        }
//    }

//    /**
//     * @Route("/category/{id}", name="category_show", methods={"GET"})
//     */
//    public function showCategory(ManagerRegistry $doctrine, int $id): JsonResponse
//    {
//        try {
//
//            $category = $doctrine->getRepository(Category::class)->find($id);
//            if (!$category) {
//                throw new NotFoundHttpException(
//                    "Category not found for id: $id", null, 404
//                );
//            }
//
//            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
//            $normalizer = new ObjectNormalizer($classMetadataFactory);
//            $serializer = new Serializer([$normalizer]);
//            $data = $serializer->normalize($category, null, ['groups' => 'group1']);
//
//            return $this->response($data);
//
//        } catch (NotFoundHttpException $e) {
//            $data = [
//                'status' => 404,
//                'errors' => "Category not found for id: $id",
//            ];
//            return $this->response($data, 404);
//        }
//
//    }

//    /**
//     * @Route("/category/{id}", name= "category_remove", methods={"DELETE"} )
//     */
//    public function removeCategory(ManagerRegistryAlias $doctrine, int $id): JsonResponse
//    {
//        try {
//            $em = $doctrine->getManager();
//            $category = $doctrine->getRepository(Category::class)->find($id);
//
//            if (!$category) {
//                throw new NotFoundHttpException(
//                    "Category not found for id: $id", null, 404
//                );
//            }
//
//            $name = $category->getName();
//            $sort = $category->getSort();
//
//            $em->remove($category);
//            $em->flush();
//            $data = [
//                'status' => 200,
//                'success' => "Category field delete successfully for id: $id ",
//                'data' => [
//                    'id' => $id,
//                    'name' => $name,
//                    'sort' => $sort,
//                ],
//            ];
//            return $this->response($data);
//
//        } catch (NotFoundHttpException $e) {
//            $data = [
//                'status' => 404,
//                'errors' => "Category not found for id: $id",
//            ];
//            return $this->response($data, 404);
//        }
//    }

//    protected function transformJsonBody(Request $request)
//    {
//        $data = json_decode($request->getContent(), true, 128, \JSON_THROW_ON_ERROR);
//
//        if ($data === null) {
//            return $request;
//        }
//
//        $request->request->replace($data);
//        return $request;
//    }

//    public function response($data, $status = 200, $headers = [])
//    {
//        return new JsonResponse($data, $status, $headers);
//    }

//    /**
//     * @Route("/category", name="category_add", methods={"POST"})
//     */
//    public function CategoryAdd(ManagerRegistry $doctrine, Request $request, ValidatorInterface $validator): JsonResponse
//    {
//
//        try {
//            $request = (new TransformJsonBody())->transformJsonBody($request);
//            $dto = new CategoryInputDTO($request->get('name'), $request->get('sort'));
//            /** @var   ConstraintViolationList $violations */
//            $violations = $validator->validate($dto);
//
//            if (0 !== count($violations)) {
//                throw  new CustomErrorException("", 422, null, $violations->getIterator());
//            }
//
//            $category = new Category();
//            $category->setName($request->get('name'));
//            $category->setSort($request->request->get('sort'));
//            $entityManager = $doctrine->getManager();
//            $entityManager->persist($category);
//            $entityManager->flush();
//
//            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
//            $normalizer = new ObjectNormalizer($classMetadataFactory);
//            $serializer = new Serializer([$normalizer]);
//            $data = $serializer->normalize($category, null, ['groups' => 'group1']);
//
//            return (new ResponseJson())->response($data);
//
//        } catch (CustomErrorException $e) {
//            return (new ResponseJson())->response($e->getViolations(), $e->getCode());
//        }
//
//    }

//    /**
//     * @Route("/category/{id}", name="category_edit", methods={"PUT"})
//     */
//    public function CategoryPut(ManagerRegistry $doctrine, Request $request, ValidatorInterface $validator, int $id): JsonResponse
//    {
//
//        try {
//
//            $entityManager = $doctrine->getManager();
//            $category = $entityManager->getRepository(Category::class)->find($id);
//
//            if (!$category) {
//                throw new NotFoundHttpException(
//                    "Category not found for id: $id", null, 404
//                );
//            }
//
//            $request = (new TransformJsonBody())->transformJsonBody($request);
//            $dto = new CategoryInputDTO($request->get('name'), $request->get('sort'));
//            /** @var   ConstraintViolationList $violations */
//            $violations = $validator->validate($dto);
//            if (0 !== count($violations)) {
//                throw  new CustomErrorException("", 422, null, $violations->getIterator());
//            }
//
//            $category->setName($request->get('name'));
//            $category->setSort($request->get('sort'));
//            $entityManager->persist($category);
//            $entityManager->flush();
//
//            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
//            $normalizer = new ObjectNormalizer($classMetadataFactory);
//            $serializer = new Serializer([$normalizer]);
//            $data = $serializer->normalize($category, null, ['groups' => 'group1']);
//
//            return (new ResponseJson())->response($data);
//
//        } catch (NotFoundHttpException $e) {
//            $data = [
//                'status' => 404,
//                'errors' => "Category not found for id: $id",
//            ];
//            return (new ResponseJson())->response($data, 404);
//        } catch (CustomErrorException $e) {
//            return (new ResponseJson())->response($e->getViolations(), $e->getCode());
//        }
//    }

//    /**
//     * @Route("/category/{id}", name="category_parameter_edit", methods={"PATCH"})
//     */
//    public function CategoryPatch(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator, int $id): JsonResponse
//    {
//        try {
//            $entityManager = $doctrine->getManager();
//            $category = $entityManager->getRepository(Category::class)->find($id);
//            if (!$category) {
//                throw new NotFoundHttpException(
//                    "Category not found for id: $id", null, 404
//                );
//            }
//            $name = $category->getName();
//            $sort = $category->getSort();
//
//            $request = (new TransformJsonBody())->transformJsonBody($request);
//            $violations = new ConstraintViolationList();
//
//            if ($request->request->has('name')) {
//                $nameConstraint = new NameConstraint;
//                /** @var   ConstraintViolationList $violationsName */
//                $violationsName = $validator->validate($request->request->get('name'), $nameConstraint);
//                $violations->addAll($violationsName);
//                $name = $request->request->get('name');
//            }
//            if ($request->request->has('sort')) {
//                $sortConstraint = new SortConstraint;
//                /** @var   ConstraintViolationList $violationsSort */
//                $violationsSort = $validator->validate($request->request->get('sort'), $sortConstraint);
//                $violations->addAll($violationsSort);
//                $sort = $request->request->get('sort');
//            }
//
//            if (0 !== count($violations)) {
//                throw  new CustomErrorException("", 422, null, $violations->getIterator());
//            }
//
//            $category->setSort($sort);
//            $category->setName($name);
//            $entityManager->flush();
//
//            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
//            $normalizer = new ObjectNormalizer($classMetadataFactory);
//            $serializer = new Serializer([$normalizer]);
//            $data = $serializer->normalize($category, null, ['groups' => 'group1']);
//
//            return (new ResponseJson())->response($data);
//
//        } catch
//        (NotFoundHttpException $e) {
//            $data = [
//                'status' => 404,
//                'errors' => "Category not found for id: $id",
//            ];
//            return (new ResponseJson())->response($data, 404);
//
//        } catch (CustomErrorException $e) {
//            return (new ResponseJson())->response($e->getViolations(), $e->getCode());
//        }
//
//    }

}