<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Form\BlogType;
use App\Repository\BlogRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Builder\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

final class BlogController extends AbstractController
{
    //All Blogs
    #[Route('/blog', name: 'app_blog')]
    public function index(BlogRepository $repository): Response
    {
        return $this->render('blog/index.html.twig', [
            'blogs' => $repository->findAll(),
        ]);
    }

    //create new blog
    #[Route('/blog/new', name: 'app_blog_new')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $blog = new Blog();

        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $blog->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($blog);
            $manager->flush();

            $this->addFlash('success', 'Blog created successfully!');

            return $this->redirectToRoute('app_blog');
        }

        return $this->render('blog/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    //show blog
    #[Route('/blog/{id<\d+>}', name: 'show_blog')]
    public function show(Blog $blog): Response
    {
        return $this->render('/blog/show.html.twig', [
            'blog' => $blog,
        ]);
    }

    //edit blog
    #[Route('/blog/{id<\d+>}/edit', name: 'blog_edit')]
    public function  edit(Request $request, Blog $blog, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $blog->setUpdatedAt(new \DateTimeImmutable());

            $manager->flush();
            $this->addFlash('success', 'Blog updated successfully!');
            return $this->redirectToRoute('app_blog');
        }
        return $this->render('blog/edit.html.twig', [
            'form' => $form->createView(),
            'blog' => $blog,
        ]);
    }

    //delete blog
    #[Route('/blog/{id<\d+>}/delete', name: 'blog_delete')]
    public function delete(Request $request, Blog $blog, EntityManagerInterface $manager): Response

    {
        if ($request->isMethod('POST')) {
            $manager->remove($blog);
            $manager->flush();
            $this->addFlash('success', 'Blog deleted successfully!');
            return $this->redirectToRoute('app_blog');
        }

        return $this->render('blog/delete.html.twig', [
            'id' => $blog->getId(),
        ]);
    }


    // nextjs api call
    #[Route('/api/blogs', name: 'api_blogs', methods: ['GET'])]
    public function apiIndex(BlogRepository $repository): JsonResponse
    {
        $blogs = $repository->findAll();
        $data = [];

        foreach ($blogs as $blog) {
            $data[] = [
                'id' => $blog->getId(),
                'title' => $blog->getTitle(),
                'slug' => $blog->getSlug(),
                'author' => $blog->getAuthor(),
                'category' => $blog->getCategory(),
                'featuredImage' => $blog->getFeaturedImage(),
                'excerpt' => $blog->getExcerpt(),
                'content' => $blog->getContent(),
                'createdAt' => $blog->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $blog->getUpdatedAt()?->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json($data);
    }


    // nextjs api call show id
    #[Route('/api/blogs/{id<\d+>}', name: 'api_blog_show', methods: ['GET'])]
    public function apiShow(Blog $blog): JsonResponse
    {
        return $this->json([
            'id' => $blog->getId(),
            'title' => $blog->getTitle(),
            'slug' => $blog->getSlug(),
            'author' => $blog->getAuthor(),
            'category' => $blog->getCategory(),
            'featuredImage' => $blog->getFeaturedImage(),
            'excerpt' => $blog->getExcerpt(),
            'content' => $blog->getContent(),
            'createdAt' => $blog->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt' => $blog->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ]);
    }
    // nextjs api call create blog
    #[Route('/api/blogs', name: 'api_blog_create', methods: ['POST'])]
    public function apiCreate(Request $request, EntityManagerInterface $manager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $blog = new Blog();
        $blog->setTitle($data['title'] ?? '');
        $blog->setSlug($data['slug'] ?? '');
        $blog->setAuthor($data['author'] ?? '');
        $blog->setCategory($data['category'] ?? '');
        $blog->setFeaturedImage($data['featuredImage'] ?? '');
        $blog->setExcerpt($data['excerpt'] ?? '');
        $blog->setContent($data['content'] ?? '');
        $blog->setCreatedAt(new \DateTimeImmutable());

        $manager->persist($blog);
        $manager->flush();

        return $this->json([
            'message' => 'Blog created successfully!',
            'id' => $blog->getId(),
        ], Response::HTTP_CREATED);
    }
    // nextjs api delete blog
    #[Route('/api/blogs/{id<\d+>}', name: 'api_blog_delete', methods: ['DELETE'])]
    public function apiDelete(Blog $blog, EntityManagerInterface $manager): JsonResponse {
        $id = $blog->getId();

        $manager->remove($blog);
        $manager->flush();

        return $this->json([
            'message' => 'Blog deleted successfully!',
            'id' => $id,
        ]);
    }
}
