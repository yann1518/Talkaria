<?php
namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Annotation\IsGranted;

class HomeController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/home', name: 'app_home')]
    public function index(Request $request, PostRepository $postRepository): Response
    {
        // Nombre de posts par page
        $limit = 5; // Changez ce nombre selon vos besoins

        // Récupération de tous les posts
        $posts = $postRepository->findAll();
        
        // S'assurer que chaque post a un utilisateur
        foreach ($posts as $post) {
            if ($post->getUsers() === null) {
                throw new \RuntimeException(sprintf('Le post avec l\'ID %d n\'a pas d\'utilisateur associé', $post->getId()));
            }
        }

        // Récupération des catégories existantes dans les posts
        $categories = array_unique(array_map(function ($post) {
            return $post->getCategory();
        }, $posts));

        // Filtrage par catégorie si la catégorie est définie dans la requête
        $selectedCategory = $request->query->get('category');
        if ($selectedCategory) {
            $posts = array_filter($posts, function ($post) use ($selectedCategory) {
                return $post->getCategory() === $selectedCategory;
            });
        }

        // Pagination
        $limit = 3;
        $currentPage = max(1, (int) $request->query->get('page', 1));
        $offset = ($currentPage - 1) * $limit;

        // Récupérer le nombre total de posts
        $totalPosts = $postRepository->count([]);
        $totalPages = (int) ceil($totalPosts / $limit);

        // Récupérer les posts pour la page courante
        $posts = $postRepository->findBy([], ['createdAt' => 'DESC'], $limit, $offset);

        // Passer les variables au template
        return $this->render('home/index.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory, // Passer la catégorie sélectionnée
            'current_page' => $currentPage, // Passer la page actuelle
            'total_pages' => $totalPages, // Passer le total des pages
        ]);
    }

    #[Route('/liens-application', name: 'app_liens_application')]
    public function liensApplication(): Response
    {
        return $this->render('liens_appli/lienappli.html.twig');
    }
}
