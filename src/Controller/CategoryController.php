<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class CategoryController extends AbstractController
{

    /**
     * @Route("/categories", name="categories", methods={"GET"})
     */
    public function getCategories(ManagerRegistry $doctrine, string $order = "ASC"): JsonResponse
    {
        $category = $doctrine->getRepository(Category::class)->findBy([], ['id' => $order]);

        if (!$category) {
            throw $this->createNotFoundException(
                'Category not  found '
            );
        }

        echo '<pre>';
        $result = [];
        foreach ($category as $k => $v) {
            $res[$k] = [
                "id" => $v->getId(),
                "name" => $v->getName(),
            ];
            $result[] = $res[$k];
        }

        $json_result = json_encode($result);
        return new JsonResponse($json_result);
    }

    /**
     * @Route("/category/{id}", name="category_show", methods={"GET"})
     */
    public function show(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $category = $doctrine->getRepository(Category::class)->find($id);

        if (!$category) {
            throw $this->createNotFoundException(
                'Category not found for:' . $id
            );
        }

        $result = [
            "id" => $category->getId(),
            "name" => $category->getName(),
        ];

        $json_result = json_encode($result);
        return new JsonResponse($json_result);
    }

    /**
     * @Route("/category/remove/{id}", name= "category_remove", methods={"DELETE"} )
     */
    public function removeCategory(ManagerRegistry $doctrine, int $id): Response
    {

        $em = $doctrine->getManager();
        $category = $doctrine->getRepository(Category::class)->find($id);

        if (!$category) {
            throw $this->createNotFoundException(
                'Category not found for:'.$id
            );
        }
        $em->remove($category);
        $em->flush();

        return new Response('Category was deleted for:'.$id);
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
//     * @throws \Exception
     * @Route("/posts", name="posts_add", methods={"POST"})
     */
    public function addCategory(Request $request,ManagerRegistry $doctrine){

//        try{
            $request = $this->transformJsonBody($request);

//            if (!$request || !$request->get('name') || !$request->request->get('description')){
//                throw new \Exception();
//            }

            $category = new Category();
            $category->setName($request->get('name'));
            $category->setSort($request->get('sort'));
            $entityManager = $doctrine->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            $data = [
                'status' => 200,
                'success' => "Post added successfully",
            ];
            return $this->response($data);

//        }catch (\Exception $e){
//            $data = [
//                'status' => 422,
//                'errors' => "Data no valid",
//            ];
//            return $this->response($data, 422);
//        }

    }

    /**
     * @Route("/category/edit/{id}", name="category_edit")
     */
    public function updateCategory(Request $request,ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $category = $entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            throw $this->createNotFoundException(
                'No category found for : '.$id
            );
        }

        $request = $this->transformJsonBody($request);
        $category->setName($request->get('name'));
        $category->setSort($request->get('sort'));
        $entityManager->flush();

        return $this->redirectToRoute('category_show', [
            'id' => $category->getId()
        ]);
    }


}