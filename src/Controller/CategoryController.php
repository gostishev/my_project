<?php

namespace App\Controller;

use App\DTO\CategoryInputDTO;
use App\Exception\CustomErrorException;
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


class CategoryController extends AbstractController
{

    /**
     * @Route("/category", name="categories", methods={"GET"})
     */
    public function getCategories(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        try {
            $order = $request->query->get('order');
            if (!in_array($order, ['ASC', 'DESC'])) {
                throw new \Exception();
            }

            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
            $category = $doctrine->getRepository(Category::class)->findBy([], ['sort' => $order]);

            $normalizer = new ObjectNormalizer($classMetadataFactory);
            $serializer = new Serializer([$normalizer]);

            $data = $serializer->normalize($category, null, ['groups' => 'group1']);
            return $this->response($data);

        } catch (\Exception $e) {
            $data = [
                'status' => 422,
                'errors' => "Query parameter 'order' not equal to 'ASC' or 'DESC'",
            ];
            return $this->response($data, 422);
        }
    }

//    /**
//     * @Route("/category/{order}", name="categories_order", methods={"GET"})
//     */
//    public function getCategory(ManagerRegistry $doctrine, string $order): JsonResponse
//    {
//
//        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
//        $category = $doctrine->getRepository(Category::class)->findBy([], ['sort' => $order]);
//
//        $normalizer = new ObjectNormalizer($classMetadataFactory);
//        $serializer = new Serializer([$normalizer]);
//
//        $data = $serializer->normalize($category, null, ['groups' => 'group1']);
//
//        return $this->response($data );
//    }

    /**
     * @Route("/category/{id}", name="category_show", methods={"GET"})
     */
    public function show(ManagerRegistryAlias $doctrine, int $id): JsonResponse
    {
        try {

            $category = $doctrine->getRepository(Category::class)->find($id);
            if (!$category) {
                throw new NotFoundHttpException(
                    'Not found category for id :' . $id, null, 404
                );
            }

            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
            $normalizer = new ObjectNormalizer($classMetadataFactory);
            $serializer = new Serializer([$normalizer]);
            $data = $serializer->normalize($category, null, ['groups' => 'group1']);

            return $this->response($data);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Category not found for id: $id",
            ];
            return $this->response($data, 404);
        }
    }

    /**
     * @Route("/category/{id}", name= "category_remove", methods={"DELETE"} )
     */
    public function removeCategory(ManagerRegistryAlias $doctrine, int $id): JsonResponse
    {
        try {
            $em = $doctrine->getManager();
            $category = $doctrine->getRepository(Category::class)->find($id);

            if (!$category) {
                throw new NotFoundHttpException(
                    'Not found category for id :' . $id, null, 404
                );
            }

            $name = $category->getName();
            $sort = $category->getSort();

            $em->remove($category);
            $em->flush();
            $data = [
                'status' => 200,
                'success' => "Category deleted successfully for id: $id ",
                'data' => [
                    'id' => $id,
                    'name' => $name,
                    'sort' => $sort,
                ],
            ];
            return $this->response($data);

        } catch (NotFoundHttpException $e) {
            $data = [
                'status' => 404,
                'errors' => "Category not found for id: $id",
            ];
            return $this->response($data, 404);
        }
    }

    protected function transformJsonBody(Request $request)
    {
        $data = json_decode($request->getContent(), true, 128, \JSON_THROW_ON_ERROR);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);
        return $request;
    }

    public function response($data, $status = 200, $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * @return JsonResponse
     * @throws \Exception
     * @Route("/category", name="category_add", methods={"POST"})
     */
    public function addCategory(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator)
    {

        try {
            $request = $this->transformJsonBody($request);
            $dto = new CategoryInputDTO($request->get('name'), $request->get('sort'));
            /** @var   ConstraintViolationList $violations */
            $violations = $validator->validate($dto);

            if (0 !== count($violations)) {
                throw  new CustomErrorException("", 422, null, $violations->getIterator());
            }
//            $errors = [];
//            /** @var ConstraintViolation $violation */
//            foreach ($violations as $violation) {
//                $errors[$violation->getPropertyPath()] = $violation->getMessage();
//            }
//            $data = [
//                'status' => 422,
//                'errors' => $errors,
//            ];
//            return $this->response($data, 422);
//        }

//            if (!$request || !$request->get('name') || !$request->request->get('sort')){
//                throw new \Exception();
//            }

            $category = new Category();
            $category->setName($request->get('name'));
            $category->setSort($request->request->get('sort'));
            $entityManager = $doctrine->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            $id = $category->getId();
            $name = $category->getName();
            $sort = $category->getSort();
            $data = [
                'status' => 200,
                'success' => "Category added successfully for id: $id",
                'data' => [
                    'id' => $id,
                    'name' => $name,
                    'sort' => $sort,
                ],
            ];
            return $this->response($data);

        } catch (CustomErrorException $e) {
            return $this->response($e->getViolations(), $e->getCode());
        }

//        }catch (\Exception $e){
//            $data = [
//                'status' => 422,
//                'errors' => "Data no valid",
//            ];
//            return $this->response($data, 422);
//        }

    }

    /**
     * @throws \Exception
     * @Route("/category/{id}", name="category_edit", methods={"PUT"})
     */
    public function CategoryPut(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator, int $id): JsonResponse
    {

        try {
            try {

                $entityManager = $doctrine->getManager();
                $category = $entityManager->getRepository(Category::class)->find($id);

                if (!$category) {
                    throw new NotFoundHttpException(
                        'Not found category for id :' . $id, null, 404
                    );
                }

                $request = $this->transformJsonBody($request);
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

                $id = $category->getId();
                $name = $category->getName();
                $sort = $category->getSort();
                $data = [
                    'status' => 200,
                    'success' => "Category updated successfully",
                    'data' => [
                        'id' => $id,
                        'name' => $name,
                        'sort' => $sort,
                    ],
                ];
                return $this->response($data);

            } catch (NotFoundHttpException $e) {
                $data = [
                    'status' => 404,
                    'errors' => "Category not found for id: $id",
                ];
                return $this->response($data, 404);
            }
        } catch (CustomErrorException $e) {
            return $this->response($e->getViolations(), $e->getCode());
        }
    }

    /**
     * @throws \Exception
     * @Route("/category/{id}", name="category_parameter_edit", methods={"PATCH"})
     */
    public function CategoryPatch(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator, int $id): JsonResponse
    {
        try {
            try {
                try {
                    $entityManager = $doctrine->getManager();
                    $category = $entityManager->getRepository(Category::class)->find($id);
                    if (!$category) {
                        throw new NotFoundHttpException(
                            'Not found category for id :' . $id, null, 404
                        );
                    }

                    $request = $this->transformJsonBody($request);
//                $dto = new CategoryInputDTO($request->get('name'), $request->get('sort'));
//                /** @var   ConstraintViolationList $violations */
//                $violations = $validator->validate($dto);
//                if (0 !== count($violations)) {
//                    throw  new CustomErrorException("", 422, null, $violations->getIterator());
//                }
                    $name = $request->request->get('name');
                    $sort = $request->request->get('sort');

                    if ($request->request->has('name') && $request->request->has('sort')) {
                        $this->nameValidation($name, $validator, $category);
                        $this->sortValidation($sort, $validator, $category);
                    } elseif ($request->request->has('name')) {
                        $this->nameValidation($name, $validator, $category);
                    } elseif ($request->request->has('sort')) {
                        $this->sortValidation($sort, $validator, $category);
                    } else {
                        $data = [
                            'status' => 404,
                            'errors' => "There are no required fields in the request. The category has not changed for id: $id",
                        ];
                        return $this->response($data, 404);
                    }


//                    if ($request->request->has('name')) {
//                        $this->nameValidation($request, $validator, $category);
//                    }
//
//
//
//                    if ($request->request->has('sort')) {
//                        $this->sortValidation($request, $validator, $category);
//                    }


                    $entityManager->flush();
                    $data = [
                        'status' => 200,
                        'success' => "Category field updated successfully",
                    ];
                    return $this->response($data);

                } catch (NotFoundHttpException $e) {
                    $data = [
                        'status' => 404,
                        'errors' => "Category not found for id: $id",
                    ];
                    return $this->response($data, 404);
                }


            } catch (CustomErrorException $e) {
                return $this->response($e->getViolations(), $e->getCode());
            }

        } catch (\Exception $e) {
            $data = [
                'status' => 500,
                'errors' => $e->getMessage(),
            ];
            return $this->response($data, 500);
        }

    }

    private function sortValidation($sort, ValidatorInterface $validator, $category)
    {
        $sortConstraint = new Assert\Type(['type' => 'integer']);
        /** @var   ConstraintViolationList $violations */
        $violations = $validator->validate($sort, $sortConstraint);
        if (0 !== count($violations)) {
            throw  new CustomErrorException("", 422, null, $violations->getIterator());
        }
        $sortConstraintNull = new Assert\NotNull();
        /** @var   ConstraintViolationList $violationsNull */
        $violationsNull = $validator->validate($sort, $sortConstraintNull);
        if (0 !== count($violationsNull)) {
            throw  new CustomErrorException("", 422, null, $violationsNull->getIterator());
        }
        $sortConstraintBlank = new Assert\NotBlank();
        /** @var   ConstraintViolationList $violationsBlank */
        $violationsBlank = $validator->validate($sort, $sortConstraintBlank);
        if (0 !== count($violationsBlank)) {
            throw  new CustomErrorException("", 422, null, $violationsBlank->getIterator());
        }

        return $category->setSort($sort);

    }

    private function nameValidation($name, ValidatorInterface $validator, $category)
    {
        $nameConstraintType = new Assert\Type(['type' => 'string']);
        /** @var   ConstraintViolationList $violationsType */
        $violationsType = $validator->validate($name, $nameConstraintType);
        if (0 !== count($violationsType)) {
            throw  new CustomErrorException("", 422, null, $violationsType->getIterator());
        }
        $nameConstraintNull = new Assert\NotNull();
        /** @var   ConstraintViolationList $violationsNull */
        $violationsNull = $validator->validate($name, $nameConstraintNull);
        if (0 !== count($violationsNull)) {
            throw  new CustomErrorException("", 422, null, $violationsNull->getIterator());
        }
        $nameConstraintBlank = new Assert\NotBlank();
        /** @var   ConstraintViolationList $violationsBlank */
        $violationsBlank = $validator->validate($name, $nameConstraintBlank);
        if (0 !== count($violationsBlank)) {
            throw  new CustomErrorException("", 422, null, $violationsBlank->getIterator());
        }
        $nameConstraintUnique = new CustomAssert\FieldUnique();
        /** @var   ConstraintViolationList $violationsUnique */
        $violationsUnique = $validator->validate($name, $nameConstraintUnique);
        if (0 !== count($violationsUnique)) {
            throw  new CustomErrorException("", 422, null, $violationsUnique->getIterator());
        }

        $category->setName($name);

    }


}