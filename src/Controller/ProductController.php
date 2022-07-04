<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
//use Symfony\Component\Validator\Validator\ValidatorInterface;


class ProductController extends AbstractController
{
//    #[Route('/product', name: 'app_product')]
//    public function index(): Response
//    {
//        return $this->render('product/index.html.twig', [
//            'controller_name' => 'ProductController',
//        ]);
//    }

    /**
     * @Route("/product", name="product_create")
     */
    public function createProduct(ManagerRegistry $doctrine ): Response
    {
        $entityManager = $doctrine->getManager();

        $product = new Product();
        $product->setName('goppa');
        $product->setPrice(5000);
        $product->setDescription('Ergonomic and stylish!');

//        $errors = $validator->validate($product);
//        if (count($errors) > 0) {
//            return new Response((string)$errors, 400);
//        }

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new product with id ' . $product->getId());

    }

//    /**
//     * @Route("/product/{id}", name="product_show")
//     */
//    public function show(int $id, ProductRepository $productRepository): Response
//    {
//        $product = $productRepository
//            ->find($id);
//
//        if (!$product) {
//            throw $this->createNotFoundException(
//                'No product found for id '.$id
//            );
//        }
//
//        return new Response('Check out this great product: '.$product->getId().'  '.$product->getName());
//
//
//    }

    /**
     * @Route("/product/{id}", name="product_show")
     */
    public function show(ManagerRegistry $doctrine, int $id): Response
    {
        $product = $doctrine->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        return new Response('Check out this great product: '.$product->getId(). $product->getName(). $product->getPrice(). $product->getDescription());

        // or render a template
        // in the template, print things with {{ product.name }}
        // return $this->render('product/show.html.twig', ['product' => $product]);
    }



    /**
     * @Route("/product/edit/{id}", name="product_edit")
     */
    public function update(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $product->setName('New!');
        $product->setPrice(3000);
        $entityManager->flush();

        return $this->redirectToRoute('product_show', [
            'id' => $product->getId()
        ]);
    }

    /**
     * @Route("/product/remove/{id}", name= "product_remove" )
     */
    public function remove(ManagerRegistry $doctrine, int $id): Response
    {

        $em = $doctrine->getManager();
        $product = $doctrine->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }
        $em->remove($product);
        $em->flush();

        return new Response('Ppipez product :'.$id);
    }

    /**
     * @Route("/findOneBy/product/{$value}", name="product_findOneBy")
     */
    public function findOneBy(ManagerRegistry $doctrine, string $value): Response
    {
        $em = $doctrine->getManager();
        $product = $doctrine->getRepository(Product::class)->findOneBy([
            'name' => $value ,

            ]);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '
            );
        }

        $description = $product->getDescription();
        $price = $product->getPrice();
        $name = $product->getName();

          return  new Response('  '.'product'.'  '.$name.'  '.'cost'.'  '.$price.'  '.'  '.'with'.'  '.$description  );

    }


    /**
     * @Route("/findBy/product", name="product_findBy")
     */
    public function findBy(ManagerRegistry $doctrine): Response
    {
//        $em = $doctrine->getManager();
        $product = $doctrine->getRepository(Product::class)->findBy(
            ['name' => 'goppaorgoopa'],
            ['price' => 'ASC']
        );

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '
            );
        }
        echo'<pre>';
            $res = '';
        foreach ($product as $k => $v){
            $description = $v->getDescription();
            $price = $v->getPrice();
            $name = $v->getName();
            $id = $v->getId();

            $res = $res.$id.'  '.'product'.'  '.$name.'  '.'cost'.'  '.$price.'  '.'  '.'with'.'  '.$description ."\n";

        }

       return  new Response($res );

    }


    /**
     * @Route("/findAll/product", name="product_findAll")
     */
    public function findAll(ManagerRegistry $doctrine): Response
    {
//        $em = $doctrine->getManager();
        $product = $doctrine->getRepository(Product::class)->findBy( [], ['id' => 'ASC']);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '
            );
        }
        echo'<pre>';
        $res = '';
        foreach ($product as $k => $v){
            $description = $v->getDescription();
            $price = $v->getPrice();
            $name = $v->getName();
            $id = $v->getId();

            $res = $res.$id.'  '.'product'.'  '.$name.'  '.'cost'.'  '.$price.'  '.'  '.'with'.'  '.$description ."\n";

        }

        return  new Response($res );

    }

    /**
     * @Route("/product/findByExampleField/{$value}", name="product_findByExampleField")
     */
    public function findByExampleField(ManagerRegistry $doctrine, string $value): Response
    {
        $result = $doctrine->getRepository(Product::class)->findByExampleField($value);

        if (!$result) {
            throw $this->createNotFoundException(
                'result not found'
            );
        }

        echo'<pre>';
        $res = implode(',',$result);


        return  new Response($res );


    }


}
