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
    public function index(PostRepository $PostRepository ): Response
    {
        $posts = $PostRepository->findAll();
        return $this->render('home/index.html.twig', [
            'posts' => $posts,
        ]);
    }
}
