<?php

namespace App\Controller;

use App\DTO\CategoryInputDTO;
use Doctrine\Persistence\ManagerRegistry as ManagerRegistryAlias;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class CategoryController extends AbstractController
{

//    public function __construct(private Validator $validator)
//
//
//
//    {
//
//    }

    /**
     * @Route("/categories", name="categories", methods={"GET"})
     */
    public function getCategories(ManagerRegistry $doctrine, string $order = "ASC"):JsonResponse
    {
        $category = $doctrine->getRepository(Category::class)->findBy([], ['id' => $order]);

        $result = [];
        foreach ($category as $k => $v) {
            $res[$k] = [
                "id" => $v->getId(),
                "name" => $v->getName(),
            ];
            $result[] = $res[$k];
        }
        return $this->response($result);

    }

    /**
     * @Route("/category/{id}", name="category_show", methods={"GET"})
     */
    public function show(ManagerRegistryAlias $doctrine, int $id):JsonResponse
    {
        $category = $doctrine->getRepository(Category::class)->find($id);

        if (!$category){
            $data = [
                'status' => 404,
                'errors' => "Category not found",
            ];
            return $this->response($data, 404);
        }

        $result = [
            "id" => $category->getId(),
            "name" => $category->getName(),
        ];
        return $this->response($result);

    }

    /**
     * @Route("/category/remove/{id}", name= "category_remove", methods={"DELETE"} )
     */
    public function removeCategory(ManagerRegistryAlias $doctrine, int $id):JsonResponse
    {

        $em = $doctrine->getManager();
        $category = $doctrine->getRepository(Category::class)->find($id);

        if (!$category){
            $data = [
                'status' => 404,
                'errors' => "Category not found",
            ];
            return $this->response($data, 404);
        }

        $em->remove($category);
        $em->flush();
        $data = [
            'status' => 200,
            'errors' => "Category deleted successfully",
        ];
        return $this->response($data);

    }

    protected function transformJsonBody(\Symfony\Component\HttpFoundation\Request $request)
    {
        $data = json_decode($request->getContent(), true);

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
     * @throws Exception
     * @Route("/category/add", name="category_add", methods={"POST"})
     */
    public function addCategory(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator)
    {

       try{
        $request = $this->transformJsonBody($request);
        $dto = new CategoryInputDTO($request->get('name'), $request->get('sort'));
           $validator = Validation::createValidator();
           $violations = $validator->validate($dto);


            if (!$request || !$request->get('name') || !$request->request->get('sort')){
                throw new \Exception();
            }

        $category = new Category();
        $category->setName($request->get('name'));
        $category->setSort($request->request->get('sort'));
        $entityManager = $doctrine->getManager();
        $entityManager->persist($category);
        $entityManager->flush();

        $data = [
            'status' => 200,
            'success' => "Category added successfully",
        ];
        return $this->response($data);

        }catch (\Exception $e){
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return $this->response($data, 422);
        }

    }

    /**
     * @throws Exception
     * @Route("/category/edit/{id}", name="category_edit", methods={"PUT"})
     */
    public function updateCategory(Request $request, ManagerRegistry $doctrine, int $id):JsonResponse
    {

        try{

        $entityManager = $doctrine->getManager();
        $category = $entityManager->getRepository(Category::class)->find($id);

        if (!$category){
            $data = [
                'status' => 404,
                'errors' => "Category not found",
            ];
            return $this->response($data, 404);
        }

        $request = $this->transformJsonBody($request);
            if (!$request || !$request->get('name') || !$request->request->get('sort')){
                throw new \Exception();
            }

        $category->setName($request->get('name'));
        $category->setSort($request->get('sort'));
        $entityManager->persist($category);
        $entityManager->flush();

        $data = [
            'status' => 200,
            'success' => "Category updated successfully",
        ];
        return $this->response($data);

        }catch (\Exception $e){
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return $this->response($data, 422);
        }
    }

    /**
     * @throws Exception
     * @Route("/category/shreder/{id}", name="category_shreder", methods={"PATCH"})
     */
    public function shrederCategory(Request $request, ManagerRegistry $doctrine, int $id):JsonResponse
    {

             $entityManager = $doctrine->getManager();
            $category = $entityManager->getRepository(Category::class)->find($id);

            if (!$category){
                $data = [
                    'status' => 404,
                    'errors' => "Category not found",
                ];
                return $this->response($data, 404);
            }
            $request = $this->transformJsonBody($request);

            if($request->get('name')) {
                $category->setName($request->get('name'));
            }elseif($request->request->get('sort')) {
                $category->setSort($request->get('sort'));
            }else{
                $data = [
                   'status' => 422,
                   'errors' => "Data no valid",
            ];
            return $this->response($data, 422);
            }

            $entityManager->flush();

            $data = [
                'status' => 200,
                'success' => "Category field updated successfully",
            ];
            return $this->response($data);

    }



}