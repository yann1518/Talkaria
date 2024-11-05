<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Post;
use App\Form\PostType; // Assure-toi d'avoir le formulaire pour le Post
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post')]
    public function index(): Response
    {
        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
        ]);
    }
    #[Route('/post/create', name: 'app_post_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création d'une nouvelle instance de Post
        $post = new Post();
        
        // Création du formulaire à partir du PostType
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer l'utilisateur connecté
            $user = $this->getUser();
            $post->setUsers($user); // Associer l'utilisateur au post
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_home'); // Redirige vers la page d'accueil ou une autre page
        }

        return $this->render('post/create.html.twig', [
            'form' => $form->createView(), // Passer le formulaire à la vue
        ]);
    }
}
