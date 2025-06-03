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
        $limit = 3; // Nombre de posts par page
        $currentPage = max(1, (int) $request->query->get('page', 1));
        $offset = ($currentPage - 1) * $limit;

        // Récupération de toutes les catégories existantes
        $allPosts = $postRepository->findAll();
        $categories = array_unique(array_map(function ($post) {
            return $post->getCategory();
        }, $allPosts));

        // Filtrage par catégorie si sélectionnée
        $selectedCategory = $request->query->get('category');
        $criteria = [];
        if ($selectedCategory) {
            $criteria['category'] = $selectedCategory;
        }

        // Nombre total de posts (filtré ou non)
        $totalPosts = $postRepository->count($criteria);
        $totalPages = (int) ceil($totalPosts / $limit);

        // Récupérer les posts filtrés et paginés
        $posts = $postRepository->findBy(
            $criteria,
            ['createdAt' => 'DESC'],
            $limit,
            $offset
        );

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
        ]);
    }

    #[Route('/liens-application', name: 'app_liens_application')]
    public function liensApplication(): Response
    {
        return $this->render('liens_appli/lienappli.html.twig');
    }
}
