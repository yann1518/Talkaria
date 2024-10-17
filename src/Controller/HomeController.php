<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();

        // Récupération des catégories existantes dans les posts
        $categories = array_unique(array_map(function ($post) {
            return $post->getCategory();
        }, $posts));

        // Filtrage par catégorie si la catégorie est définie dans la requête
        $selectedCategory = $_GET['category'] ?? null;
        if ($selectedCategory) {
            $posts = array_filter($posts, function ($post) use ($selectedCategory) {
                return $post->getCategory() === $selectedCategory;
            });
        }

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
        ]);
    }
}
