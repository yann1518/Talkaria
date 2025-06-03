<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comments;
use App\Entity\Users;
use App\Form\PostType;
use App\Form\CommentsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Annotation\IsGranted;

class PostController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/post', name: 'app_post')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = $request->query->get('category');
        $repo = $entityManager->getRepository(Post::class);
        $qb = $repo->createQueryBuilder('p')
            ->leftJoin('p.likes', 'l')
            ->addSelect('l');

        if ($category) {
            $qb->where('p.category = :cat')->setParameter('cat', $category);
        }

        $posts = $qb->getQuery()->getResult();

        // Récupérer toutes les catégories distinctes pour le filtre
        $categories = $repo->createQueryBuilder('p')
            ->select('DISTINCT p.category')
            ->getQuery()
            ->getResult();

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'categories' => array_column($categories, 'category'),
            'selectedCategory' => $category,
        ]);
    }

    #[Route('/post/create-or-edit/{id?}', name: 'app_post_create_or_edit')]
    public function createOrEdit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, int $id = null): Response
    {
        // Si un ID est fourni, essayez de récupérer le post existant
        $post = $id ? $entityManager->getRepository(Post::class)->find($id) : new Post();
    
        if ($id && !$post) {
            throw $this->createNotFoundException('Le post n\'existe pas');
        }

        // Vérifier si c'est une requête API
        $isApiRequest = $request->headers->get('Content-Type') === 'application/ld+json';
        
        if ($isApiRequest) {
            // Décoder les données JSON
            $data = json_decode($request->getContent(), true);
            
            // Mettre à jour le post avec les données reçues
            $post->setTitle($data['title']);
            $post->setContent($data['content']);
            $post->setCategory($data['category']);
            $post->setImageFilename($data['imageFilename'] ?? null);
            $post->setSlug($data['slug']);
            
            // Si c'est un nouveau post, définir la date de création
            if (!$post->getId()) {
                $post->setCreatedAt(new \DateTimeImmutable());
            }
            
            // Récupérer l'utilisateur depuis l'URL fournie (ex: /api/users/1)
            if (isset($data['users'])) {
                $userId = basename($data['users']);
                $user = $entityManager->getRepository(User::class)->find($userId);
                if ($user) {
                    $post->setUsers($user);
                }
            }
            
            $entityManager->persist($post);
            $entityManager->flush();
            
            return $this->json([
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'users' => $post->getUsers() ? ['id' => $post->getUsers()->getId()] : null
            ], Response::HTTP_CREATED);
        }
    
        // Traitement normal pour le formulaire web
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$post->getId()) {
                // Associer l'utilisateur connecté si nouveau post
                $post->setUsers($this->getUser());
                $post->setCreatedAt(new \DateTimeImmutable());
            }
    
            // Gérer le téléchargement de l'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $post->setImageFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }
    
            // Générer un slug unique
            $slug = $this->generateUniqueSlug($post->getTitle(), $entityManager, $slugger);
            $post->setSlug($slug);
    
            $entityManager->persist($post);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId(), 'slug' => $post->getSlug()]);
        }
    
        return $this->render('post/create.html.twig', [
            'form' => $form->createView(),
            'is_editing' => $id !== null,
        ]);
    }
    
    #[Route('/post/show/{id}/{slug}', name: 'app_post_show')]
    public function show(int $id, string $slug, EntityManagerInterface $entityManager, Request $request): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Le post n\'existe pas. ID: ' . $id);
        }

        if ($post->getSlug() !== $slug) {
            throw $this->createNotFoundException('Le post n\'existe pas. ID: ' . $id);
        }

        // Récupère les commentaires liés à ce post
        $comments = $entityManager->getRepository(Comments::class)->findBy(['posts' => $post], ['createdAt' => 'DESC']);

        // Gestion du formulaire d'ajout de commentaire
        $comment = new Comments();
        $form = $this->createForm(CommentsType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setPosts($post);
            if ($this->getUser()) {
                $comment->setAuthor($this->getUser()->getUsername());
            } else {
                $comment->setAuthor('Anonyme');
            }
            $comment->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId(), 'slug' => $post->getSlug()]);
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comments' => $comments,
            'commentForm' => $form->createView(),
        ]);
    }

    #[Route('/post/{id}/edit', name: 'app_post_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Le post n\'existe pas');
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'enregistrement des modifications
            $entityManager->flush();
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId(), 'slug' => $post->getSlug()]);
        }

        return $this->render('post/create_edit.html.twig', [
            'form' => $form->createView(),
            'isEditMode' => true,
            'post' => $post,
        ]);
    }

    #[Route('/post/{id}/delete', name: 'app_post_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        dump($id);
        $post = $entityManager->getRepository(Post::class)->find($id);
        dump($post);

        if (!$post) {
            throw $this->createNotFoundException('Le post n\'existe pas. ID: ' . $id);
        }

        // Vérification du token CSRF
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $post->getId(), $submittedToken)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $entityManager->remove($post);
        $entityManager->flush();

        return $this->redirectToRoute('app_home');
    }

    private function generateUniqueSlug(string $title, EntityManagerInterface $entityManager, SluggerInterface $slugger): string
    {
        // Générer un slug basé sur le titre
        $slug = strtolower($slugger->slug($title)->toString());
        $originalSlug = $slug;

        // Vérifier si le slug est déjà pris et ajouter un suffixe pour garantir l'unicité
        $i = 1;
        while ($entityManager->getRepository(Post::class)->findOneBy(['slug' => $slug])) {
            $slug = $originalSlug . '-' . $i;
            $i++;
        }

        return $slug;
    }

    #[Route('/post/{id}/like', name: 'app_post_like', methods: ['POST'])]
    public function like(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        // ... (code existant)
    }

    #[Route('/post/{id}/like', name: 'app_post_like_status', methods: ['GET'])]
    public function likeStatus(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $post = $entityManager->getRepository(Post::class)->find($id);
        if (!$post) {
            return $this->json(['error' => 'Post non trouvé'], 404);
        }
        $likeRepo = $entityManager->getRepository(\App\Entity\Like::class);
        $likesCount = $likeRepo->countLikesForPost($post);
        $isLiked = $user ? $likeRepo->isPostLikedByUser($post, $user) : false;

        return $this->json([
            'likes' => $likesCount,
            'isLiked' => $isLiked,
        ]);
    }
        try {
            // Vérification de la requête AJAX
            if (!$request->isXmlHttpRequest()) {
                return $this->json(['error' => 'Requête invalide'], 400);
            }

            // Vérification de l'utilisateur connecté
            $user = $this->getUser();
            if (!$user) {
                return $this->json(['error' => 'Vous devez être connecté pour liker.'], 403);
            }

            // Récupération du post
            $post = $entityManager->getRepository(Post::class)->find($id);
            if (!$post) {
                return $this->json(['error' => 'Post non trouvé'], 404);
            }

            $likeRepo = $entityManager->getRepository(\App\Entity\Like::class);
            // Recherche du like existant (utiliser l'objet Users)
            $existingLike = $likeRepo->findOneBy([
                'user' => $user,
                'post' => $post
            ]);

            if ($existingLike) {
                // Suppression du like existant
                $entityManager->remove($existingLike);
                $entityManager->flush();
                $likesCount = $likeRepo->count(['post' => $post]);
                return $this->json([
                    'success' => true,
                    'likes' => $likesCount,
                    'isLiked' => false,
                    'message' => 'Like supprimé avec succès'
                ]);
            } else {
                // Création d'un nouveau like
                $like = new \App\Entity\Like();
                $like->setUser($user); // $user est bien un objet Users
                $like->setPost($post);
                $entityManager->persist($like);
                $entityManager->flush();
                $likesCount = $likeRepo->count(['post' => $post]);
                return $this->json([
                    'success' => true,
                    'likes' => $likesCount,
                    'isLiked' => true,
                    'message' => 'Like ajouté avec succès'
                ]);
            }
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Une erreur est survenue: ' . $e->getMessage()
            ], 500);
        }
    }
}