<?php

// src/Controller/BlogController.php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Annotation\IsGranted;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BlogController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/blog', name: 'blog')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer les 5 posts les plus vus
        $postRepository = $entityManager->getRepository(Post::class);
        $posts = $postRepository->findBy([], ['views' => 'DESC'], 5);

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/blog/create', name: 'blog_create')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        Security $security
    ): Response {
        $post = new Post();
        $form = $formFactory->createNamedBuilder('post', Post::class, $post)
            ->add('title')
            ->add('content')
            ->add('imageFile')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUsers($security->getUser());
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('blog');
        }

        return $this->render('post/create_edit.html.twig', [
            'form' => $form->createView(),
            'isEditMode' => false
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/blog/edit/{id}', name: 'blog_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        Security $security,
        Post $post
    ): Response {
        if ($post->getUsers() !== $security->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce post.');
        }

        $form = $formFactory->createNamedBuilder('post', Post::class, $post)
            ->add('title')
            ->add('content')
            ->add('imageFile')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->redirectToRoute('blog');
        }

        return $this->render('post/create_edit.html.twig', [
            'form' => $form->createView(),
            'isEditMode' => true
        ]);
    }
}
