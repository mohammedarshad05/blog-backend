<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;
use App\Entity\Product;
use App\Form\ProductType; 
use Doctrine\ORM\EntityManagerInterface;

final class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_product')]
    public function index(ProductRepository $repository): Response
    {
        // Fetch all products from database table
        return $this->render('product/index.html.twig', [
            'products' => $repository->findAll(), // Get all product records
        ]);
    }

    // Display single product using its id
    #[Route('/product/{id<\d+>}', name: 'app_product_show')]
    public function show(Product $product): Response
    { 
        return $this->render('product/show.html.twig', [
            'product' => $product, // Pass selected product to page
        ]);

        }
        



        // Product create form page
    #[Route('/product/new', name: 'product_new')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        // Create empty product object instance
        $product = new Product();

        // Build form and bind product object
        $form = $this->createForm(ProductType::class, $product);

        // Read submitted form request data
        $form->handleRequest($request);

        // Check whether form was submitted
        if ($form->isSubmitted() && $form->isValid()) {

            // Mark product ready for database save
            $manager->persist($product);

            // Write product data into database
            $manager->flush();
            $this->addFlash('success', 'Product created successfully!');
            return $this->redirectToRoute('app_product');

            // Show product object for debugging purposes
            dd($product);
        }

        return $this->render('product/new.html.twig', [
            // Convert form into Twig view format
            'form' => $form->createView(),
        ]);
    }




    #edit product
    #[Route('/product/{id<\d+>}/edit', name: 'product_edit')]
    public function edit(Product $product , Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $manager->flush();
            $this->addFlash('success', 'Product updated successfully!');
            return $this->redirectToRoute('app_product');

            dd($product);
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form,
        ]);
    }



    #delete product
    #[Route('/product/{id<\d+>}/delete', name: 'product_delete')]
    public function delete(Request $request, Product $product, EntityManagerInterface $manager): Response
    {
        if ($request->isMethod('POST')){

            $manager->remove($product);

            $manager->flush();

            $this->addFlash(
                'success',
                'Product deleted successfully'

            );
            
            return $this->redirectToRoute('app_product');
        }

        return $this->render('product/delete.html.twig',[
            'id' => $product ->getId(), 
        ]);

    }
}