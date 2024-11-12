<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les posts
        $posts = $entityManager->getRepository(Post::class)->findAll();

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/post/create', name: 'app_post_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Créer un nouveau post
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associer l'utilisateur connecté au post
            $user = $this->getUser();
            $post->setUsers($user);

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

            // Sauvegarder le post dans la base de données
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('post/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/post/{id}/{slug}', name: 'app_post_show')]
    public function show(int $id, string $slug, EntityManagerInterface $entityManager): Response
    {
        // Récupérer le post avec l'ID et le slug
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post || $post->getSlug() !== $slug) {
            throw $this->createNotFoundException('Le post n\'existe pas');
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/post/{id}/edit', name: 'app_post_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Récupérer le post à éditer avec l'ID
        $post = $entityManager->getRepository(Post::class)->find($id);
    
        if (!$post) {
            // Si le post n'existe pas, afficher une erreur
            throw $this->createNotFoundException('Le post n\'existe pas');
        }
    
        // Créer le formulaire de modification lié au post
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
    
        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Regénérer le slug si nécessaire (peut être utilisé si vous changez le titre par exemple)
            $slug = $this->generateUniqueSlug($post->getTitle(), $entityManager, $slugger);
            $post->setSlug($slug);
    
            // Sauvegarder les modifications dans la base de données
            $entityManager->flush();
    
            // Rediriger vers la page d'affichage du post
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId(), 'slug' => $post->getSlug()]);
        }
    
        // Affiche le formulaire d'édition du post
        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }    

    #[Route('/post/{id}/delete', name: 'app_post_delete')]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer le post à supprimer
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Le post n\'existe pas');
        }

        // Supprimer le post
        $entityManager->remove($post);
        $entityManager->flush();

        // Rediriger après la suppression
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
}
