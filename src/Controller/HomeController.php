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
        $totalPosts = count($posts); // Total des posts après filtrage
        $totalPages = ceil($totalPosts / $limit);
        $currentPage = (int) $request->query->get('page', 1); // Récupération de la page actuelle
        $currentPage = max(1, min($currentPage, $totalPages)); // S'assurer que la page est valide
        $offset = ($currentPage - 1) * $limit;

        // Récupérer les posts pour la page actuelle
        $posts = array_slice($posts, $offset, $limit);

        // Passer l'URL de création de post au template
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
